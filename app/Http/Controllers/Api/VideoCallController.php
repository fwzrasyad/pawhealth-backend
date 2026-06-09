<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\VideoCallNotification;
use App\Services\AgoraService;
use Illuminate\Http\Request;

class VideoCallController extends Controller
{
    protected AgoraService $agora;

    public function __construct(AgoraService $agora)
    {
        $this->agora = $agora;
    }

    /**
     * POST /api/appointments/{id}/start-call
     *
     * Start a video call for a confirmed appointment.
     * Only the assigned veterinarian may start the call.
     */
    public function startCall(Request $request, string $id)
    {
        $appointment = Appointment::where('appointment_id', $id)->firstOrFail();
        $user = $request->user();

        // Only confirmed appointments can start a call
        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed appointments can start a video call.',
            ], 422);
        }

        // Verify the requesting user is the assigned vet
        // The Veterinarian model maps vet_id → user_id, so vet_id IS the user_id
        if ($user->user_id !== $appointment->vet_id) {
            return response()->json([
                'message' => 'Only the assigned veterinarian can start a video call.',
            ], 403);
        }

        // Generate channel name and token
        $channel = 'pawhealth_' . $id;
        $vetUid = 1;
        $token = $this->agora->generateToken($channel, $vetUid);

        // Update appointment with video call info
        $appointment->update([
            'video_call_channel'    => $channel,
            'video_call_status'     => 'active',
            'video_call_started_at' => now(),
        ]);

        // Notify the pet owner
        $owner = $appointment->pet?->owner;
        if ($owner) {
            $owner->notify(new VideoCallNotification(
                $appointment->appointment_id,
                'incoming_call',
                $channel
            ));
        }

        return response()->json([
            'channel' => $channel,
            'token'   => $token,
            'uid'     => $vetUid,
            'app_id'  => $this->agora->getAppId(),
        ]);
    }

    /**
     * GET /api/appointments/{id}/call-status
     *
     * Check video call status and get a fresh token to join.
     */
    public function callStatus(Request $request, string $id)
    {
        $appointment = Appointment::where('appointment_id', $id)->firstOrFail();

        if ($appointment->video_call_status !== 'active') {
            return response()->json(['active' => false]);
        }

        $user = $request->user();

        // Determine caller role: vet gets uid=1, owner gets uid=2
        $isVet = $user->user_id === $appointment->vet_id;
        $uid = $isVet ? 1 : 2;

        $token = $this->agora->generateToken($appointment->video_call_channel, $uid);

        return response()->json([
            'active'  => true,
            'channel' => $appointment->video_call_channel,
            'token'   => $token,
            'uid'     => $uid,
            'app_id'  => $this->agora->getAppId(),
        ]);
    }

    /**
     * POST /api/appointments/{id}/end-call
     *
     * End the video call and mark the appointment as completed.
     */
    public function endCall(Request $request, string $id)
    {
        $appointment = Appointment::where('appointment_id', $id)->firstOrFail();

        if ($appointment->video_call_status !== 'active') {
            return response()->json([
                'message' => 'No active video call for this appointment.',
            ], 422);
        }

        $user = $request->user();

        // Update appointment
        $appointment->update([
            'video_call_status'   => 'ended',
            'video_call_ended_at' => now(),
            'status'              => 'completed',
        ]);

        // Notify the other party
        $isVet = $user->user_id === $appointment->vet_id;

        if ($isVet) {
            // Notify the pet owner
            $owner = $appointment->pet?->owner;
            if ($owner) {
                $owner->notify(new VideoCallNotification(
                    $appointment->appointment_id,
                    'call_ended'
                ));
            }
        } else {
            // Notify the vet
            $vetUser = User::where('user_id', $appointment->vet_id)->first();
            if ($vetUser) {
                $vetUser->notify(new VideoCallNotification(
                    $appointment->appointment_id,
                    'call_ended'
                ));
            }
        }

        return response()->json([
            'message' => 'Video call ended successfully.',
        ]);
    }
}

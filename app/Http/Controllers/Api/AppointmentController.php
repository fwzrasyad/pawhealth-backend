<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppointmentController extends Controller
{
    /**
     * GET /api/appointments
     * List appointments — filtered by role:
     *   - owner: appointments for the owner's pets
     *   - vet:   appointments assigned to the vet
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'vet') {
            $appointments = Appointment::where('vet_id', $user->user_id)
                ->with('medicalRecord')
                ->orderByDesc('appointment_date')
                ->get();
        } else {
            // Owner: get appointments for all their pets
            $petIds = $user->pets()->pluck('pet_id');
            $appointments = Appointment::whereIn('pet_id', $petIds)
                ->with('medicalRecord')
                ->orderByDesc('appointment_date')
                ->get();
        }

        return AppointmentResource::collection($appointments);
    }

    /**
     * POST /api/appointments
     * Book a new appointment at a specific clinic with a specific vet.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id'        => 'required|string|exists:clinics,clinic_id',
            'pet_id'           => 'required|string|exists:pets,pet_id',
            'pet_name'         => 'required|string|max:255',
            'vet_id'           => 'required|string|exists:veterinarians,vet_id',
            'vet_name'         => 'required|string|max:255',
            'reason'           => 'required|string',
            'appointment_date' => 'required|date',
            'time_slot'        => 'required|date',
        ]);

        $timeSlot = \Carbon\Carbon::parse($validated['time_slot']);
        if ($timeSlot->isPast()) {
            return response()->json(['message' => 'Cannot book an appointment in the past.'], 422);
        }

        $isTaken = Appointment::where('vet_id', $validated['vet_id'])
            ->where('time_slot', $validated['time_slot'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($isTaken) {
            return response()->json(['message' => 'This time slot is already booked.'], 422);
        }

        $appointment = Appointment::create(array_merge($validated, [
            'status' => 'pending',
        ]));

        return new AppointmentResource($appointment);
    }

    /**
     * PUT /api/appointments/{id}
     * Update appointment status.
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::where('appointment_id', $id)->firstOrFail();

        $validated = $request->validate([
            'status'           => 'sometimes|in:pending,confirmed,completed,cancelled',
            'reason'           => 'sometimes|string',
            'appointment_date' => 'sometimes|date',
            'time_slot'        => 'sometimes|date',
        ]);

        $appointment->update($validated);

        return new AppointmentResource($appointment->fresh());
    }

    /**
     * DELETE /api/appointments/{id}
     * Cancel / delete an appointment.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::where('appointment_id', $id)->firstOrFail();
        $appointment->delete();

        return response()->json(['message' => 'Appointment cancelled.'], Response::HTTP_OK);
    }
}

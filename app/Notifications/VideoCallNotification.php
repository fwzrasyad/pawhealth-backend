<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\FcmChannel;

class VideoCallNotification extends Notification
{
    use Queueable;

    protected string $appointmentId;
    protected string $type;    // 'incoming_call' or 'call_ended'
    protected ?string $channel;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $appointmentId, string $type, ?string $channel = null)
    {
        $this->appointmentId = $appointmentId;
        $this->type = $type;
        $this->channel = $channel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->type === 'incoming_call'
            ? 'Incoming Video Call'
            : 'Video Call Ended';

        $body = $this->type === 'incoming_call'
            ? 'Your veterinarian is calling you. Tap to join the video consultation.'
            : 'The video consultation has ended.';

        return [
            'appointment_id' => $this->appointmentId,
            'type'           => $this->type,
            'channel'        => $this->channel,
            'title'          => $title,
            'body'           => $body,
        ];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable)
    {
        $title = $this->type === 'incoming_call'
            ? 'Incoming Video Call'
            : 'Video Call Ended';

        $body = $this->type === 'incoming_call'
            ? 'Your veterinarian is calling you. Tap to join the video consultation.'
            : 'The video consultation has ended.';

        return [
            'title' => $title,
            'body'  => $body,
            'data'  => [
                'appointment_id' => (string) $this->appointmentId,
                'type'           => $this->type,
                'channel'        => (string) ($this->channel ?? ''),
            ],
        ];
    }
}

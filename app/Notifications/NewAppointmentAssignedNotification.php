<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\FcmChannel;

class NewAppointmentAssignedNotification extends Notification
{
    use Queueable;

    protected $appointment_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($appointment_id)
    {
        $this->appointment_id = $appointment_id;
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
        return [
            'appointment_id' => $this->appointment_id,
            'title' => 'New Appointment Assigned',
            'body' => 'You have been assigned a new appointment.',
        ];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable)
    {
        return [
            'title' => 'New Appointment Assigned',
            'body' => 'You have been assigned a new appointment.',
            'data' => [
                'appointment_id' => (string) $this->appointment_id,
            ],
        ];
    }
}

<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Check if the notifiable model has an FCM token
        if (! method_exists($notifiable, 'routeFCM')) {
            return;
        }

        $token = $notifiable->routeFCM();

        if (! $token) {
            return;
        }

        // Get the payload from the notification class
        $messageData = $notification->toFirebase($notifiable);

        $fcmNotification = FcmNotification::create(
            $messageData['title'] ?? '',
            $messageData['body'] ?? ''
        );

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($fcmNotification);

        if (isset($messageData['data']) && is_array($messageData['data'])) {
            $message = $message->withData($messageData['data']);
        }

        try {
            $messaging = app('firebase.messaging');
            $messaging->send($message);
        } catch (\Throwable $e) {
            // Log the error or handle it as necessary
            \Illuminate\Support\Facades\Log::error('FCM Notification Error: ' . $e->getMessage());
        }
    }
}

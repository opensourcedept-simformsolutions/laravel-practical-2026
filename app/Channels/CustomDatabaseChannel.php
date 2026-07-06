<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CustomDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        $data = method_exists($notification, 'toArray') ? $notification->toArray($notifiable) : [];
        
        $title = 'System Notification';
        if (method_exists($notification, 'toTitle')) {
            $title = $notification->toTitle($notifiable);
        } elseif (isset($data['title'])) {
            $title = $data['title'];
        }

        $message = '';
        if (method_exists($notification, 'toMessage')) {
            $message = $notification->toMessage($notifiable);
        } elseif (isset($data['message'])) {
            $message = $data['message'];
        }

        return $notifiable->notifications()->create([
            'type' => get_class($notification),
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }
}

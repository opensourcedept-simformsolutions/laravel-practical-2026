<?php

namespace App\Notifications;

use App\Models\ApiKey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApiKeyRotatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ApiKey $oldApiKey, public ApiKey $newApiKey) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('API Key Rotated successfully')
            ->line("Your API Key '{$this->oldApiKey->name}' has been rotated.")
            ->line("A new API key has been generated: '{$this->newApiKey->key}'.")
            ->line("The old key will continue to function during a grace period until {$this->oldApiKey->rotation_grace_expires_at->toDateTimeString()}.")
            ->line('After this grace period, the old key will be automatically revoked.')
            ->action('Manage API Keys', url('/admin/api-keys'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your API Key '{$this->oldApiKey->name}' has been rotated. Grace period ends at {$this->oldApiKey->rotation_grace_expires_at->toDateTimeString()}.",
            'old_api_key_id' => $this->oldApiKey->id,
            'new_api_key_id' => $this->newApiKey->id,
            'rotation_grace_expires_at' => $this->oldApiKey->rotation_grace_expires_at->toDateTimeString(),
        ];
    }
}

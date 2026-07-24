<?php

namespace App\Notifications;

use App\Models\ApiKey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApiKeyExpiryWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ApiKey $apiKey) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('API Key Expiration Warning')
            ->line("Your API Key '{$this->apiKey->name}' (Key: {$this->apiKey->key}) is set to expire on {$this->apiKey->expires_at->toDateTimeString()}.")
            ->line('Please rotate or update your key to avoid service disruption.')
            ->action('Manage API Keys', url('/admin/api-keys'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your API Key '{$this->apiKey->name}' is set to expire on {$this->apiKey->expires_at->toDateTimeString()}.",
            'api_key_id' => $this->apiKey->id,
            'expires_at' => $this->apiKey->expires_at->toDateTimeString(),
        ];
    }
}

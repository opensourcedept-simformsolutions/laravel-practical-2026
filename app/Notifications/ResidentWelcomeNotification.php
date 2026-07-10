<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class ResidentWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [60];

    public function __construct(public User $resident) {}

    public function via(object $notifiable): array
    {
        return [CustomDatabaseChannel::class, 'mail'];
    }

    public function toTitle(object $notifiable): string
    {
        return 'Welcome to SocietyMS';
    }

    public function toMessage(object $notifiable): string
    {
        return 'Welcome to SocietyMS! Your resident account has been set up successfully.';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);

        $url = route(
            'password.reset',
            [
                'token' => $token,
                'email' => $notifiable->email,
            ]
        );

        return (new MailMessage)
            ->subject('Welcome to SocietyMS')
            ->view(
                'emails.resident-welcome',
                [
                    'user' => $notifiable,
                    'url' => $url,
                ]
            );
    }

    public function failed(Throwable $exception): void
    {
        Log::critical(
            'Resident welcome email failed permanently',
            [
                'resident_id' => $this->resident->id,
                'resident_name' => $this->resident->name,
                'resident_email' => $this->resident->email,
                'error' => $exception->getMessage(),
            ]
        );
    }

    public function toArray(object $notifiable): array
    {
        return [

        ];
    }
}

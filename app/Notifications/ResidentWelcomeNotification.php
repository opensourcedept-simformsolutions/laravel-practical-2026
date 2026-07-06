<?php

namespace App\Notifications;

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

    // here backoff is used to provide a wait between all 3 attempts 1,5,10 minutes respectively
    public $backoff = [60];

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $resident)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\CustomDatabaseChannel::class, 'mail'];
    }

    public function toTitle(object $notifiable): string
    {
        return "Welcome to SocietyMS";
    }

    public function toMessage(object $notifiable): string
    {
        return "Welcome to SocietyMS! Your resident account has been set up successfully.";
    }

    /**
     * Get the mail representation of the notification.
     */
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

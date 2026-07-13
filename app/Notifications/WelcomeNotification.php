<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    public function __construct(private readonly User $user) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Welcome to {$appName}")
            ->greeting("Hello {$this->user->full_name},")
            ->line("Thank you for registering at {$appName}. Your account has been created successfully and is ready to use.")
            ->action('Go to Dashboard', url($this->user->role->homeUrl()))
            ->line('If you did not create this account, please ignore this email or contact our support team.');
    }
}

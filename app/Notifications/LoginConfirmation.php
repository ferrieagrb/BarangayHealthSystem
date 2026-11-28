<?php

namespace App\Notifications;

use App\Models\LoginChallenge;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class LoginConfirmation extends Notification
{
    public function __construct(public LoginChallenge $challenge, public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function reviewUrl(): string
    {
        return URL::temporarySignedRoute('login.confirmation.review', $this->challenge->expires_at, [
            'challenge' => $this->challenge->id,
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('BHEAMS: Is this you signing in?')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Someone entered the correct password for your BHEAMS account. Is this you?')
            ->line('Time: '.$this->challenge->created_at->copy()->timezone('Asia/Manila')->format('M j, Y g:i A').' (Philippine time)')
            ->line('IP address: '.$this->challenge->ip_address)
            ->line('Browser/device: '.$this->challenge->user_agent)
            ->action('Review sign-in: Yes / No', $this->reviewUrl())
            ->line('If this is you, choose "Yes, this is me" on the review page, then enter this code in the browser where you started signing in:')
            ->line($this->code)
            ->line('Both confirmation and code are required. They expire in 10 minutes. Never share this code.')
            ->line('If this was not you, choose "No, this is not me" to block this attempt, then reset your password.');
    }
}

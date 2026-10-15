<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInvitation extends Notification
{
    public function __construct(public string $token) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Witamy w systemie CopyCabana')
            ->greeting('Witamy w systemie CopyCabana!')
            ->line('Utworzyliśmy dla Ciebie konto administratora. Kliknij poniższy przycisk, aby wygenerować hasło i zalogować się do panelu.')
            ->action('Wygeneruj hasło', $this->passwordUrl($notifiable))
            ->line('Link jest ważny przez '.config('auth.passwords.users.expire').' minut. Po tym czasie poproś o nowy, uruchamiając zaproszenie ponownie.')
            ->salutation('Zespół CopyCabana');
    }

    private function passwordUrl(object $notifiable): string
    {
        /** @var User $notifiable */
        return $notifiable->adminPasswordResetUrl($this->token);
    }
}

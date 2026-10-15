<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;

class AdminResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        /** @var User $notifiable */
        return $notifiable->adminPasswordResetUrl($this->token);
    }
}

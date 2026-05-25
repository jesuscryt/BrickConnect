<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación de restablecimiento de contraseña en español.
 * Sobreescribe la notificación por defecto de Laravel.
 */
class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Construye el contenido del email.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Restablece tu contraseña - BrickConnect')
            ->greeting('¡Hola!')
            ->line('Has solicitado restablecer la contraseña de tu cuenta en BrickConnect.')
            ->action('Restablecer contraseña', $resetUrl)
            ->line('Este enlace caduca en ' . config('auth.passwords.users.expire', 60) . ' minutos.')
            ->line('Si no solicitaste este cambio, puedes ignorar este correo. Tu cuenta sigue protegida.')
            ->salutation('Un saludo, el equipo de BrickConnect');
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = config('app.frontend_url', 'http://localhost:5173')
            . '/reset-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Сброс пароля — VPN Panel')
            ->greeting('Здравствуйте, ' . $notifiable->first_name . '!')
            ->line('Вы запросили сброс пароля в VPN Panel.')
            ->action('Сбросить пароль', $url)
            ->line('Ссылка действительна 60 минут.')
            ->line('Если вы не запрашивали сброс — проигнорируйте это письмо.')
            ->salutation('VPN Panel');
    }
}
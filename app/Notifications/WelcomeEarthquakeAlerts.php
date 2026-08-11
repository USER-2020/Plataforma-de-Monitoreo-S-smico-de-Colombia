<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEarthquakeAlerts extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $magnitude = $notifiable->min_magnitude > 0 ? 'magnitud '.$notifiable->min_magnitude.' o superior' : 'todas las magnitudes';
        $area = $notifiable->department ?: 'toda Colombia';

        return (new MailMessage)
            ->subject('Bienvenido a las alertas sísmicas de terracosismos')
            ->greeting('Hola '.$notifiable->name)
            ->line('Tu registro fue confirmado correctamente.')
            ->line("Recibirás alertas para {$magnitude} en {$area}.")
            ->line('Los eventos son preliminares y no constituyen una predicción ni una alerta oficial.')
            ->action('Abrir monitor sísmico', route('dashboard'))
            ->line('Gracias por usar terracosismos.');
    }
}

<?php

namespace App\Notifications;

use App\Models\Earthquake;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEarthquakeAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Earthquake $earthquake) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerta sísmica: M '.$this->earthquake->magnitude.' en Colombia')
            ->greeting('Hola '.$notifiable->name)
            ->line('Registramos un nuevo evento que coincide con tus preferencias de alerta.')
            ->line('Magnitud: '.$this->earthquake->magnitude)
            ->line('Ubicación: '.$this->earthquake->place)
            ->line('Profundidad: '.$this->earthquake->depth_km.' km')
            ->action('Ver evento', route('earthquakes.show', $this->earthquake));
    }
}

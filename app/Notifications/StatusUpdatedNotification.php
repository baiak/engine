<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;

class StatusUpdatedNotification extends FilamentNotification
{
    use Queueable;

    protected $serviceLabor;
    protected $originalStatus;
    protected $newStatus;

    public function __construct($serviceLabor, $originalStatus, $newStatus)
    {
        $this->serviceLabor = $serviceLabor;
        $this->originalStatus = $originalStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['database']; // Você pode adicionar 'mail' se quiser enviar por e-mail também
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Mao de obra alterada',
            'body' => "O status de {$this->id} foi alterado de '{$this->originalStatus}' para '{$this->newStatus}'.",
            'user_id' => auth()->id(), ];
    }

   /* public function toArray($notifiable): array
    {
        return [
            'title' => 'OPAA Status Atualizado',
            'body' => "O status de {$this->serviceLabor->title} foi alterado de '{$this->originalStatus}' para '{$this->newStatus}'.",
            'user_id' => auth()->user()
        ];
    }*/

   /* public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Status Atualizado')
            ->line("O status de {$this->serviceLabor->title} foi alterado de '{$this->originalStatus}' para '{$this->newStatus}'.")
            ->action('Ver Detalhes', url('/status')) // Altere a URL conforme necessário
            ->line('Obrigado por usar nosso aplicativo!');
    }*/
 }

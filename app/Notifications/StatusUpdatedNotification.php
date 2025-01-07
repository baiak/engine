<?php

namespace App\Notifications;

use AllowDynamicProperties;
use App\Filament\Resources\OrderResource;
use App\Models\ServiceLabor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;

#[AllowDynamicProperties] class StatusUpdatedNotification extends FilamentNotification
{
    use Queueable;

    protected $serviceLabor;
    protected $originalStatus;
    protected $newStatus;

    protected $order_id;

    protected $makeOrderLink;

    protected $userName;
    protected $orderNumber;


    /**
     * Construtor da notificação.
     *
     * @param object $serviceLabor Representa a mão de obra cujo status foi alterado.
     * @param string $originalStatus O status anterior.
     * @param string $newStatus O novo status.
     */
    public function __construct($serviceLabor, $originalStatus, $newStatus, $order_id, $userName, $orderNumber)
    {
        $this->serviceLabor = $serviceLabor;
        $this->originalStatus = $originalStatus;
        $this->newStatus = $newStatus;
        $this->order_id = $order_id;
        $this->userName = $userName;
        $this->orderNumber = $orderNumber;
        $this->makeOrderLink = '
        <a href="'.OrderResource::getUrl('edit',['record'=> $this->order_id]). '" style="color: #6969e8; text-decoration: underline">' .$this->orderNumber.'</a>';
    }

    /**
     * Define os canais de notificação.
     *
     * @param mixed $notifiable O notificado.
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // Adicione 'mail' se quiser enviar por e-mail também.
    }

    /**
     * Define os dados que serão salvos no banco de dados.
     *
     * @param mixed $notifiable O notificado.
     * @return array
     */

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Mão de obra alterada',
            'body' => "O '{$this->userName}' alterou o status de um serviço para:'{$this->newStatus}' na ordem:
            '{$this->makeOrderLink}'",
            'user_id' => auth()->check() ? auth()->id() : null, // Valida a presença de um usuário autenticado.MUDANÇA->O ID DO USUARIO SERA REPASSADA NO CONSTRUTOR
            'order_id' => $this->order_id,
        ];
    }

    /**
     * (Opcional) Define os dados para notificação por array.
     *
     * @param mixed $notifiable O notificado.
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Status Atualizado',
            'body' => "O status de '{$this->serviceLabor->title}' foi alterado de '{$this->originalStatus}' para '{$this->newStatus}'.",
            'user_id' => auth()->check() ? auth()->id() : null,
        ];
    }

    /**
     * (Opcional) Define os dados para notificação por e-mail.
     *
     * @param mixed $notifiable O notificado.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Status Atualizado')
            ->line("O status de '{$this->serviceLabor->title}' foi alterado de '{$this->originalStatus}' para '{$this->newStatus}'.")
            ->action('Ver Detalhes', url('/status')) // Substitua a URL pelo caminho correto.
            ->line('Obrigado por usar nosso aplicativo!');
    }
}

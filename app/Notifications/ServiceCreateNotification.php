<?php

namespace App\Notifications;

use AllowDynamicProperties;
use App\Filament\Resources\OrderResource;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

#[AllowDynamicProperties] class ServiceCreateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $order;
    protected $userName;
    protected $userId;
    protected $orderId;
    protected $orderNumber;


    /**
     * Construtor da notificação.
     *
     * @param object $service Instância.
     * @param string $userNameAuth Nome do usuário que adicionou o servico
     * @param string $userId ID do usuário que adicionou o servico
     * @param integer $orderId ID da ordem de serviço.
     * @param integer $orderNumber Número da ordem de serviço.
     */
    public function __construct(object $service, string $userNameAuth, string $userId, int $orderId, int $orderNumber)
    {
        $this->service = $service;
        $this->userNameAuth = $userNameAuth;
        $this->userId = $userId;
        $this->orderId = $service->order_id;
        $this->orderNumber = $orderNumber;
        $this->makeOrderLink = '
        <a href="'.OrderResource::getUrl('edit',['record'=> $this->orderId]). '" style="color: #6969e8; text-decoration: underline">' .$this->orderNumber.'</a>';
        $this->userName = User::findOrFail($userId)->name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }
    /**
     * Define os dados que serão salvos no banco de dados.
     *
     * @param mixed $notifiable O notificado.
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'title' => 'Serviço adicionado',
            'body' => "<b>{$this->userNameAuth}</b> adicionou um serviço para <b>{$this->userName}</b> na ordem : {$this->makeOrderLink}.<p><small>em: {$this->service->created_at->format('d/m/Y - H:i:s')}</small></p>",
            'user_id' => $this->userId,
            'order_id' => $this->orderId,

        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

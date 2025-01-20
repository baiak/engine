<?php

namespace App\Notifications;

use AllowDynamicProperties;
use App\Filament\Resources\OrderResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Notification as FilamentNotification;

#[AllowDynamicProperties] class ServiceLaborCreateNotification extends FilamentNotification
{
    use Queueable;

    protected $order;
    protected $userName;
    protected $userId;
    protected $orderId;
    protected $orderNumber;
    protected $serviceLabor;
    protected $makeOrderLink;


    /**
     * Construtor da notificação.
     *
     * @param object $serviceLabor Instância.
     * @param string $userName Nome do usuário que adicionou a mão de obra.
     * @param string $userId ID do usuário que adicionou a mão de obra.
     * @param integer $orderId ID da ordem de serviço.
     * @param integer $orderNumber Número da ordem de serviço.
     */
    public function __construct(object $serviceLabor, string $userName, string $userId, int $orderId, int $orderNumber)
    {
        $this->serviceLabor = $serviceLabor;
        $this->userName = $userName;
        $this->userId = $userId;
        $this->orderId = $serviceLabor->order_id;
        $this->orderNumber = $orderNumber;
        $this->makeOrderLink = '
        <a href="'.OrderResource::getUrl('edit',['record'=> $this->orderId]). '" style="color: #6969e8; text-decoration: underline">' .$this->orderNumber.'</a>';

    }

    /**
     * Define os canais de notificação.
     *
     * @param mixed $notifiable O notificado.
     * @return array
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Define os dados que serão salvos no banco de dados.
     *
     * @param mixed $notifiable O notificado.
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Mão de obra adicionada',
        'body' => "<b>{$this->userName}</b>  adicionou uma nova <u>mão de obra</u> na ordem de serviço: {$this->makeOrderLink}.<p><small>em: {$this->serviceLabor->created_at->format('d/m/Y - H:i:s')}</small></p>",
            'user_id' => $this->userId,
            'order_id' => $this->orderId,
            'service_labor_id' => $this->serviceLabor->id ?? null,
        ];
    }


}

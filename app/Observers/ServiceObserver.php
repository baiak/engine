<?php

namespace App\Observers;

use App\Enums\TypeOfServiceStatus;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use App\Notifications\ServiceCreateNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    /**
     * Lida com o evento "created" do Service.
     * Este método agora é o ÚNICO responsável por registrar o log de CRIAÇÃO.
     */
    public function created(Service $service): void
    {
        // 1. Registra o log de criação
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'order_id' => $service->order_id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => json_encode($service->getAttributes()),
            'user_id' => Auth::id(),
            'created_at' => now(), 
        ]);

        // 2. Envia as notificações
        try {
            $service->load(['user', 'order']); // Garante que as relações estejam carregadas
            $userAuth = Auth::user();

            // Notifica o usuário responsável pelo serviço
            $service->user->notify(new ServiceCreateNotification(
                $service,
                $userAuth->name,
                $service->user->id,
                $service->order_id,
                $service->order->order_number
            ));

            // Notifica todos os administradores
            $adminUsers = User::where('is_admin', 1)->get();
            foreach ($adminUsers as $adminUser) {
                if ($adminUser->id !== $service->user->id) { 
                    $adminUser->notify(new ServiceCreateNotification(
                        $service,
                        $userAuth->name,
                        $service->user->id,
                        $service->order_id,
                        $service->order->order_number
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de criação de serviço:', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
            ]);
        }
    }

    /**
     * Lida com o evento "saved" do Service.
     * A lógica agora só é executada para ATUALIZAÇÕES, evitando duplicidade.
     */
    public function saved(Service $service): void
    {
       
        if ($service->wasRecentlyCreated) {
            return;
        }

        
        if ($service->wasChanged()) { // Otimização: só loga se algo realmente mudou
            DB::table('service_audit_logs')->insert([
                'service_id' => $service->id,
                'order_id' => $service->order_id,
                'action' => 'updated',
                'old_values' => json_encode($service->getOriginal()),
                'new_values' => json_encode($service->getChanges()),
                'user_id' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        // Lógica para sincronizar status com "service labors" (se o status mudou)
        if ($service->wasChanged('status')) {
            $newStatusValue = $service->status->value;

            if (in_array($newStatusValue, [TypeOfServiceStatus::aprovado->value, TypeOfServiceStatus::pendente->value])) {
                ServiceLabor::where('service_id', $service->id)
                    ->where('status', '!=', $newStatusValue)
                    ->update(['status' => $newStatusValue]);
            }
        }
    }

    /**
     * Lida com o evento "deleted" do Service.
     */
    public function deleted(Service $service): void
    {
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'order_id' => $service->order_id,
            'action' => 'deleted',
            'old_values' => json_encode($service->getAttributes()),
            'new_values' => null,
            'user_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}
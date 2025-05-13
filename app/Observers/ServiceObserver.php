<?php

namespace App\Observers;

use App\Enums\TypeOfServiceStatus;
use App\Models\Department;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use App\Notifications\ServiceCreateNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    public function created(Service $service): void
    {
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'order_id' => $service->order_id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => json_encode($service->getAttributes()),
            'user_id' => Auth::id(),
            'created_at' => now(),
        ]);

        try {
            $userData = User::findOrFail($service->user);
            $userAuth = User::findOrFail(Auth::id());

            // Envia notificação para o usuário associado ao serviço
            $userData->notify(new ServiceCreateNotification(
                $service,
                $userAuth->name,
                $userData->id,
                $service->order_id,
                $service->order->order_number
            ));

            // Envia notificação para todos os administradores
            $adminUsers = User::where('is_admin', 1)->get();
            foreach ($adminUsers as $adminUser ) {
                $adminUser ->notify(new ServiceCreateNotification(
                    $service,
                    $userAuth->name,
                    $userData->id,
                    $service->order_id,
                    $service->order->order_number
                ));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação:', [
                'message' => $e->getMessage(),
                'user_id' => $service->user,
                'service_id' => $service->id,
            ]);
        }
    }

    public function updated(Service $service): void
    {
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'order_id' => $service->order_id,
            'action' => 'updated',
            'old_values' => json_encode($service->getOriginal()),
            'new_values' => json_encode($service->getChanges()),
            'user_id' => Auth::id(),
            'created_at' => now(),
        ]);

        if ($service->isDirty('status')) {
            $getStatus = $service->getChanges()['status'];
            Log::info("Status mudado: " . $getStatus);

            if (in_array($getStatus, ['Aprovado', 'Pendente'])) {
                $serviceLaborData = ServiceLabor::where('service_id', $service->id)->get();

                /*foreach ($serviceLaborData as $serviceLabor) {
                    DB::table('service_labors')
                        ->where('id', $serviceLabor->id)
                        ->update(['status' => $getStatus]);
                }*/
                foreach ($serviceLaborData as $serviceLabor) {
                    if ($serviceLabor->status !== $getStatus) {
                        $serviceLabor->update(['status' => $getStatus]);
                    }
                }
            }
        }
    }

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

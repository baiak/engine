<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\ServiceLaborLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceLaborObserver
{
    /**
     * Handle the ServiceLabor "created" event.
     */
    public function created(ServiceLabor $serviceLabor):void
    {

        DB::table('service_labor_logs')->insert([
            'service_labor_id' => $serviceLabor->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => json_encode($serviceLabor->getAttributes()),
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Handle the ServiceLabor "updated" event.
     */
    public function updated(ServiceLabor $serviceLabor): void
    {
        // Verifica se o status da mão de obra foi alterado para algo diferente de 'Aprovado'
        if ($serviceLabor->status !== 'Aprovado') {
            // Obtém o ID do serviço relacionado
           // Log::info('o status nao é aprovado, o service_id é '.$serviceLabor->service_id);
            $serviceId = $serviceLabor->service_id;

            // Verifica na tabela `services` se o serviço está com status 'Aprovado'
            $service = Service::where('id', $serviceId)->firstOrFail();

            if ($service->status->value == 'Aprovado') {
                // Atualiza o status do serviço para 'Pendente'
                //$service->update(['status' => 'Pendente']);
                DB::table('services')
                    ->where('id', $serviceId)
                    ->update(['status' => 'Pendente']);
            }
        }
       // Log::info('ServiceLabor atualizado event triggered.', ['id' => $serviceLabor->id]);
    }
    public function saved(ServiceLabor $serviceLabor)
    {
        // Sempre verifica se o status da mão de obra atual é 'Aprovado'
        if ($serviceLabor->status === 'Aprovado') {
            $serviceId = $serviceLabor->service_id;
    
            // Pega todas as mãos de obra do serviço
            $allLabors = ServiceLabor::where('service_id', $serviceId)->get();
    
            // Verifica se todas estão com status 'Aprovado'
            $allApproved = $allLabors->every(function ($labor) {
                return $labor->status === 'Aprovado';
            });
    
            if ($allApproved) {
                // Atualiza o status do serviço se ele ainda não for 'Aprovado'
                $service = Service::find($serviceId);
                if ($service && $service->status !== 'Aprovado') {
                    $service->updateQuietly(['status' => 'Aprovado']);
                }
            }
        }
    }


    /**
     *
     * Handle the ServiceLabor "deleted" event.
     */
    public function deleted(ServiceLabor $serviceLabor): void
    {
        DB::table('service_labor_logs')->insert([
            'service_labor_id' => $serviceLabor->id,
            'event' => 'deleted',
            'old_values' => json_encode($serviceLabor->getOriginal()),
            'new_values' => json_encode($serviceLabor->getChanges()),
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

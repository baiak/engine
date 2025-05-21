<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\ServiceLaborLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;      

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
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Handle the ServiceLabor "updating" event.
     * This method is called BEFORE the model's changes are saved to the database.
     * Ideal for setting timestamps based on status changes.
     */
    public function updating(ServiceLabor $serviceLabor): void
    {
        //verifica se o status esta mudando.
        if ($serviceLabor->isDirty('status')) {
            $newStatus = $serviceLabor->status; // novo valor do status.
            $originalStatus = $serviceLabor->getOriginal('status'); //status atual na base de dados.

            // so continua se o status for diferente do original.
            if ($newStatus !== $originalStatus) {
                if ($newStatus === 'Aprovado') {
                    $serviceLabor->approvedAt = Carbon::now();
                } elseif ($newStatus === 'Em Andamento') {
                    
                    $serviceLabor->startedAt = Carbon::now();
                } elseif ($newStatus === 'Finalizado') {
                    $serviceLabor->finishedAt = Carbon::now();
                }
            }
        }
    }

    /**
     * Handle the ServiceLabor "updated" event.
     * This method is called AFTER the model's changes have been saved.
     */
    public function updated(ServiceLabor $serviceLabor): void
    {
       
        // Verifica se o status da mão de obra foi alterado para algo diferente de 'Aprovado'
        if ($serviceLabor->status !== 'Aprovado') {
            // Obtém o ID do serviço relacionado
            $serviceId = $serviceLabor->service_id;

            // Verifica na tabela `services` se o serviço está com status 'Aprovado'
     
            $service = Service::where('id', $serviceId)->firstOrFail();

            $isServiceApproved = false;
            
            if (is_object($service->status) && property_exists($service->status, 'value')) {
                $isServiceApproved = ($service->status->value == 'Aprovado');
            } elseif (is_string($service->status)) {
                $isServiceApproved = ($service->status == 'Aprovado');
            }

            if ($isServiceApproved) {
                // Atualiza o status do serviço para 'Pendente'
                DB::table('services')
                    ->where('id', $serviceId)
                    ->update(['status' => 'Pendente']);
            }
        }
       // Log::info('ServiceLabor atualizado event triggered.', ['id' => $serviceLabor->id]);
    }

    /**
     * Handle the ServiceLabor "saved" event.
     * This method is called after the model has been "saved" (created or updated).
     */
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
                if ($service) {
                    $currentServiceStatusIsApproved = false;
                    if (is_object($service->status) && property_exists($service->status, 'value')) {
                        $currentServiceStatusIsApproved = ($service->status->value === 'Aprovado');
                    } elseif (is_string($service->status)) {
                        $currentServiceStatusIsApproved = ($service->status === 'Aprovado');
                    }

                    if (!$currentServiceStatusIsApproved) {
                        $service->updateQuietly(['status' => 'Aprovado', 'approvedAt' => Carbon::now()]);
                    }
                }
            }
        }
    }

    /**
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
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
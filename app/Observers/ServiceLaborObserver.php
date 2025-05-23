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
     */
    public function updating(ServiceLabor $serviceLabor): void
    {
        $originalDatabaseStatus = $serviceLabor->getOriginal('status'); 
        $newProposedStatus = $serviceLabor->status;           

        $protectedStatuses = ['Finalizado', 'Cancelado', 'Em Andamento'];

        if (in_array($originalDatabaseStatus, $protectedStatuses) && $newProposedStatus !== $originalDatabaseStatus) {
            $serviceLabor->status = $originalDatabaseStatus;
        }

        if ($serviceLabor->isDirty('status')) {
            $statusThatWillBeSaved = $serviceLabor->status; 
            if ($statusThatWillBeSaved !== $originalDatabaseStatus) {
                if ($statusThatWillBeSaved === 'Aprovado') {
                    $serviceLabor->approvedAt = Carbon::now();
                } elseif ($statusThatWillBeSaved === 'Em Andamento') {
                    $serviceLabor->startedAt = Carbon::now();
                } elseif ($statusThatWillBeSaved === 'Finalizado') {
                    $serviceLabor->finishedAt = Carbon::now();
                }
            }
        }
    }

    /**
     * Handle the ServiceLabor "updated" event.
     * This method is called AFTER the model's changes have been saved.
     * Agora também gerencia o status do Serviço pai com base nos status de todas as suas Mão de Obra.
     */
    public function updated(ServiceLabor $serviceLabor): void
    {
        $serviceId = $serviceLabor->service_id;
        // Garante que o serviço relacionado seja encontrado, caso contrário, falha (firstOrFail).
        $service = Service::where('id', $serviceId)->firstOrFail();

        // Obtém todas as mão de obra associadas a este serviço.
        $allLabors = ServiceLabor::where('service_id', $serviceId)->get();

        // Define os status de mão de obra que permitem que o Serviço seja 'Aprovado'.
        $permissibleLaborStatusesForServiceApproval = ['Cancelado', 'Aprovado', 'Em Andamento'];

        $canServiceBeApprovedBasedOnLabors = null;

        if ($allLabors->isEmpty()) {
            // REGRA DE NEGÓCIO: Se não houver mão de obra, o serviço pode ser 'Aprovado'?
            // Para este exemplo, vamos definir como falso. Isso significa que um serviço
            // precisa de mão de obra em estados válidos para ser aprovado por esta lógica.
            // Se um serviço sem MDOs puder ser aprovado, mude para true.
            $canServiceBeApprovedBasedOnLabors = false;
        } else {
            // Verifica se TODAS as mão de obra do serviço têm um status dentro dos permissíveis.
            $canServiceBeApprovedBasedOnLabors = $allLabors->every(function ($labor) use ($permissibleLaborStatusesForServiceApproval) {
                return in_array($labor->status, $permissibleLaborStatusesForServiceApproval);
            });
        }

        // Obtém o status atual do serviço (string) para comparação.
        $currentServiceStatusString = null;
        if (is_object($service->status) && property_exists($service->status, 'value')) {
            $currentServiceStatusString = $service->status->value;
        } elseif (is_string($service->status)) {
            $currentServiceStatusString = $service->status;
        }

        if ($canServiceBeApprovedBasedOnLabors) {
            // Se as condições são atendidas para o serviço ser 'Aprovado'.
            if ($currentServiceStatusString !== 'Aprovado') {
                // Atualiza o status do serviço para 'Aprovado' e define a data de aprovação.
                DB::table('services')
                    ->where('id', $serviceId)
                    ->update(['status' => 'Aprovado', 'approvedAt' => Carbon::now()]);
                // Log::info("Service {$serviceId} status updated to Aprovado via ServiceLaborObserver@updated.");
            }
        } else {
            // Se as condições NÃO são atendidas para o serviço ser 'Aprovado'.
            if ($currentServiceStatusString === 'Aprovado') {
                // E o serviço está atualmente 'Aprovado', então mude para 'Pendente'.
                // Também limpa a data de aprovação.
                DB::table('services')
                    ->where('id', $serviceId)
                    ->update(['status' => 'Pendente', 'approvedAt' =>  Carbon::now()]);
                // Log::info("Service {$serviceId} status changed from Aprovado to Pendente via ServiceLaborObserver@updated because labor conditions not met.");
            }
        }
    }

    /**
     * Handle the ServiceLabor "saved" event.
     * This method is called after the model has been "saved" (created or updated).
     */
    public function saved(ServiceLabor $serviceLabor)
    {
        // A lógica original do 'saved' para aprovar o serviço:
        // if ($serviceLabor->status === 'Aprovado') {
        //     $serviceId = $serviceLabor->service_id;
        //     $allLabors = ServiceLabor::where('service_id', $serviceId)->get();
        //     $allApproved = $allLabors->every(function ($labor) {
        //         return $labor->status === 'Aprovado';
        //     });
        //     if ($allApproved) {
        //         $service = Service::find($serviceId);
        //         if ($service) {
        //             $currentServiceStatusIsApproved = false;
        //             if (is_object($service->status) && property_exists($service->status, 'value')) {
        //                 $currentServiceStatusIsApproved = ($service->status->value === 'Aprovado');
        //             } elseif (is_string($service->status)) {
        //                 $currentServiceStatusIsApproved = ($service->status === 'Aprovado');
        //             }
        //             if (!$currentServiceStatusIsApproved) {
        //                 $service->updateQuietly(['status' => 'Aprovado', 'approvedAt' => Carbon::now()]);
        //             }
        //         }
        //     }
        // }
        // NOTA: Com a nova lógica abrangente no método `updated`, a lógica de aprovação de serviço
        // no método `saved` acima pode se tornar redundante ou precisar de revisão para evitar conflitos
        // ou processamento duplicado. O método `saved` é executado após o `updated`.
        // Considere centralizar a lógica de atualização do status do serviço em um único local
        // (preferencialmente `saved` para cobrir eventos de criação e atualização de MDOs)
        // ou garantir que não haja sobreposição conflitante.
        // Por ora, mantive o 'saved' original, mas com a ressalva de que a nova lógica em 'updated'
        // é mais completa para a gestão do status do serviço.
        
        // Para manter a lógica original do 'saved' que lida com o caso específico de uma MDO
        // se tornando 'Aprovado' e verificando se todas as outras também estão 'Aprovado':
        if ($serviceLabor->status === 'Aprovado') {
            $serviceId = $serviceLabor->service_id;

            $allLaborsCheck = ServiceLabor::where('service_id', $serviceId)->get();
            $allLaborsAreStrictlyApproved = $allLaborsCheck->every(function ($labor) {
                return $labor->status === 'Aprovado';
            });

            if ($allLaborsAreStrictlyApproved) {
                $service = Service::find($serviceId);
                if ($service) {
                    $currentServiceStatusIsApproved = false;
                    if (is_object($service->status) && property_exists($service->status, 'value')) {
                        $currentServiceStatusIsApproved = ($service->status->value === 'Aprovado');
                    } elseif (is_string($service->status)) {
                        $currentServiceStatusIsApproved = ($service->status === 'Aprovado');
                    }

                    if (!$currentServiceStatusIsApproved) {
                        // Esta atualização pode ser redundante se o `updated` já o fez,
                        // mas garante a aprovação se a condição mais estrita for atendida aqui.
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
        
        // Adicionado para reavaliar o status do serviço após a exclusão de uma MDO
        // (Lógica similar à de `updated` e `saved`)
        $serviceId = $serviceLabor->service_id;
        $service = Service::find($serviceId);

        if ($service) {
            $allLabors = ServiceLabor::where('service_id', $serviceId)->get();
            $permissibleLaborStatusesForServiceApproval = ['Cancelado', 'Aprovado', 'Em Andamento'];
            $canServiceBeApprovedBasedOnLabors = null;

            if ($allLabors->isEmpty()) {
                $canServiceBeApprovedBasedOnLabors = false; // Ou true, conforme regra de negócio
            } else {
                $canServiceBeApprovedBasedOnLabors = $allLabors->every(function ($labor) use ($permissibleLaborStatusesForServiceApproval) {
                    return in_array($labor->status, $permissibleLaborStatusesForServiceApproval);
                });
            }

            $currentServiceStatusString = null;
            if (is_object($service->status) && property_exists($service->status, 'value')) {
                $currentServiceStatusString = $service->status->value;
            } elseif (is_string($service->status)) {
                $currentServiceStatusString = $service->status;
            }

            if (!$canServiceBeApprovedBasedOnLabors && $currentServiceStatusString === 'Aprovado') {
                // Se as condições não são mais atendidas e o serviço estava aprovado, muda para pendente.
                $service->updateQuietly(['status' => 'Pendente', 'approvedAt' =>  Carbon::now()]);
            } elseif ($canServiceBeApprovedBasedOnLabors && $currentServiceStatusString !== 'Aprovado') {
                 // Se as condições são atendidas e o serviço não estava aprovado, aprova.
                $service->updateQuietly(['status' => 'Aprovado', 'approvedAt' => Carbon::now()]);
            }
        }
    }
} 
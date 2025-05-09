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
        //notificacao
        //$userData = $service->department->user;
        $userData =  User::findOrFail($service->user);
        $userAuth = User::findOrFail(Auth::id());
        // Log::info('USERDATA -'.$userData);
        //envia a notificacao para o usuario selecionado do departamento no form

        //$userDataId = $service->department->user->id;

       // $userData->notify(new ServiceCreateNotification($service, $userData->name, $userData->id, $service->order_id, $service->order->order_number ));
        //iterar sob uma query com filtragem de administrador e enviar notificacao para cada registro encontrado

        //Log::info('Servico criado -'.$userData.'-', $service->toArray());

        try {
            $userData->notify(new ServiceCreateNotification(
                $service,
                $userAuth->name,
                $userData->id,
                $service->order_id,
                $service->order->order_number
            ));
            //envia notificacao para todos os admins
            //// Obtém todos os usuários administradores
            $adminUsers = User::where('is_admin', 1)->get();
            // Itera sobre os usuários e envia a notificação
            foreach ($adminUsers as $userDataFor) {
                $userDataFor->notify(new ServiceCreateNotification(
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
                'user_id' => $userData->id,
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

        $getStatus = $service->getChanges()['status'];
        Log::info("Status mudado olha o json capturado ". $getStatus);

        //logica para quando um servico for arrastado para aprovado, todas as maos de obra sao aprovadas também

            if(in_array($getStatus, ['Aprovado', 'Pendente'])){
               // Log::info("Entrou no if do aprovado o id do servico é ".$service->id);


                //iterar sobre a tabela service_labors para acessar todas as maos de obras deste servico

                $serviceLaborData = ServiceLabor::where('service_id', $service->id)->get();

                foreach($serviceLaborData as $serviceLabor){
                    //Log::info("aqui as maos de obra deste servico ".$serviceLabor->id);
                    DB::table('service_labors')
                        ->where('id', $serviceLabor->id)
                        ->update([
                        'status' => $getStatus,
                    ]);
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
            'user_id' => Auth::id(),  // Armazena o ID do usuário logado
            'created_at' => now(),
        ]);
    }
}

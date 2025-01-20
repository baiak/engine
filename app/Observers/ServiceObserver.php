<?php
namespace App\Observers;

use App\Models\Department;
use App\Models\Service;
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
        $userData =  User::findOrFail($service->department->user->id);
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

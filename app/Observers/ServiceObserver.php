<?php
namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

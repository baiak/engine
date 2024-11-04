<?php
namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServiceObserver
{
    public function created(Service $service): void
    {
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => json_encode($service->getAttributes()),
            'created_at' => now(),
        ]);
    }

    public function updated(Service $service): void
    {
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'action' => 'updated',
            'old_values' => json_encode($service->getOriginal()),
            'new_values' => json_encode($service->getChanges()),
            'created_at' => now(),
        ]);
    }

    public function deleted(Service $service): void
    {
        DB::table('service_audit_logs')->insert([
            'service_id' => $service->id,
            'action' => 'deleted',
            'old_values' => json_encode($service->getAttributes()),
            'new_values' => null,
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Models;


use App\Enums\TypeOfServiceStatus;
use App\Livewire\ServiceStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Log;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Service extends Model implements Sortable
{
    use SortableTrait;
    use HasFactory;
    protected $fillable =
        [
            'order_id',
            'part_id',
            'department_id',
            'deadline',
            'status',
            'description',
            'order_number',
        ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function laborList(): BelongsTo
    {
        return $this->belongsTo(Labor::class);
    }

    public function labor():BelongsToMany
    {
        return $this->belongsToMany(Labor::class, 'service_labors', 'service_id', 'labor_id')
               ->withPivot('id','user_id',
                   'order_id',
                   'service_id',
                   'labor_id',
                   'includedAt',
                   'approvedAt',
                   'startedAt',
                   'finishedAt',
                   'status',
                   'description')
            ->withTimestamps();
    }
    /**
     * Compara valores antigos e novos para retornar uma lista de mudanças.
     *
     * @param string $oldJson JSON com os valores antigos.
     * @param string $newJson JSON com os valores novos.
     * @return array Lista de mudanças com o campo, valor antigo e valor novo.
     */
    public function getChangesFromJson(string $oldJson, string $newJson): array
    {
        // Decodifica os JSONs para arrays associativos
        $oldValues = json_decode($oldJson, true);
        $newValues = json_decode($newJson, true);

        $changes = [];

        // Itera sobre os novos valores para encontrar mudanças
        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            // Verifica se houve alteração
            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ];
            }
        }

        return $changes;
    }
    public function serviceAuditLog(): HasOne
    {
        return $this->hasOne(ServiceAuditLog::class);

    }
    public function statusHistory()
    {
        return $this->hasMany(ServiceAuditLog::class, 'service_id');
    }
    public function getFormattedTitleAttribute(): string
    {
        $orderNumber = $this->order->order_number ?? 'Sem número';
        $clientName = $this->order->client->name ?? 'Cliente Desconhecido';
        $vehicleModel = $this->order->vehicle->factory.'/'.$this->order->vehicle->model ?? 'Carro Desconhecido';

        return "{$orderNumber} - {$vehicleModel} - {$clientName}";
    }


    protected $casts = [
        'status' => TypeOfServiceStatus::class
    ];
}

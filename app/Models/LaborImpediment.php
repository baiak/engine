<?php

namespace App\Models;

use App\Enums\TypeOfLaborImpedimentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborImpediment extends Model
{
    public $timestamps = true;
    use HasFactory;
    protected $fillable = [
        'id',
        'service_labor_id',
        'complainant_id',
        'complained_id',
        'reason',
        'status',
        'observations',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => TypeOfLaborImpedimentStatus::class,
        'logs'=>'json',

    ];

        // Relationship to the user who made the complaint
        public function complainantUser(): BelongsTo
        {
            return $this->belongsTo(User::class, 'complainant_id');
        }
    
        // Relationship to the user who is assigned/complained to
        public function complainedUser(): BelongsTo
        {
            return $this->belongsTo(User::class, 'complained_id');
        }
    
        // Relationship to the specific ServiceLabor entry
        public function serviceLabor(): BelongsTo
        {
            return $this->belongsTo(ServiceLabor::class, 'service_labor_id');
        }

    public static function listImpediments($serviceLaborId){
        return LaborImpediment::where('service_labor_id', $serviceLaborId)->orderBy('id', 'desc')->get();
    }
/*    public static function loadLogs($id)
    {
        $selectLogs = LaborImpediment::where('id',$id)->get();

        $dataLogs = json_decode($selectLogs, true);
        usort($dataLogs, function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        return $dataLogs;

    }*/
    public static function loadLogs($id)
    {
        // Encontra o registro pelo ID
        $record = self::where('id', $id)->first();

        // Verifica se o registro e os logs existem
        if (!$record || !$record->logs) {
            return [];
        }

        // Decodifica o JSON armazenado em 'logs'
        $dataLogs = json_decode($record->logs, true);

        if (!$dataLogs) {
            return [];
        }

        // Ordena os logs pela chave 'date'
        usort($dataLogs, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']); // Ordenação decrescente
        });

        return $dataLogs;
    }



}

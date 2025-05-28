<?php

namespace App\Filament\Resources\LaborImpedimentResource\Pages;

use App\Filament\Resources\LaborImpedimentResource;
use App\Models\User;
use App\Models\Department; // Ensure you have Department model
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Required for throwing validation errors


class CreateLaborImpediment extends CreateRecord
{
    protected static string $resource = LaborImpedimentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $createdImpediments = [];
        $complainantId = Auth::id();

        $initialLogEntry = [
            'date' => now()->toDateTimeString(),
            'user_id' => $complainantId,
            'observation' => $data['description_for_log'],
            'selected_status' => $data['status'],
        ];

        $usersToReceiveImpediment = collect();
        $selectionType = $data['target_selection_type'];

        if ($selectionType === 'system_all_users') {
            $usersToReceiveImpediment = User::all();
        } elseif ($selectionType === 'department_all_users') {
            $departmentId = $data['target_department_id'] ?? null;
            if ($departmentId) {
                $department = Department::find($departmentId);
                if ($department) {
                    // Use the 'users' relationship from your Department model
                    $usersToReceiveImpediment = $department->users()->get(); // Ensure it's a collection
                } else {
                    throw ValidationException::withMessages(['target_department_id' => 'Departamento selecionado não encontrado.']);
                }
            } else {
                 throw ValidationException::withMessages(['target_department_id' => 'Departamento é obrigatório para esta opção.']);
            }
        } elseif ($selectionType === 'department_specific_user') {
            $userId = $data['final_complained_user_id'] ?? null;
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $usersToReceiveImpediment->push($user);
                } else {
                     throw ValidationException::withMessages(['final_complained_user_id' => 'Usuário específico selecionado não encontrado.']);
                }
            } else {
                throw ValidationException::withMessages(['final_complained_user_id' => 'Usuário específico é obrigatório para esta opção.']);
            }
        }

        if ($usersToReceiveImpediment->isEmpty()) {
            throw ValidationException::withMessages([
                'target_selection_type' => 'Nenhum usuário alvo foi determinado com base na seleção. Verifique as opções de direcionamento.',
            ]);
        }

        $firstCreatedImpediment = null;

        foreach ($usersToReceiveImpediment as $user) {
            $impedimentData = [
                'service_labor_id' => $data['service_labor_id'],
                'complainant_id' => $complainantId,
                'complained_id' => $user->id, // Assign the target user's ID
                'reason' => $data['reason'],
                'status' => $data['status'],
                'logs' => [$initialLogEntry],
            ];
            $newImpediment = static::getModel()::create($impedimentData);
            if(!$firstCreatedImpediment) {
                $firstCreatedImpediment = $newImpediment;
            }
            $createdImpediments[] = $newImpediment;
        }
        
        if (empty($createdImpediments) && $firstCreatedImpediment === null) { // Should be caught by isEmpty check above
            Notification::make()
                ->title('Nenhum impedimento foi criado.')
                ->warning()
                ->send();
            return new (static::getModel()); // Return a new model instance to prevent errors
        }

        Notification::make()
            ->title(count($createdImpediments) . ' impedimento(s) criado(s) com sucesso!')
            ->success()
            ->send();

        return $firstCreatedImpediment; // Return the first created model
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
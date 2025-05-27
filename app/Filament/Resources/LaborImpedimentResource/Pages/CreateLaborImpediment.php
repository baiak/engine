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

        // Prepare the initial log entry as per your structure **
        $initialLogEntry = [
            'date' => now()->toDateTimeString(),
            'user_id' => $complainantId,
            'observation' => $data['description_for_log'], // From the temporary form field
            'selected_status' => $data['status'],          // Status chosen in the form **
        ];

        $usersToReceiveImpediment = collect();

        if ($data['target_audience'] === 'all_system_users') {
            $usersToReceiveImpediment = User::all();
        } else {
            // Assumes target_audience stores a department_id
            $departmentId = $data['target_audience'];
            // Get users belonging to the selected department. User model has BelongsToMany departments. **
            $usersToReceiveImpediment = User::whereHas('departments', function ($query) use ($departmentId) {
                $query->where('departments.id', $departmentId); // Ensure 'departments.id' is correct join condition
            })->get();
        }

        if ($usersToReceiveImpediment->isEmpty()) {
            // It's good practice to throw a validation exception that Filament can catch and display
            throw ValidationException::withMessages([
                'target_audience' => 'Nenhum usuário encontrado para o público alvo selecionado. Verifique o departamento ou a seleção.',
            ]);
        }

        $firstCreatedImpediment = null;

        foreach ($usersToReceiveImpediment as $user) {
            $impedimentData = [
                'service_labor_id' => $data['service_labor_id'], // **
                'complainant_id' => $complainantId,              // **
                'complained_id' => $user->id,                    // ** The user this impediment is for
                'reason' => $data['reason'],                     // **
                'status' => $data['status'],                     // **
                'logs' => [$initialLogEntry], // Store as an array of log objects; Eloquent will cast to JSON **
                // 'observations' is ignored as requested **
            ];
            $newImpediment = static::getModel()::create($impedimentData);
            if(!$firstCreatedImpediment) {
                $firstCreatedImpediment = $newImpediment;
            }
            $createdImpediments[] = $newImpediment;
        }

        if (empty($createdImpediments)) {
             // This case should ideally be prevented by the isEmpty check above,
             // but as a fallback:
            Notification::make()
                ->title('Nenhum impedimento foi criado.')
                ->warning()
                ->send();
            // Return a new model instance to prevent errors, though this indicates a logic flaw if reached.
            return new (static::getModel());
        }

        Notification::make()
            ->title(count($createdImpediments) . ' impedimento(s) criado(s) com sucesso!')
            ->success()
            ->send();

        // The method expects a single Model instance to be returned for redirection purposes.
        // We return the first one created.
        return $firstCreatedImpediment;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\LaborImpediment;
use App\Enums\TypeOfLaborImpedimentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Contar serviços aguardando lançamento de mão de obra.
        // Assumindo que "aguardando lançamento" significa que não há `ServiceLabor` associado.
        $servicesAwaitingLaborCount = Service::whereDoesntHave('serviceLabors')
            // Opcional: descomente a linha abaixo se quiser filtrar
            // apenas por serviços atribuídos ao departamento do usuário.
            // ->whereIn('department_id', $user->activeDepartments->pluck('id'))
            ->count();


        // 2. Buscar impedimentos abertos para o usuário logado.
        // Busca por impedimentos onde o usuário logado é o "reclamado" (complained_id)
        // e o status é diferente de 'CONCLUIDO'.
        $unresolvedImpediments = LaborImpediment::where('complained_id', $user->id)
            ->where('status', '!=', TypeOfLaborImpedimentStatus::resolvido) // Usando o Enum para segurança
            ->with(['serviceLabor.service.order', 'complainantUser']) // Carrega relacionamentos para exibir mais detalhes
            ->get();

        return view('dashboard', [
            'servicesAwaitingLaborCount' => $servicesAwaitingLaborCount,
            'unresolvedImpediments' => $unresolvedImpediments,
        ]);
    }
}
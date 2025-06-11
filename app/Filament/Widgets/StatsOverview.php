<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\LaborImpediment;
use App\Enums\TypeOfLaborImpedimentStatus;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
     // --- 1. Lógica para buscar os dados ---
        $user = Auth::user();

        // Conta serviços aguardando lançamento de mão de obra
        $servicesAwaitingLaborCount = Service::whereDoesntHave('serviceLabors')->count();

        // Busca impedimentos abertos para o usuário logado
        $unresolvedImpedimentsCount = LaborImpediment::where('complained_id', $user->id)
            ->where('status', '!=', TypeOfLaborImpedimentStatus::resolvido)
            ->count();

        // URL para o resource de Serviços
        $servicesResourceUrl = Filament::getTenant()
            ? url('/') // Use the base URL or adjust as needed for your tenant setup
            : route('filament.admin.resources.services.index');


        // --- 2. Criação dos Cards (Stats) ---
        $stats = [];

        // Card para Serviços Aguardando Mão de Obra
        $stats[] = Stat::make('Serviços Aguardando Lançamento', $servicesAwaitingLaborCount)
            ->description('Serviços que precisam de mão de obra definida')
            ->descriptionIcon('heroicon-m-wrench-screwdriver')
            ->url($servicesResourceUrl) // Adiciona o link no card
            ->color('info');

        // Card para Impedimentos (só aparece se houver algum)
        if ($unresolvedImpedimentsCount > 0) {
            $stats[] = Stat::make('Meus Impedimentos em Aberto', $unresolvedImpedimentsCount)
                ->description('Impedimentos que requerem sua atenção')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->url($servicesResourceUrl) // Pode apontar para a mesma página ou uma com filtro
                ->color('danger');
        }

        return $stats;
    }
}

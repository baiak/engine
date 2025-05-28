<?php
namespace App\Enums;

enum TypeOfLaborImpedimentStatus: string
{
    case em_aberto = 'em aberto';
    case resolvido = 'resolvido';
    case cancelado = 'cancelado';
    case sem_solucao = 'sem solucao';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabels(): array
    {
        return array_map(fn($case) => $case->name, self::cases());

    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}

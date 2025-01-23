<?php

namespace App\Enums;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TypeOfLaborStatus: string implements HasLabel, HasIcon, HasColor
{

    case pendente = 'Pendente';
    case aprovado = 'Aprovado';
    case rejeitado = 'Rejeitado';
    case condenado = 'Condenado';
    case em_andamento = 'Em Andamento';
    case impedido = 'Impedido';
    case finalizado = 'Finalizado';

    public function getLabel(): string
    {
        return match ($this) {
            self::pendente=>'Pendente',
            self::aprovado => 'Aprovado',
            self::rejeitado => 'Rejeitado',
            self::condenado => 'Condenado',
            self::em_andamento => 'Em Andamento',
            self::impedido => 'Impedido',
            self::finalizado => 'Finalizado',
        };
    }
    public function getIcon(): string
    {
        return match ($this) {
            self::pendente => 'pepicon-hourglass-circle',
            self::aprovado => 'heroicon-s-check',
            self::rejeitado => 'uiw-dislike-o',
            self::condenado => 'hugeicons-dead',
            self::em_andamento => 'pepicon-hourglass-circle',
            self::impedido => 'heroicon-o-question-mark-circle',
            self::finalizado => 'elemplus-finished',
        };
    }
    public function getStyle(): string
    {
        return match ($this) {
            self::pendente => 'style="color: #854d0e; font-size: small"',
            self::aprovado => 'style=" color: #065f46; font-size: small"',
            self::rejeitado => 'style=" color: #1f2937; font-size: small"',
            self::condenado => ' style=" color: #1f2937; font-size: small"',
            self::em_andamento => 'style="color: #1e40af; font-size: small"',
            self::impedido => 'style=" color: #991b1b; font-size: small"',
            self::finalizado => 'style="color: #065f46;font-size: small"',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::pendente => '#854d0e',
            self::aprovado => '#065f46',
            self::rejeitado => '#1f2937',
            self::condenado => '#1f2937',
            self::em_andamento => '#1e40af',
            self::impedido => '#991b1b',
            self::finalizado => '#1f2937',

        };
    }



}

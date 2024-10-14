<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TypeOfLaborStatus: string implements HasLabel
{

    case aguardando_aprovacao = 'Aguardando aprovacao';
    case aprovado = 'Aprovado';
    case em_andamento = 'Em Andamento';
    case impedido = 'Impedido';
    case finalizado = 'Finalizado';

    public function getLabel(): string
    {
        return match ($this) {
            self::aguardando_aprovacao=>'Aguardando aprovacao',
            self::aprovado => 'Aprovado',
            self::em_andamento => 'Em Andamento',
            self::impedido => 'Impedido',
            self::finalizado => 'Finalizado',
        };
    }

}

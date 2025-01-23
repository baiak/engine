<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;

enum TypeOfServiceStatus: string implements HasLabel
{
    use IsKanbanStatus;
    case pendente = 'Pendente';
    case aprovado = 'Aprovado';
    case finalizado = 'Finalizado';

    public function getLabel(): string
    {
        return match ($this) {
            self::pendente => 'Pendente',
            self::aprovado => 'Aprovado',
            self::finalizado => 'Finalizado',
        };
    }


}


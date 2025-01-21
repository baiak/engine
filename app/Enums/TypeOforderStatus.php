<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;

enum TypeOforderStatus: string implements HasLabel
{
    use IsKanbanStatus;
    case aguardando_orcamento_servicos = 'Aguardando orçamento de serviços';
    case aguardando_aprovacao_cliente = 'Aguardando aprovacao do cliente';
    case aprovado = 'Aprovado';
    case em_andamento = 'Em Andamento';
    case finalizado = 'Finalizado';

    public function getLabel(): string
    {
        return match ($this) {
            self::aguardando_orcamento_servicos => 'Aguardando orçamento de serviços',
            self::aguardando_aprovacao_cliente=>'Aguardando aprovacao do cliente',
            self::aprovado => 'Aprovado',
            self::em_andamento => 'Em Andamento',
            self::finalizado => 'Finalizado',

        };
    }

}

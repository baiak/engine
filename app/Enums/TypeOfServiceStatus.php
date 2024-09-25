<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TypeOfServiceStatus: string implements HasLabel
{
    case aguardando_orcamento_labor = 'Aguardando orçamento de mão de obra';
    case aguardando_aprovacao_cliente = 'Aguardando aprovacao de mão de obra';
    case aprovado = 'Aprovado';

    case rejeitado = 'Rejeitado';
    case em_andamento = 'Em Andamento';

    case impedido = 'Impedido';
    case finalizado = 'Finalizado';

    case aguardando_retirada = 'Aguardando retirada';

    case entregue = 'Entregue';

    public function getLabel(): string
    {
        return match ($this) {
            self::aguardando_orcamento_labor => 'Aguardando orçamento de mão de obra',
            self::aguardando_aprovacao_cliente=>'Aguardando aprovacao de mao de obra',
            self::aprovado => 'Aprovado',
            self::rejeitado => 'Rejeitado',
            self::em_andamento => 'Em Andamento',
            self::impedido => 'Impedido',
            self::finalizado => 'Finalizado',
            self::aguardando_retirada => 'Aguardando retirada',
            self::entregue => 'Entregue',
        };
    }

}


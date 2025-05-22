<?php

namespace App\Enums;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;


enum TypeOfLaborStatus: string implements HasLabel, HasIcon, HasColor
{
    use IsKanbanStatus;

    case pendente = 'Pendente';
    case Aguardando_aprovacao = 'Aguardando Aprovação';
    case aprovado = 'Aprovado';
    case em_andamento = 'Em Andamento';  
    case cancelado = 'Cancelado';    
    case finalizado = 'Finalizado';

    public function getLabel(): string
    {
        return match ($this) {
            self::pendente=>'Pendente',
            self::Aguardando_aprovacao => 'Aguardando Aprovação',
            self::aprovado => 'Aprovado',           
            self::em_andamento => 'Em Andamento',   
            self::cancelado => 'Cancelado',         
            self::finalizado => 'Finalizado',
        };
    }
    public function getIcon(): string
    {
        return match ($this) {
            self::pendente => 'pepicon-hourglass-circle',
            self::Aguardando_aprovacao => 'heroicon-s-clock',
            self::aprovado => 'heroicon-s-check',
            self::em_andamento => 'pepicon-hourglass-circle',
            self::cancelado => 'uiw-dislike-o',                        
            self::finalizado => 'elemplus-finished',
        };
    }
    public function getStyle(): string
    {
        return match ($this) {
            self::pendente => 'style="padding:3px; background-color:#FFF; border-radius:4px; color: #854d0e; font-size: small"',
            self::Aguardando_aprovacao => 'style="padding:3px; background-color:#FFF; border-radius:4px; color: #854d0e; font-size: small"',
            self::aprovado => 'style="padding:3px; background-color:#FFF; border-radius:4px; color: #065f46; font-size: small"',           
            self::em_andamento => 'style="padding:3px; background-color:#FFF; border-radius:4px; color: #1e40af; font-size: small"',  
            self::cancelado => 'style="padding:3px; background-color:#FFF; border-radius:4px; color:rgb(229, 17, 63); font-size: small"',          
            self::finalizado => 'style="padding:3px; background-color:#FFF; border-radius:4px; color: #065f46;font-size: small"',
            self::CANCELLED => 'style="padding:3px; background-color:#FFF; border-radius:4px; color:rgb(229, 17, 63); font-size: small"',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::pendente => '#854d0e',
            self::aprovado => '#065f46',            
            self::em_andamento => '#1e40af',  
            self::cancelado => '#1f2937',      
            self::finalizado => '#065f46',
            self::Aguardando_aprovacao => '#854d0e',
        };
    }



}

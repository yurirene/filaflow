<?php

namespace App\Fila\Enums;

enum StatusOperador: string
{
    case Ativo = 'ativo';
    case Inativo = 'inativo';

    public function label(): string
    {
        return match ($this) {
            self::Ativo => __('Ativo'),
            self::Inativo => __('Inativo'),
        };
    }

    public function isAtivo(): bool
    {
        return $this === self::Ativo;
    }
}

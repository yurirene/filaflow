<?php

namespace App\Fila\Enums;

enum PrioridadeSenha: string
{
    case Normal = 'normal';
    case Idoso = 'idoso';
    case Pcd = 'pcd';
    case Gestante = 'gestante';

    public function isPreferencial(): bool
    {
        return $this !== self::Normal;
    }
}

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

    public function label(): string
    {
        return match ($this) {
            self::Idoso => '👴 Idoso',
            self::Pcd => '♿ PCD',
            self::Gestante => '🤰 Gestante',
            self::Normal => 'Normal',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Idoso => '👴 Idoso — Preferencial',
            self::Pcd => '♿ PCD — Preferencial',
            self::Gestante => '🤰 Gestante — Preferencial',
            self::Normal => 'Atendimento Normal',
        };
    }

    public static function labelFrom(string $prioridade): string
    {
        return self::tryFrom($prioridade)?->label() ?? 'Preferencial';
    }

    public static function badgeFrom(string $prioridade): string
    {
        return self::tryFrom($prioridade)?->badge() ?? 'Atendimento Normal';
    }
}

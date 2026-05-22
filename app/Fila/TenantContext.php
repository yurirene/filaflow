<?php

namespace App\Fila;

use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    protected static ?string $empresaId = null;

    public static function set(?string $empresaId): void
    {
        self::$empresaId = $empresaId;
    }

    public static function empresaId(): ?string
    {
        if (self::$empresaId) {
            return self::$empresaId;
        }

        $user = Auth::user();

        if ($user?->empresa_id) {
            return $user->empresa_id;
        }

        return Empresa::query()->value('id');
    }

    public static function requireEmpresaId(): string
    {
        $id = self::empresaId();

        if (! $id) {
            throw new \RuntimeException('Nenhuma empresa configurada no tenant.');
        }

        return $id;
    }
}

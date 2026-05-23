<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Empresa extends Model
{
    protected $fillable = [
        'nome',
        'cnpj',
        'ativo',
        'hora_inicio',
        'hora_fim',
        'ticker',
        'reinicio_hora',
        'som',
        'notificacoes',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'notificacoes' => 'array',
        ];
    }

    public static function instancia(): self
    {
        return static::query()->first()
            ?? throw new RuntimeException('Nenhuma clínica configurada. Execute o seeder ou cadastre em Configurações.');
    }
}

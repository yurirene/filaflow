<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasUuids;

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

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class);
    }

    public function guiches(): HasMany
    {
        return $this->hasMany(Guiche::class);
    }

    public function senhas(): HasMany
    {
        return $this->hasMany(Senha::class);
    }
}

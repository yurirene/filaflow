<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultorio extends Model
{
    protected $fillable = [
        'ala_id',
        'medico_id',
        'numero',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'numero' => 'integer',
        ];
    }

    public function ala(): BelongsTo
    {
        return $this->belongsTo(Ala::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class, 'consultorio_servico');
    }

    public function senhas(): HasMany
    {
        return $this->hasMany(Senha::class);
    }

    public function chamadas(): HasMany
    {
        return $this->hasMany(Chamada::class);
    }

    public function aceitaServico(Servico $servico): bool
    {
        if ($this->servicos()->exists()) {
            return $this->servicos()->whereKey($servico->id)->exists();
        }

        return $this->ala_id === $servico->ala_id;
    }

    public function labelCurto(): string
    {
        return str_pad((string) $this->numero, 2, '0', STR_PAD_LEFT);
    }
}

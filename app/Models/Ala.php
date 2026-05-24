<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ala extends Model
{
    protected $table = 'alas';

    protected $fillable = [
        'nome',
        'ativo',
        'is_consultorio',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'is_consultorio' => 'boolean',
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

    public function consultorios(): HasMany
    {
        return $this->hasMany(Consultorio::class);
    }

    public function scopeAtiva(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeConsultorio(Builder $query): Builder
    {
        return $query->where('is_consultorio', true);
    }
}

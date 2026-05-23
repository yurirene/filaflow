<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ala extends Model
{
    protected $table = 'alas';

    protected $fillable = [
        'nome',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
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
}

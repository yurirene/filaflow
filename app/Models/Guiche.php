<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guiche extends Model
{
    protected $fillable = [
        'ala_id',
        'numero',
        'descricao',
        'servico_padrao_id',
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

    public function servicoPadrao(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'servico_padrao_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamada extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'senha_id',
        'guiche_id',
        'consultorio_id',
        'operador_id',
        'medico_id',
        'chamada_em',
        'rechamada_vezes',
    ];

    protected function casts(): array
    {
        return [
            'chamada_em' => 'immutable_datetime',
            'rechamada_vezes' => 'integer',
        ];
    }

    public function senha(): BelongsTo
    {
        return $this->belongsTo(Senha::class);
    }

    public function guiche(): BelongsTo
    {
        return $this->belongsTo(Guiche::class);
    }

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }
}

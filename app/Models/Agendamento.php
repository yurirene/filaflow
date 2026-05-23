<?php

namespace App\Models;

use App\Fila\Enums\StatusAgendamento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agendamento extends Model
{
    protected $fillable = [
        'paciente_nome',
        'paciente_celular',
        'servico_id',
        'data_hora',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusAgendamento::class,
            'data_hora' => 'immutable_datetime',
        ];
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}

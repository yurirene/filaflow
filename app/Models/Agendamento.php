<?php

namespace App\Models;

use App\Fila\Enums\StatusAgendamento;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agendamento extends Model
{
    use BelongsToEmpresa;
    use HasUuids;

    protected $fillable = [
        'empresa_id',
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

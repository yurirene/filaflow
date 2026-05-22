<?php

namespace App\Models;

use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Enums\StatusSenha;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Senha extends Model
{
    use BelongsToEmpresa;
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'codigo',
        'servico_id',
        'prioridade',
        'is_preferencial',
        'is_agendado',
        'status',
        'paciente_celular',
        'emitida_em',
        'chamada_em',
        'finalizada_em',
        'ordem_fila',
    ];

    protected function casts(): array
    {
        return [
            'prioridade' => PrioridadeSenha::class,
            'status' => StatusSenha::class,
            'is_preferencial' => 'boolean',
            'is_agendado' => 'boolean',
            'emitida_em' => 'immutable_datetime',
            'chamada_em' => 'immutable_datetime',
            'finalizada_em' => 'immutable_datetime',
            'ordem_fila' => 'integer',
        ];
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function chamadas(): HasMany
    {
        return $this->hasMany(Chamada::class);
    }

    public function scopeAguardando($query)
    {
        return $query->where('status', StatusSenha::Aguardando);
    }
}

<?php

namespace App\Models;

use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Enums\StatusSenha;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Senha extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'servico_id',
        'consultorio_id',
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

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function scopeFilaGuiche($query, int $servicoId)
    {
        return $query->aguardando()
            ->where('servico_id', $servicoId)
            ->whereNull('consultorio_id');
    }

    public function scopeFilaConsultorio($query, int $consultorioId, ?int $servicoId = null)
    {
        $query->aguardando()
            ->where('consultorio_id', $consultorioId);

        if ($servicoId) {
            $query->where('servico_id', $servicoId);
        }

        return $query;
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

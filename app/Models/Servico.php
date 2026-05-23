<?php

namespace App\Models;

use App\Fila\Services\TempoMedioServico;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Servico extends Model
{
    protected $fillable = [
        'ala_id',
        'nome',
        'prefixo',
        'cor',
        'icone',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /** Tempo médio de atendimento (chamada → finalização), calculado pelo servidor. */
    protected function tempoMedioMinutos(): Attribute
    {
        return Attribute::get(
            fn (): int => app(TempoMedioServico::class)->paraServico($this->id),
        );
    }

    public function ala(): BelongsTo
    {
        return $this->belongsTo(Ala::class);
    }

    public function senhas(): HasMany
    {
        return $this->hasMany(Senha::class);
    }

    public function regraIntercalacao(): HasOne
    {
        return $this->hasOne(RegraIntercalacao::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Servico extends Model
{
    use BelongsToEmpresa;
    use HasUuids;

    protected $fillable = [
        'empresa_id',
        'nome',
        'prefixo',
        'ala',
        'tempo_medio_minutos',
        'cor',
        'icone',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'tempo_medio_minutos' => 'integer',
        ];
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

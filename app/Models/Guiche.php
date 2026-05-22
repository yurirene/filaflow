<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guiche extends Model
{
    use BelongsToEmpresa;
    use HasUuids;

    protected $fillable = [
        'empresa_id',
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

    public function servicoPadrao(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'servico_padrao_id');
    }
}

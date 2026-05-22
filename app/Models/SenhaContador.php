<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SenhaContador extends Model
{
    use BelongsToEmpresa;
    use HasUuids;

    public $timestamps = false;

    protected $table = 'senha_contadores';

    protected $fillable = [
        'empresa_id',
        'servico_id',
        'data',
        'ultimo_numero',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'ultimo_numero' => 'integer',
        ];
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegraIntercalacao extends Model
{
    public $timestamps = false;

    protected $table = 'regras_intercalacao';

    protected $fillable = [
        'servico_id',
        'normais_por_ciclo',
        'preferenciais_por_ciclo',
        'ciclo_atual',
    ];

    protected function casts(): array
    {
        return [
            'normais_por_ciclo' => 'integer',
            'preferenciais_por_ciclo' => 'integer',
            'ciclo_atual' => 'integer',
        ];
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}

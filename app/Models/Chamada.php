<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamada extends Model
{
    use BelongsToEmpresa;
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'senha_id',
        'guiche_id',
        'operador_id',
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

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}

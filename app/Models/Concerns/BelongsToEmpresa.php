<?php

namespace App\Models\Concerns;

use App\Fila\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 */
trait BelongsToEmpresa
{
    public static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $builder): void {
            if ($empresaId = TenantContext::empresaId()) {
                $builder->where($builder->getModel()->getTable().'.empresa_id', $empresaId);
            }
        });

        static::creating(function (Model $model): void {
            if (! $model->empresa_id && $empresaId = TenantContext::empresaId()) {
                $model->empresa_id = $empresaId;
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}

<?php

namespace App\Fila\Services;

use App\Models\RegraIntercalacao;
use App\Models\Senha;
use Illuminate\Support\Collection;

/**
 * Algoritmo de intercalação conforme Use Case 2 da documentação.
 */
class SelecionadorProximaSenha
{
    /**
     * @param  Collection<int, Senha>  $filaAguardando  ordenada por emitida_em
     */
    public function selecionar(Collection $filaAguardando, ?RegraIntercalacao $regra): ?Senha
    {
        if ($filaAguardando->isEmpty()) {
            return null;
        }

        if (! $regra) {
            return $filaAguardando->first();
        }

        $normais = $filaAguardando->filter(fn (Senha $s) => ! $s->is_preferencial)->values();
        $preferenciais = $filaAguardando->filter(fn (Senha $s) => $s->is_preferencial)->values();

        if ($preferenciais->isEmpty()) {
            return $normais->first();
        }

        if ($normais->isEmpty()) {
            return $preferenciais->first();
        }

        $cicloTotal = $regra->normais_por_ciclo + $regra->preferenciais_por_ciclo;
        $posNoCiclo = $regra->ciclo_atual % $cicloTotal;

        return $posNoCiclo < $regra->normais_por_ciclo
            ? $normais->first()
            : $preferenciais->first();
    }
}

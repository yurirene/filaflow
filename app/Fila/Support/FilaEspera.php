<?php

namespace App\Fila\Support;

use App\Fila\Services\TempoMedioServico;
use App\Models\Servico;

class FilaEspera
{
    public static function estimativaMinutos(Servico|int $servico, int $tamanhoFila): int
    {
        $servicoId = $servico instanceof Servico ? $servico->id : $servico;
        $tempoMedio = app(TempoMedioServico::class)->paraServico($servicoId);

        return max(1, $tamanhoFila * $tempoMedio);
    }
}

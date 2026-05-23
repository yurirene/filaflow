<?php

namespace App\Fila\Services;

use App\Models\SenhaContador;
use App\Models\Servico;
use Illuminate\Support\Facades\DB;

class GeradorCodigoSenha
{
    public function gerar(Servico $servico): string
    {
        $hoje = now()->toDateString();

        $numero = DB::transaction(function () use ($servico, $hoje): int {
            $contador = SenhaContador::query()
                ->where('servico_id', $servico->id)
                ->where('data', $hoje)
                ->lockForUpdate()
                ->first();

            if (! $contador) {
                $contador = SenhaContador::query()->create([
                    'servico_id' => $servico->id,
                    'data' => $hoje,
                    'ultimo_numero' => 0,
                ]);
            }

            $contador->increment('ultimo_numero');

            return $contador->ultimo_numero;
        });

        return $servico->prefixo.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }
}

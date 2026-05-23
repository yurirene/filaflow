<?php

namespace App\Fila\Queries;

use App\Fila\Support\FilaEspera;
use App\Models\Empresa;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Collection;

class TotemIndexQuery
{
    /**
     * @return array{empresa: Empresa, servicos: Collection<int, Servico>, filasResumo: array<int, array{tamanho: int, esperaMin: int}>}
     */
    public function execute(): array
    {
        $empresa = Empresa::instancia();

        $servicos = Servico::query()
            ->with('ala')
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $contagens = Senha::query()
            ->aguardando()
            ->whereIn('servico_id', $servicos->pluck('id'))
            ->selectRaw('servico_id, count(*) as total')
            ->groupBy('servico_id')
            ->pluck('total', 'servico_id');

        $filasResumo = [];
        foreach ($servicos as $servico) {
            $tamanho = (int) ($contagens[$servico->id] ?? 0);
            $filasResumo[$servico->id] = [
                'tamanho' => $tamanho,
                'esperaMin' => FilaEspera::estimativaMinutos($servico, $tamanho),
            ];
        }

        return [
            'empresa' => $empresa,
            'servicos' => $servicos,
            'filasResumo' => $filasResumo,
        ];
    }
}

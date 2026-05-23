<?php

namespace App\Fila\Queries;

use App\Fila\Enums\StatusSenha;
use App\Models\Senha;
use App\Models\Servico;

class RelatorioResumoQuery
{
    /**
     * @return array{totalHoje: int, emEspera: int, ausentes: int, atendidos: int}
     */
    public function execute(?int $servicoId = null): array
    {
        $hoje = now()->startOfDay();

        $senhasHoje = Senha::query()->where('emitida_em', '>=', $hoje);
        $aguardando = Senha::query()->aguardando();
        $ausentes = Senha::query()
            ->where('status', StatusSenha::Ausente)
            ->where('finalizada_em', '>=', $hoje);
        $atendidos = Senha::query()
            ->where('status', StatusSenha::Finalizado)
            ->where('finalizada_em', '>=', $hoje);

        if ($servicoId) {
            $senhasHoje->where('servico_id', $servicoId);
            $aguardando->where('servico_id', $servicoId);
            $ausentes->where('servico_id', $servicoId);
            $atendidos->where('servico_id', $servicoId);
        }

        return [
            'totalHoje' => $senhasHoje->count(),
            'emEspera' => $aguardando->count(),
            'ausentes' => $ausentes->count(),
            'atendidos' => $atendidos->count(),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, Servico> */
    public function servicosAtivos()
    {
        return Servico::query()->where('ativo', true)->orderBy('nome')->get();
    }
}

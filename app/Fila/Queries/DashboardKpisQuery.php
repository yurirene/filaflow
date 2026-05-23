<?php

namespace App\Fila\Queries;

use App\Fila\Enums\StatusSenha;
use App\Fila\OperadorSessao;
use App\Fila\Services\TempoMedioServico;
use App\Models\Empresa;
use App\Models\Guiche;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Facades\Auth;

class DashboardKpisQuery
{
    /**
     * @return array{
     *     empresa: Empresa,
     *     kpis: array{totalHoje: int, tMedio: int, emEspera: int, ausentes: int, pico: string, guichesAtivos: int},
     *     atendidosHoje: int,
     *     operadorNome: string,
     *     guicheNumero: int,
     *     servicoNome: string
     * }
     */
    public function execute(): array
    {
        $empresa = Empresa::instancia();
        $hoje = now()->startOfDay();

        $emEspera = Senha::query()->aguardando()->count();

        $ausentes = Senha::query()
            ->where('status', StatusSenha::Ausente)
            ->where('finalizada_em', '>=', $hoje)
            ->count();

        $atendidosHoje = Senha::query()
            ->where('status', StatusSenha::Finalizado)
            ->where('finalizada_em', '>=', $hoje)
            ->count();

        $totalHoje = Senha::query()->where('emitida_em', '>=', $hoje)->count();

        $guichesAtivos = Guiche::query()->where('ativo', true)->count();

        $servicoId = OperadorSessao::servicoId();
        $servico = $servicoId ? Servico::query()->find($servicoId) : Servico::query()->where('ativo', true)->orderBy('nome')->first();

        $guicheNumero = OperadorSessao::guicheNumero();

        return [
            'empresa' => $empresa,
            'kpis' => [
                'totalHoje' => $totalHoje,
                'tMedio' => app(TempoMedioServico::class)->mediaGeral(),
                'emEspera' => $emEspera,
                'ausentes' => $ausentes,
                'pico' => '10:00',
                'guichesAtivos' => $guichesAtivos,
            ],
            'atendidosHoje' => $atendidosHoje,
            'operadorNome' => Auth::guard('operador')->user()?->nome ?? 'Operador',
            'guicheNumero' => $guicheNumero,
            'servicoNome' => $servico?->nome ?? '',
        ];
    }
}

<?php

namespace App\Fila\Queries;

use App\Fila\Enums\StatusSenha;
use App\Fila\OperadorSessao;
use App\Fila\Services\TempoMedioServico;
use App\Models\Empresa;
use App\Models\Guiche;
use App\Models\Senha;
use App\Models\Servico;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;

class DashboardKpisQuery
{
    /**
     * @return array{
     *     empresa: Empresa,
     *     kpis: array{totalHoje: int, tMedio: int, emEspera: int, ausentes: int, pico: string, guichesAtivos: int},
     *     charts: array{
     *         porHora: list<array{label: string, value: int}>,
     *         porServico: list<array{label: string, color: string, value: int}>
     *     },
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

        $porHora = $this->atendimentosPorHora($empresa, $hoje);
        $porServico = $this->distribuicaoPorServico($hoje);

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
                'pico' => $this->horarioPico($porHora),
                'guichesAtivos' => $guichesAtivos,
            ],
            'charts' => [
                'porHora' => $porHora,
                'porServico' => $porServico,
            ],
            'atendidosHoje' => $atendidosHoje,
            'operadorNome' => Auth::guard('operador')->user()?->nome ?? 'Operador',
            'guicheNumero' => $guicheNumero,
            'servicoNome' => $servico?->nome ?? '',
        ];
    }

    /** @return list<array{label: string, value: int}> */
    protected function atendimentosPorHora(Empresa $empresa, CarbonInterface $hoje): array
    {
        [$inicio, $fim] = $this->faixaHoraria($empresa);

        $contagens = array_fill_keys(range($inicio, $fim), 0);

        Senha::query()
            ->where('status', StatusSenha::Finalizado)
            ->where('finalizada_em', '>=', $hoje)
            ->whereNotNull('finalizada_em')
            ->get(['finalizada_em'])
            ->each(function (Senha $senha) use (&$contagens, $inicio, $fim): void {
                $hora = (int) $senha->finalizada_em->timezone(config('app.timezone'))->format('G');

                if ($hora >= $inicio && $hora <= $fim) {
                    $contagens[$hora]++;
                }
            });

        return collect($contagens)
            ->map(fn (int $value, int $hour) => [
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).'h',
                'value' => $value,
            ])
            ->values()
            ->all();
    }

    /** @return list<array{label: string, color: string, value: int}> */
    protected function distribuicaoPorServico(CarbonInterface $hoje): array
    {
        return Senha::query()
            ->join('servicos', 'senhas.servico_id', '=', 'servicos.id')
            ->where('senhas.status', StatusSenha::Finalizado)
            ->where('senhas.finalizada_em', '>=', $hoje)
            ->selectRaw('servicos.nome as label, servicos.cor as color, COUNT(*) as value')
            ->groupBy('servicos.id', 'servicos.nome', 'servicos.cor')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'color' => $row->color ?: '#2563eb',
                'value' => (int) $row->value,
            ])
            ->values()
            ->all();
    }

    /** @param list<array{label: string, value: int}> $porHora */
    protected function horarioPico(array $porHora): string
    {
        $max = collect($porHora)->max('value');

        if (! $max) {
            return '--';
        }

        $item = collect($porHora)->firstWhere('value', $max);

        return str_replace('h', ':00', $item['label'] ?? '--');
    }

    /** @return array{0: int, 1: int} */
    protected function faixaHoraria(Empresa $empresa): array
    {
        $inicio = (int) substr((string) $empresa->hora_inicio, 0, 2);
        $fim = (int) substr((string) $empresa->hora_fim, 0, 2);

        if ($fim < $inicio) {
            $fim = $inicio;
        }

        return [$inicio, $fim];
    }
}

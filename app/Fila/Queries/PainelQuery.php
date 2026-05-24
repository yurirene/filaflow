<?php

namespace App\Fila\Queries;

use App\Fila\OperadorSessao;
use App\Fila\Support\FilaEspera;
use App\Models\Ala;
use App\Models\Chamada;
use App\Models\Empresa;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Collection;

class PainelQuery
{
    /**
     * @return array{
     *     empresa: Empresa,
     *     alas: Collection<int, Ala>,
     *     servicos: Collection<int, Servico>,
     *     painelAtual: array{tipo: string, codigo: string, servico: string, local: string},
     *     historico: list<array{codigo: string, servico: string, servicoId: int, alaId: int, tipo: string, local: string, hora: string}>,
     *     filasResumo: array<int, array{tamanho: int, esperaMin: int}>,
     *     videos: list<string>
     * }
     */
    public function execute(?int $alaId = null): array
    {
        $empresa = Empresa::instancia();

        $alas = Ala::query()->where('ativo', true)->orderBy('nome')->get();

        $servicosQuery = Servico::query()
            ->with('ala')
            ->where('ativo', true)
            ->orderBy('nome');

        if ($alaId !== null) {
            $servicosQuery->where('ala_id', $alaId);
        }

        $servicos = $servicosQuery->get();

        $contagens = Senha::query()
            ->aguardando()
            ->whereNull('consultorio_id')
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

        $historicoQuery = Chamada::query()
            ->with(['senha.servico', 'guiche', 'consultorio.medico'])
            ->orderByDesc('chamada_em')
            ->limit(20);

        if ($alaId !== null) {
            $this->aplicarFiltroAla($historicoQuery, $alaId);
        }

        $historico = $historicoQuery
            ->get()
            ->map(fn (Chamada $c) => $this->mapHistorico($c))
            ->values()
            ->all();

        return [
            'empresa' => $empresa,
            'alas' => $alas,
            'servicos' => $servicos,
            'painelAtual' => $this->painelAtual($alaId),
            'historico' => $historico,
            'filasResumo' => $filasResumo,
            'videos' => app(PainelVideosQuery::class)->urls(),
        ];
    }

    /** @return array{tipo: string, codigo: string, servico: string, local: string} */
    public function painelAtual(?int $alaId = null): array
    {
        $default = [
            'tipo' => OperadorSessao::MODO_GUICHE,
            'codigo' => '---',
            'servico' => 'Aguardando...',
            'local' => '--',
        ];

        $query = Chamada::query()
            ->with(['senha.servico', 'guiche', 'consultorio.medico'])
            ->orderByDesc('chamada_em');

        if ($alaId !== null) {
            $this->aplicarFiltroAla($query, $alaId);
        }

        $ultima = $query->first();

        if (! $ultima) {
            return $default;
        }

        return $this->mapPainelFromChamada($ultima);
    }

    protected function aplicarFiltroAla($query, int $alaId): void
    {
        $query->where(function ($q) use ($alaId) {
            $q->where(function ($q2) use ($alaId) {
                $q2->whereNotNull('guiche_id')
                    ->whereHas('guiche', fn ($g) => $g->where('ala_id', $alaId));
            })->orWhere(function ($q2) use ($alaId) {
                $q2->whereNotNull('consultorio_id')
                    ->whereHas('consultorio', fn ($c) => $c->where('ala_id', $alaId));
            });
        });
    }

    /** @return array{tipo: string, codigo: string, servico: string, local: string} */
    protected function mapPainelFromChamada(Chamada $c): array
    {
        if ($c->consultorio_id && $c->consultorio) {
            $local = str_pad((string) $c->consultorio->numero, 2, '0', STR_PAD_LEFT);

            return [
                'tipo' => OperadorSessao::MODO_CONSULTORIO,
                'codigo' => $c->senha->codigo,
                'servico' => $c->senha->servico->nome,
                'local' => $local,
                'paciente' => $c->senha->paciente_nome,
            ];
        }

        return [
            'tipo' => OperadorSessao::MODO_GUICHE,
            'codigo' => $c->senha->codigo,
            'servico' => $c->senha->servico->nome,
            'local' => str_pad((string) ($c->guiche?->numero ?? 0), 2, '0', STR_PAD_LEFT),
        ];
    }

    /** @return array{codigo: string, servico: string, servicoId: int, alaId: int, tipo: string, local: string, hora: string} */
    protected function mapHistorico(Chamada $c): array
    {
        $painel = $this->mapPainelFromChamada($c);

        $alaId = $c->consultorio_id && $c->consultorio
            ? $c->consultorio->ala_id
            : $c->senha->servico->ala_id;

        return [
            'codigo' => $painel['codigo'],
            'servico' => $painel['servico'],
            'servicoId' => $c->senha->servico_id,
            'alaId' => $alaId,
            'tipo' => $painel['tipo'],
            'local' => $painel['local'],
            'hora' => $c->chamada_em->timezone(config('app.timezone'))->format('H:i'),
        ];
    }
}

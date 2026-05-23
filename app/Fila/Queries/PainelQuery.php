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
     *     filasResumo: array<int, array{tamanho: int, esperaMin: int}>
     * }
     */
    public function execute(): array
    {
        $empresa = Empresa::instancia();

        $alas = Ala::query()->where('ativo', true)->orderBy('nome')->get();

        $servicos = Servico::query()
            ->with('ala')
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

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

        $historico = Chamada::query()
            ->with(['senha.servico', 'guiche', 'consultorio'])
            ->orderByDesc('chamada_em')
            ->limit(20)
            ->get()
            ->map(fn (Chamada $c) => $this->mapHistorico($c))
            ->values()
            ->all();

        return [
            'empresa' => $empresa,
            'alas' => $alas,
            'servicos' => $servicos,
            'painelAtual' => $this->painelAtual(),
            'historico' => $historico,
            'filasResumo' => $filasResumo,
        ];
    }

    /** @return array{tipo: string, codigo: string, servico: string, local: string} */
    public function painelAtual(): array
    {
        $default = [
            'tipo' => OperadorSessao::MODO_GUICHE,
            'codigo' => '---',
            'servico' => 'Aguardando...',
            'local' => '--',
        ];

        if ($painel = OperadorSessao::painelAtual()) {
            return $painel;
        }

        $ultima = Chamada::query()
            ->with(['senha.servico', 'guiche', 'consultorio'])
            ->orderByDesc('chamada_em')
            ->first();

        if (! $ultima) {
            return $default;
        }

        return $this->mapPainelFromChamada($ultima);
    }

    /** @return array{tipo: string, codigo: string, servico: string, local: string} */
    protected function mapPainelFromChamada(Chamada $c): array
    {
        if ($c->consultorio_id && $c->consultorio) {
            $local = str_pad((string) $c->consultorio->numero, 2, '0', STR_PAD_LEFT);
            if ($c->consultorio->responsavel) {
                $local .= ' — '.$c->consultorio->responsavel;
            }

            return [
                'tipo' => OperadorSessao::MODO_CONSULTORIO,
                'codigo' => $c->senha->codigo,
                'servico' => $c->senha->servico->nome,
                'local' => $local,
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

        return [
            'codigo' => $painel['codigo'],
            'servico' => $painel['servico'],
            'servicoId' => $c->senha->servico_id,
            'alaId' => $c->senha->servico->ala_id,
            'tipo' => $painel['tipo'],
            'local' => $painel['local'],
            'hora' => $c->chamada_em->timezone(config('app.timezone'))->format('H:i'),
        ];
    }
}

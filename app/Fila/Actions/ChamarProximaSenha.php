<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Events\SenhaChamada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Queries\OperadorPainelQuery;
use App\Fila\Services\SelecionadorProximaSenha;
use App\Models\Chamada;
use App\Models\Guiche;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamarProximaSenha
{
    public function __construct(
        protected SelecionadorProximaSenha $selecionador,
        protected OperadorPainelQuery $painelQuery,
    ) {}

    /**
     * @return array{senha: Senha, chamada: Chamada, servico: Servico, guiche: Guiche}
     */
    public function execute(int $guicheId, ?int $servicoId = null, ?int $operadorId = null): array
    {
        $operadorId ??= Auth::guard('operador')->id();

        $guiche = Guiche::query()->where('id', $guicheId)->where('ativo', true)->first()
            ?? throw FilaException::guicheInvalido();

        if ($servicoId) {
            $servico = Servico::query()->with('ala')->where('id', $servicoId)->where('ativo', true)->first()
                ?? throw FilaException::servicoInativo();

            if ($guiche->ala_id !== $servico->ala_id) {
                throw FilaException::guicheAlaIncompativel();
            }
        }

        return DB::transaction(function () use ($guiche, $servicoId, $operadorId): array {
            $servicoIds = $servicoId
                ? collect([$servicoId])
                : $this->painelQuery->servicosDoGuiche($guiche)->pluck('id');

            if ($servicoIds->isEmpty()) {
                throw FilaException::filaVazia();
            }

            $fila = Senha::query()
                ->aguardando()
                ->whereNull('consultorio_id')
                ->whereIn('servico_id', $servicoIds)
                ->orderBy('ordem_fila')
                ->orderBy('emitida_em')
                ->lockForUpdate()
                ->get();

            $senha = $servicoId
                ? $this->selecionarUmaFila($fila, $servicoId)
                : $this->selecionarMultiplasFilas($fila);

            $senha ??= throw FilaException::filaVazia();

            $servico = Servico::query()->with('ala')->findOrFail($senha->servico_id);
            $agora = now();

            $senha->update([
                'status' => StatusSenha::Chamado,
                'chamada_em' => $agora,
            ]);

            $chamada = Chamada::query()->create([
                'senha_id' => $senha->id,
                'guiche_id' => $guiche->id,
                'operador_id' => $operadorId,
                'chamada_em' => $agora,
            ]);

            $tamanhoFila = Senha::query()
                ->aguardando()
                ->whereNull('consultorio_id')
                ->whereIn('servico_id', $servicoIds)
                ->count();
            $espera = $tamanhoFila * $servico->tempo_medio_minutos;

            SenhaChamada::dispatch(
                codigo: $senha->codigo,
                servico: $servico->nome,
                guiche: $guiche->numero,
                isPreferencial: $senha->is_preferencial,
                ala: $servico->ala?->nome,
                alaId: $servico->ala_id,
            );

            FilaAtualizada::dispatch(
                servicoId: $servico->id,
                tamanhoFila: $tamanhoFila,
                esperaEstimada: $espera,
            );

            return [
                'senha' => $senha->fresh(['servico']),
                'chamada' => $chamada,
                'servico' => $servico,
                'guiche' => $guiche,
            ];
        });
    }

    /** @param Collection<int, Senha> $fila */
    protected function selecionarUmaFila(Collection $fila, int $servicoId): ?Senha
    {
        $subset = $fila->where('servico_id', $servicoId)->values();

        $regra = RegraIntercalacao::query()
            ->where('servico_id', $servicoId)
            ->lockForUpdate()
            ->first();

        $senha = $this->selecionador->selecionar($subset, $regra);

        if ($senha && $regra) {
            $regra->increment('ciclo_atual');
        }

        return $senha;
    }

    /** @param Collection<int, Senha> $fila */
    protected function selecionarMultiplasFilas(Collection $fila): ?Senha
    {
        if ($fila->isEmpty()) {
            return null;
        }

        $melhor = null;

        foreach ($fila->groupBy('servico_id') as $sid => $grupo) {
            $regra = RegraIntercalacao::query()
                ->where('servico_id', $sid)
                ->lockForUpdate()
                ->first();

            $candidata = $this->selecionador->selecionar($grupo->values(), $regra);

            if (! $candidata) {
                continue;
            }

            if ($regra) {
                $regra->increment('ciclo_atual');
            }

            if ($melhor === null
                || $candidata->ordem_fila < $melhor->ordem_fila
                || ($candidata->ordem_fila === $melhor->ordem_fila && $candidata->emitida_em < $melhor->emitida_em)) {
                $melhor = $candidata;
            }
        }

        return $melhor;
    }
}

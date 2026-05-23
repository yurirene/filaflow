<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Events\SenhaChamada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Services\SelecionadorProximaSenha;
use App\Models\Chamada;
use App\Models\Consultorio;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamarProximaConsultorio
{
    public function __construct(
        protected SelecionadorProximaSenha $selecionador,
    ) {}

    /**
     * @return array{senha: Senha, chamada: Chamada, servico: Servico, consultorio: Consultorio}
     */
    public function execute(int $consultorioId, ?int $servicoId = null, ?int $operadorId = null): array
    {
        $operadorId ??= Auth::guard('operador')->id();

        $consultorio = Consultorio::query()->with('ala')->where('id', $consultorioId)->where('ativo', true)->first()
            ?? throw FilaException::consultorioInvalido();

        if ($servicoId) {
            $servico = Servico::query()->with('ala')->where('id', $servicoId)->where('ativo', true)->first()
                ?? throw FilaException::servicoInativo();

            if ($servico->ala_id !== $consultorio->ala_id) {
                throw FilaException::consultorioAlaIncompativel();
            }

            if (! $consultorio->aceitaServico($servico)) {
                throw FilaException::servicoNaoPermitidoNoConsultorio();
            }
        }

        return DB::transaction(function () use ($consultorio, $servicoId, $operadorId): array {
            $query = Senha::query()
                ->aguardando()
                ->where('consultorio_id', $consultorio->id)
                ->orderBy('ordem_fila')
                ->orderBy('emitida_em');

            if ($servicoId) {
                $query->where('servico_id', $servicoId);
            }

            $fila = $query->lockForUpdate()->get();

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
                'consultorio_id' => $consultorio->id,
                'operador_id' => $operadorId,
                'chamada_em' => $agora,
            ]);

            $tamanhoFila = Senha::query()
                ->aguardando()
                ->where('consultorio_id', $consultorio->id)
                ->count();

            SenhaChamada::dispatch(
                codigo: $senha->codigo,
                servico: $servico->nome,
                guiche: null,
                isPreferencial: $senha->is_preferencial,
                ala: $servico->ala?->nome,
                consultorio: $consultorio->numero,
                responsavel: $consultorio->responsavel,
            );

            FilaAtualizada::dispatch(
                servicoId: $servico->id,
                tamanhoFila: $tamanhoFila,
                esperaEstimada: $tamanhoFila * $servico->tempo_medio_minutos,
            );

            return [
                'senha' => $senha->fresh(['servico', 'consultorio']),
                'chamada' => $chamada,
                'servico' => $servico,
                'consultorio' => $consultorio,
            ];
        });
    }

    /** @param \Illuminate\Support\Collection<int, Senha> $fila */
    protected function selecionarUmaFila($fila, int $servicoId): ?Senha
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

    /** @param \Illuminate\Support\Collection<int, Senha> $fila */
    protected function selecionarMultiplasFilas($fila): ?Senha
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

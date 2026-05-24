<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Events\SenhaChamada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Services\SelecionadorProximaSenha;
use App\Models\Chamada;
use App\Models\Guiche;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamarProximaSenha
{
    public function __construct(
        protected SelecionadorProximaSenha $selecionador,
    ) {}

    /**
     * @return array{senha: Senha, chamada: Chamada, servico: Servico, guiche: Guiche}
     */
    public function execute(int $servicoId, int $guicheId, ?int $operadorId = null): array
    {
        $operadorId ??= Auth::guard('operador')->id();

        $servico = Servico::query()->with('ala')->where('id', $servicoId)->where('ativo', true)->first()
            ?? throw FilaException::servicoInativo();

        $guiche = Guiche::query()->where('id', $guicheId)->where('ativo', true)->first()
            ?? throw FilaException::guicheInvalido();

        if ($guiche->ala_id !== $servico->ala_id) {
            throw FilaException::guicheAlaIncompativel();
        }

        return DB::transaction(function () use ($servico, $guiche, $operadorId): array {
            $fila = Senha::query()
                ->aguardando()
                ->where('servico_id', $servico->id)
                ->whereNull('consultorio_id')
                ->orderBy('ordem_fila')
                ->orderBy('emitida_em')
                ->lockForUpdate()
                ->get();

            $regra = RegraIntercalacao::query()
                ->where('servico_id', $servico->id)
                ->lockForUpdate()
                ->first();

            $senha = $this->selecionador->selecionar($fila, $regra)
                ?? throw FilaException::filaVazia();

            $agora = now();

            $senha->update([
                'status' => StatusSenha::Chamado,
                'chamada_em' => $agora,
            ]);

            if ($regra) {
                $regra->increment('ciclo_atual');
            }

            $chamada = Chamada::query()->create([
                'senha_id' => $senha->id,
                'guiche_id' => $guiche->id,
                'operador_id' => $operadorId,
                'chamada_em' => $agora,
            ]);

            $tamanhoFila = Senha::query()
                ->aguardando()
                ->where('servico_id', $servico->id)
                ->whereNull('consultorio_id')
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
}

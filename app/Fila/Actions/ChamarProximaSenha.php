<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Events\SenhaChamada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Services\SelecionadorProximaSenha;
use App\Fila\TenantContext;
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
    public function execute(string $servicoId, string $guicheId, ?int $operadorId = null): array
    {
        $operadorId ??= Auth::id();

        $servico = Servico::query()->where('id', $servicoId)->where('ativo', true)->first()
            ?? throw FilaException::servicoInativo();

        $guiche = Guiche::query()->where('id', $guicheId)->where('ativo', true)->first()
            ?? throw FilaException::guicheInvalido();

        return DB::transaction(function () use ($servico, $guiche, $operadorId): array {
            $fila = Senha::query()
                ->aguardando()
                ->where('servico_id', $servico->id)
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
                'empresa_id' => TenantContext::requireEmpresaId(),
                'senha_id' => $senha->id,
                'guiche_id' => $guiche->id,
                'operador_id' => $operadorId,
                'chamada_em' => $agora,
            ]);

            $tamanhoFila = Senha::query()->aguardando()->where('servico_id', $servico->id)->count();
            $espera = $tamanhoFila * $servico->tempo_medio_minutos;

            SenhaChamada::dispatch(
                empresaId: $servico->empresa_id,
                codigo: $senha->codigo,
                servico: $servico->nome,
                guiche: $guiche->numero,
                isPreferencial: $senha->is_preferencial,
                ala: $servico->ala,
            );

            FilaAtualizada::dispatch(
                empresaId: $servico->empresa_id,
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

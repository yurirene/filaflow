<?php

namespace App\Fila\Actions;

use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Services\GeradorCodigoSenha;
use App\Fila\Services\OrdemFilaService;
use App\Fila\TenantContext;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Facades\DB;

class EmitirSenha
{
    public function __construct(
        protected GeradorCodigoSenha $gerador,
        protected OrdemFilaService $ordemFila,
    ) {}

    /**
     * @return array{id: string, codigo: string, servico_nome: string, prioridade: string, espera_estimada_minutos: int, posicao_fila: int, emitida_em: string}
     */
    public function execute(
        string $servicoId,
        PrioridadeSenha $prioridade,
        ?string $pacienteCelular = null,
        bool $isAgendado = false,
    ): array {
        $servico = Servico::query()->where('id', $servicoId)->where('ativo', true)->first();

        if (! $servico) {
            throw FilaException::servicoInativo();
        }

        return DB::transaction(function () use ($servico, $prioridade, $pacienteCelular, $isAgendado): array {
            $isPreferencial = $prioridade->isPreferencial();
            $ordem = $this->ordemFila->proximaOrdem($servico->id, $isPreferencial, $isAgendado);
            $codigo = $this->gerador->gerar($servico);
            $emitidaEm = now();

            $senha = Senha::query()->create([
                'empresa_id' => TenantContext::requireEmpresaId(),
                'codigo' => $codigo,
                'servico_id' => $servico->id,
                'prioridade' => $prioridade,
                'is_preferencial' => $isPreferencial,
                'is_agendado' => $isAgendado,
                'status' => StatusSenha::Aguardando,
                'paciente_celular' => $pacienteCelular,
                'emitida_em' => $emitidaEm,
                'ordem_fila' => $ordem,
            ]);

            $posicao = Senha::query()
                ->aguardando()
                ->where('servico_id', $servico->id)
                ->where('ordem_fila', '<=', $senha->ordem_fila)
                ->count();

            $espera = $posicao * $servico->tempo_medio_minutos;

            FilaAtualizada::dispatch(
                empresaId: $servico->empresa_id,
                servicoId: $servico->id,
                tamanhoFila: Senha::query()->aguardando()->where('servico_id', $servico->id)->count(),
                esperaEstimada: $espera,
            );

            return [
                'id' => $senha->id,
                'codigo' => $senha->codigo,
                'servico_nome' => $servico->nome,
                'prioridade' => $prioridade->value,
                'espera_estimada_minutos' => max(1, $espera),
                'posicao_fila' => $posicao,
                'emitida_em' => $emitidaEm->toIso8601String(),
            ];
        });
    }
}

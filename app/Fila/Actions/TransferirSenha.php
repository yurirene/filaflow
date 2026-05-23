<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Services\OrdemFilaService;
use App\Models\Senha;
use App\Models\Servico;

class TransferirSenha
{
    public function __construct(
        protected OrdemFilaService $ordemFila,
    ) {}

    public function execute(Senha $senha, int $servicoDestinoId): Senha
    {
        if ($senha->consultorio_id !== null) {
            throw FilaException::senhaNaoPodeTransferir();
        }

        $destino = Servico::query()->where('id', $servicoDestinoId)->where('ativo', true)->first()
            ?? throw FilaException::servicoInativo();

        $ordem = $this->ordemFila->proximaOrdem(
            $destino->id,
            $senha->is_preferencial,
            $senha->is_agendado,
        );

        $senha->update([
            'servico_id' => $destino->id,
            'status' => StatusSenha::Aguardando,
            'chamada_em' => null,
            'ordem_fila' => $ordem,
        ]);

        FilaAtualizada::dispatch(
            servicoId: $destino->id,
            tamanhoFila: Senha::query()
                ->aguardando()
                ->where('servico_id', $destino->id)
                ->whereNull('consultorio_id')
                ->count(),
            esperaEstimada: 0,
        );

        return $senha->fresh();
    }
}

<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Models\Senha;

class FinalizarSenha
{
    public function execute(Senha $senha): void
    {
        $senha->load('servico');
        $agora = now();

        $senha->update([
            'status' => StatusSenha::Finalizado,
            'finalizada_em' => $agora,
        ]);

        FilaAtualizada::dispatch(
            empresaId: $senha->empresa_id,
            servicoId: $senha->servico_id,
            tamanhoFila: Senha::query()->aguardando()->where('servico_id', $senha->servico_id)->count(),
            esperaEstimada: 0,
        );
    }
}

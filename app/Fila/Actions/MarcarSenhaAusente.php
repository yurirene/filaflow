<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Models\Senha;

class MarcarSenhaAusente
{
    public function execute(Senha $senha): void
    {
        $senha->update([
            'status' => StatusSenha::Ausente,
            'finalizada_em' => now(),
        ]);

        FilaAtualizada::dispatch(
            servicoId: $senha->servico_id,
            tamanhoFila: Senha::query()->aguardando()->where('servico_id', $senha->servico_id)->count(),
            esperaEstimada: 0,
        );
    }
}

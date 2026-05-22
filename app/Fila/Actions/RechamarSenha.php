<?php

namespace App\Fila\Actions;

use App\Fila\Events\SenhaChamada;
use App\Fila\Exceptions\FilaException;
use App\Models\Chamada;
use App\Models\Senha;

class RechamarSenha
{
    public function execute(Senha $senha, Chamada $chamada): void
    {
        $senha->load('servico');
        $chamada->load('guiche');

        if (! $chamada->guiche) {
            throw FilaException::guicheInvalido();
        }

        $chamada->increment('rechamada_vezes');

        SenhaChamada::dispatch(
            empresaId: $senha->empresa_id,
            codigo: $senha->codigo,
            servico: $senha->servico->nome,
            guiche: $chamada->guiche->numero,
            isPreferencial: $senha->is_preferencial,
            ala: $senha->servico->ala,
        );
    }
}

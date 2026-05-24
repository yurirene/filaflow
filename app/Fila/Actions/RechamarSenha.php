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
        $senha->load('servico.ala');
        $chamada->load(['guiche', 'consultorio.medico']);

        if ($chamada->consultorio_id) {
            $consultorio = $chamada->consultorio;
            if (! $consultorio) {
                throw FilaException::consultorioInvalido();
            }

            $chamada->increment('rechamada_vezes');

            SenhaChamada::dispatch(
                codigo: $senha->codigo,
                servico: $senha->servico->nome,
                guiche: null,
                isPreferencial: $senha->is_preferencial,
                ala: $senha->servico->ala?->nome,
                consultorio: $consultorio->numero,
                responsavel: $consultorio->medico?->nome,
                alaId: $consultorio->ala_id,
            );

            return;
        }

        if (! $chamada->guiche) {
            throw FilaException::localChamadaInvalido();
        }

        $chamada->increment('rechamada_vezes');

        SenhaChamada::dispatch(
            codigo: $senha->codigo,
            servico: $senha->servico->nome,
            guiche: $chamada->guiche->numero,
            isPreferencial: $senha->is_preferencial,
            ala: $senha->servico->ala?->nome,
            alaId: $senha->servico->ala_id,
        );
    }
}

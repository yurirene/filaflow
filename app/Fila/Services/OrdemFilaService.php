<?php

namespace App\Fila\Services;

use App\Models\Senha;

class OrdemFilaService
{
    /**
     * Calcula ordem de inserção: agendados após preferenciais; preferenciais antes de normais.
     */
    public function proximaOrdem(string $servicoId, bool $isPreferencial, bool $isAgendado): int
    {
        $query = Senha::query()
            ->aguardando()
            ->where('servico_id', $servicoId);

        if ($isAgendado) {
            $ultimaPref = (clone $query)->where('is_preferencial', true)->max('ordem_fila') ?? 0;

            return $ultimaPref + 1;
        }

        if ($isPreferencial) {
            $primeiraNormal = (clone $query)->where('is_preferencial', false)->min('ordem_fila');

            if ($primeiraNormal !== null) {
                Senha::query()
                    ->aguardando()
                    ->where('servico_id', $servicoId)
                    ->where('ordem_fila', '>=', $primeiraNormal)
                    ->increment('ordem_fila');

                return (int) $primeiraNormal;
            }

            return ((int) $query->max('ordem_fila')) + 1;
        }

        return ((int) $query->max('ordem_fila')) + 1;
    }
}

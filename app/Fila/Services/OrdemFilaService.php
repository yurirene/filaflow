<?php

namespace App\Fila\Services;

use App\Models\Senha;

class OrdemFilaService
{
    /**
     * Calcula ordem de inserção: agendados após preferenciais; preferenciais antes de normais.
     */
    public function proximaOrdem(int $servicoId, bool $isPreferencial, bool $isAgendado, ?int $consultorioId = null): int
    {
        $query = Senha::query()
            ->aguardando()
            ->where('servico_id', $servicoId);

        if ($consultorioId) {
            $query->where('consultorio_id', $consultorioId);
        } else {
            $query->whereNull('consultorio_id');
        }

        if ($isAgendado) {
            $ultimaPref = (clone $query)->where('is_preferencial', true)->max('ordem_fila') ?? 0;

            return $ultimaPref + 1;
        }

        if ($isPreferencial) {
            $primeiraNormal = (clone $query)->where('is_preferencial', false)->min('ordem_fila');

            if ($primeiraNormal !== null) {
                $shift = Senha::query()
                    ->aguardando()
                    ->where('servico_id', $servicoId)
                    ->where('ordem_fila', '>=', $primeiraNormal);

                if ($consultorioId) {
                    $shift->where('consultorio_id', $consultorioId);
                } else {
                    $shift->whereNull('consultorio_id');
                }

                $shift->increment('ordem_fila');

                return (int) $primeiraNormal;
            }

            return ((int) $query->max('ordem_fila')) + 1;
        }

        return ((int) $query->max('ordem_fila')) + 1;
    }
}

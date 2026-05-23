<?php

namespace App\Fila\Services;

use App\Fila\Enums\StatusSenha;
use App\Models\Senha;
use Illuminate\Support\Carbon;

class TempoMedioServico
{
    public const PADRAO_MINUTOS = 10;

    /** Dias de histórico considerados no cálculo. */
    public const DIAS_HISTORICO = 30;

    public function paraServico(int $servicoId): int
    {
        $desde = now()->subDays(self::DIAS_HISTORICO);

        $senhas = Senha::query()
            ->where('servico_id', $servicoId)
            ->where('status', StatusSenha::Finalizado)
            ->whereNotNull('chamada_em')
            ->whereNotNull('finalizada_em')
            ->where('finalizada_em', '>=', $desde)
            ->get(['chamada_em', 'finalizada_em']);

        if ($senhas->isEmpty()) {
            return self::PADRAO_MINUTOS;
        }

        $media = $senhas->avg(function (Senha $senha): float {
            $chamada = Carbon::parse($senha->chamada_em);
            $finalizada = Carbon::parse($senha->finalizada_em);

            return max(1, $chamada->diffInMinutes($finalizada));
        });

        return max(1, (int) round($media));
    }

    /** Média ponderada pelos serviços ativos (para KPIs gerais). */
    public function mediaGeral(): int
    {
        $servicoIds = Senha::query()
            ->where('status', StatusSenha::Finalizado)
            ->whereNotNull('chamada_em')
            ->whereNotNull('finalizada_em')
            ->where('finalizada_em', '>=', now()->subDays(self::DIAS_HISTORICO))
            ->distinct()
            ->pluck('servico_id');

        if ($servicoIds->isEmpty()) {
            return self::PADRAO_MINUTOS;
        }

        $medias = $servicoIds->map(fn (int $id) => $this->paraServico($id));

        return max(1, (int) round($medias->avg()));
    }
}

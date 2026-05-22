<?php

namespace App\Support;

use App\Fila\FilaSnapshot;
use App\Fila\TenantContext;
use App\Models\Guiche;

class FilaState
{
    public const SESSION_KEY = 'fila_ui';

    public static function get(): array
    {
        if (! TenantContext::empresaId()) {
            return self::empty();
        }

        return app(FilaSnapshot::class)->build();
    }

    public static function set(array $state): void
    {
        $ui = session(self::SESSION_KEY, []);

        if (isset($state['prioridadeSelecionada'])) {
            $ui['prioridade_selecionada'] = $state['prioridadeSelecionada'];
        }
        if (isset($state['queueFilter'])) {
            $ui['queue_filter'] = $state['queueFilter'];
        }
        if (isset($state['painelAla'])) {
            $ui['painel_ala'] = $state['painelAla'];
        }
        if (isset($state['timerSegundos'])) {
            $ui['timer_segundos'] = $state['timerSegundos'];
        }
        if (isset($state['operador'])) {
            if (isset($state['operador']['guiche'])) {
                $guiche = Guiche::query()->where('numero', $state['operador']['guiche'])->first();
                if ($guiche) {
                    $ui['guiche_id'] = $guiche->id;
                }
            }
            if (isset($state['operador']['servico'])) {
                $ui['servico_id'] = $state['operador']['servico'];
            }
        }
        if (isset($state['log'])) {
            $ui['log'] = $state['log'];
        }

        session([self::SESSION_KEY => $ui]);
    }

    public static function reset(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function ensureSeeded(): array
    {
        return self::get();
    }

    public static function default(): array
    {
        return self::empty();
    }

    protected static function empty(): array
    {
        return [
            'empresa_id' => null,
            'clinicName' => '',
            'servicos' => [],
            'guiches' => [],
            'filas' => [],
            'contadores' => [],
            'historico' => [],
            'senhaAtual' => null,
            'timerSegundos' => 0,
            'prioridadeSelecionada' => 'normal',
            'intercalacao' => [],
            'operador' => ['nome' => 'Operador', 'guiche' => 1, 'servico' => ''],
            'stats' => ['atendidos' => 0, 'ausentes' => 0, 'tempos' => []],
            'agendamentos' => [],
            'kpis' => [
                'totalHoje' => 0,
                'tMedio' => 0,
                'emEspera' => 0,
                'ausentes' => 0,
                'pico' => '--',
                'guichesAtivos' => 0,
            ],
            'notificacoes' => [],
            'queueFilter' => 'all',
            'painelAla' => 'all',
            'painelAtual' => ['codigo' => '---', 'servico' => 'Aguardando...', 'guiche' => '--'],
            'log' => [],
            'config' => [],
            'seeded' => false,
        ];
    }

    public static function servico(array $state, string $id): ?array
    {
        foreach ($state['servicos'] as $svc) {
            if ($svc['id'] === $id) {
                return $svc;
            }
        }

        return null;
    }

    public static function calcEspera(array $state, string $servicoId): int
    {
        return FilaSnapshot::calcEspera($state, $servicoId);
    }

    public static function prioridadeLabel(string $prioridade): string
    {
        return match ($prioridade) {
            'idoso' => '👴 Idoso',
            'pcd' => '♿ PCD',
            'gestante' => '🤰 Gestante',
            default => 'Preferencial',
        };
    }

    public static function prioridadeBadge(string $prioridade): string
    {
        return match ($prioridade) {
            'idoso' => '👴 Idoso — Preferencial',
            'pcd' => '♿ PCD — Preferencial',
            'gestante' => '🤰 Gestante — Preferencial',
            default => 'Atendimento Normal',
        };
    }

    public static function totalEmEspera(array $state): int
    {
        return collect($state['filas'])->flatten(1)->count();
    }
}

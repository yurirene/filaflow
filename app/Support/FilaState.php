<?php

namespace App\Support;

class FilaState
{
    public const SESSION_KEY = 'fila_state';

    public static function get(): array
    {
        $state = session(self::SESSION_KEY);

        if ($state === null) {
            $state = self::default();
            self::set($state);
        }

        return $state;
    }

    public static function set(array $state): void
    {
        session([self::SESSION_KEY => $state]);
    }

    public static function reset(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function default(): array
    {
        return [
            'empresa_id' => 'emp_001',
            'clinicName' => 'Clínica São Lucas',
            'servicos' => [
                ['id' => 'triagem', 'nome' => 'Triagem', 'prefixo' => 'T', 'ala' => 'Ala A', 'tMedio' => 8, 'cor' => '#2563eb', 'ativo' => true, 'icon' => '🩺'],
                ['id' => 'coleta', 'nome' => 'Coleta', 'prefixo' => 'C', 'ala' => 'Ala B', 'tMedio' => 12, 'cor' => '#0ea5e9', 'ativo' => true, 'icon' => '🧪'],
                ['id' => 'raio-x', 'nome' => 'Raio-X', 'prefixo' => 'R', 'ala' => 'Ala C', 'tMedio' => 20, 'cor' => '#7c3aed', 'ativo' => true, 'icon' => '🔬'],
                ['id' => 'caixa', 'nome' => 'Caixa', 'prefixo' => 'X', 'ala' => 'Ala D', 'tMedio' => 6, 'cor' => '#16a34a', 'ativo' => true, 'icon' => '💳'],
            ],
            'guiches' => [
                ['id' => 1, 'num' => 1, 'desc' => 'Guichê de Triagem', 'servico' => 'triagem', 'ativo' => true],
                ['id' => 2, 'num' => 2, 'desc' => 'Guichê de Triagem', 'servico' => 'triagem', 'ativo' => true],
                ['id' => 3, 'num' => 3, 'desc' => 'Guichê de Coleta', 'servico' => 'coleta', 'ativo' => true],
                ['id' => 4, 'num' => 4, 'desc' => 'Guichê de Raio-X', 'servico' => 'raio-x', 'ativo' => false],
                ['id' => 5, 'num' => 5, 'desc' => 'Guichê de Caixa', 'servico' => 'caixa', 'ativo' => true],
            ],
            'filas' => ['triagem' => [], 'coleta' => [], 'raio-x' => [], 'caixa' => []],
            'contadores' => ['triagem' => 0, 'coleta' => 0, 'raio-x' => 0, 'caixa' => 0],
            'historico' => [],
            'senhaAtual' => null,
            'timerSegundos' => 0,
            'prioridadeSelecionada' => 'normal',
            'intercalacao' => [
                'triagem' => ['normais' => 2, 'preferenciais' => 1, 'cicloAtual' => 0],
                'coleta' => ['normais' => 2, 'preferenciais' => 1, 'cicloAtual' => 0],
                'raio-x' => ['normais' => 2, 'preferenciais' => 1, 'cicloAtual' => 0],
                'caixa' => ['normais' => 2, 'preferenciais' => 1, 'cicloAtual' => 0],
            ],
            'operador' => ['nome' => 'Ana Tereza', 'guiche' => 3, 'servico' => 'triagem'],
            'stats' => ['atendidos' => 0, 'ausentes' => 0, 'tempos' => []],
            'agendamentos' => [
                ['id' => 'ag1', 'hora' => '08:30', 'nome' => 'Maria Silva', 'servico' => 'triagem', 'status' => 'aguardando'],
                ['id' => 'ag2', 'hora' => '09:00', 'nome' => 'João Pereira', 'servico' => 'coleta', 'status' => 'na-fila'],
                ['id' => 'ag3', 'hora' => '09:30', 'nome' => 'Carla Mendes', 'servico' => 'triagem', 'status' => 'aguardando'],
                ['id' => 'ag4', 'hora' => '10:00', 'nome' => 'Roberto Lima', 'servico' => 'raio-x', 'status' => 'aguardando'],
                ['id' => 'ag5', 'hora' => '10:30', 'nome' => 'Fernanda Costa', 'servico' => 'caixa', 'status' => 'atendido'],
            ],
            'kpis' => [
                'totalHoje' => 47,
                'tMedio' => 9,
                'emEspera' => 12,
                'ausentes' => 3,
                'pico' => '10:00',
                'guichesAtivos' => 4,
            ],
            'notificacoes' => [
                'whatsapp' => ['ativo' => false, 'provider' => 'z-api', 'antecedencia' => 3],
                'sms' => ['ativo' => false, 'provider' => 'twilio', 'antecedencia' => 5],
            ],
            'queueFilter' => 'all',
            'painelAla' => 'all',
            'painelAtual' => ['codigo' => '---', 'servico' => 'Aguardando...', 'guiche' => '--'],
            'log' => [],
            'config' => [
                'clinicName' => 'Clínica São Lucas',
                'cnpj' => '',
                'horaInicio' => '07:00',
                'horaFim' => '19:00',
                'ticker' => 'Bem-vindo! Traga seus documentos e exames anteriores.',
                'reinicioHora' => '00:00',
                'som' => 'beep',
            ],
            'seeded' => false,
        ];
    }

    public static function ensureSeeded(): array
    {
        $state = self::get();

        if ($state['seeded']) {
            return $state;
        }

        $prioridades = ['normal', 'normal', 'normal', 'idoso', 'normal', 'pcd', 'normal', 'gestante'];

        foreach ($state['servicos'] as $svc) {
            $qtd = random_int(2, 7);
            for ($i = 0; $i < $qtd; $i++) {
                $state['contadores'][$svc['id']]++;
                $num = str_pad((string) $state['contadores'][$svc['id']], 3, '0', STR_PAD_LEFT);
                $prio = $prioridades[array_rand($prioridades)];
                $isPref = in_array($prio, ['idoso', 'pcd', 'gestante'], true);
                $senha = [
                    'id' => "{$svc['id']}_demo_{$i}",
                    'codigo' => "{$svc['prefixo']}{$num}",
                    'servicoId' => $svc['id'],
                    'prioridade' => $prio,
                    'isPreferencial' => $isPref,
                    'agendado' => false,
                    'status' => 'aguardando',
                    'emitidaEm' => now()->subMinutes(random_int(1, 30))->toIso8601String(),
                    'posicao' => $i + 1,
                ];
                $state['filas'][$svc['id']][] = $senha;
            }
        }

        $state['historico'] = [
            ['codigo' => 'T012', 'servico' => 'Triagem', 'guiche' => 1, 'hora' => '10:45'],
            ['codigo' => 'C008', 'servico' => 'Coleta', 'guiche' => 3, 'hora' => '10:43'],
            ['codigo' => 'T011', 'servico' => 'Triagem', 'guiche' => 2, 'hora' => '10:40'],
        ];

        $state['kpis']['emEspera'] = collect($state['filas'])->flatten(1)->count();
        $state['seeded'] = true;
        self::set($state);

        return $state;
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
        $svc = self::servico($state, $servicoId);
        $fila = $state['filas'][$servicoId] ?? [];

        return max(1, count($fila) * ($svc['tMedio'] ?? 10));
    }

    public static function proximaSenhaIntercalada(array $state, string $servicoId): ?array
    {
        $fila = $state['filas'][$servicoId] ?? [];
        if (count($fila) === 0) {
            return null;
        }

        $ic = $state['intercalacao'][$servicoId] ?? null;
        if (! $ic) {
            return $fila[0];
        }

        $normais = array_values(array_filter($fila, fn ($s) => ! $s['isPreferencial']));
        $preferenciais = array_values(array_filter($fila, fn ($s) => $s['isPreferencial']));

        if (count($preferenciais) === 0) {
            return $normais[0] ?? null;
        }
        if (count($normais) === 0) {
            return $preferenciais[0] ?? null;
        }

        $cicloTotal = $ic['normais'] + $ic['preferenciais'];
        $posNoCiclo = $ic['cicloAtual'] % $cicloTotal;

        return $posNoCiclo < $ic['normais'] ? $normais[0] : $preferenciais[0];
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

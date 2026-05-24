<?php

namespace App\Fila;

use App\Models\Consultorio;
use App\Models\Guiche;

class OperadorSessao
{
    public const KEY = 'fila_ui';

    public const MODO_GUICHE = 'guiche';

    public const MODO_CONSULTORIO = 'consultorio';

    public static function get(): array
    {
        return session(self::KEY, []);
    }

    public static function merge(array $data): void
    {
        session([self::KEY => array_merge(self::get(), $data)]);
    }

    public static function reset(): void
    {
        session()->forget(self::KEY);
    }

    public static function modo(): string
    {
        return self::get()['modo'] ?? self::MODO_GUICHE;
    }

    public static function setModo(string $modo): void
    {
        self::merge(['modo' => $modo]);
    }

    public static function senhaAtualId(): ?int
    {
        $id = self::get()['senha_atual_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public static function setSenhaAtual(?int $senhaId): void
    {
        self::merge(['senha_atual_id' => $senhaId]);
    }

    public static function guicheId(): ?int
    {
        $id = self::get()['guiche_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public static function guicheNumero(): int
    {
        $id = self::guicheId();
        if ($id) {
            $numero = Guiche::query()->where('id', $id)->value('numero');
            if ($numero !== null) {
                return (int) $numero;
            }
        }

        return 1;
    }

    public static function consultorioId(): ?int
    {
        $id = self::get()['consultorio_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public static function consultorioNumero(): int
    {
        $id = self::consultorioId();
        if ($id) {
            $numero = Consultorio::query()->where('id', $id)->value('numero');
            if ($numero !== null) {
                return (int) $numero;
            }
        }

        return 1;
    }

    public static function servicoId(): ?int
    {
        $id = self::get()['servico_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public static function setOperadorContext(int $guicheId, ?int $servicoId = null): void
    {
        self::merge([
            'modo' => self::MODO_GUICHE,
            'guiche_id' => $guicheId,
            'servico_id' => $servicoId,
        ]);
    }

    public static function setConsultorioContext(int $consultorioId, ?int $servicoId = null): void
    {
        self::merge([
            'modo' => self::MODO_CONSULTORIO,
            'consultorio_id' => $consultorioId,
            'servico_id' => $servicoId,
        ]);
    }

    public static function queueFilter(): string
    {
        return self::get()['queue_filter'] ?? 'all';
    }

    public static function setQueueFilter(string $filter): void
    {
        self::merge(['queue_filter' => $filter]);
    }

    public static function timerSegundos(): int
    {
        return (int) (self::get()['timer_segundos'] ?? 0);
    }

    public static function setTimerSegundos(int $segundos): void
    {
        self::merge(['timer_segundos' => $segundos]);
    }

    public static function painelAla(): string
    {
        $ala = self::get()['painel_ala'] ?? 'all';

        return (string) $ala;
    }

    public static function setPainelAla(string|int $ala): void
    {
        self::merge(['painel_ala' => (string) $ala]);
    }

    /** @return array{tipo: string, codigo: string, servico: string, local: string}|null */
    public static function painelAtual(): ?array
    {
        $painel = self::get()['painel_atual'] ?? null;

        if (! is_array($painel)) {
            return null;
        }

        if (isset($painel['tipo'], $painel['local'])) {
            return $painel;
        }

        if (isset($painel['guiche'])) {
            return [
                'tipo' => self::MODO_GUICHE,
                'codigo' => $painel['codigo'] ?? '---',
                'servico' => $painel['servico'] ?? '',
                'local' => $painel['guiche'],
            ];
        }

        return null;
    }

    public static function setPainelAtual(array $painel): void
    {
        self::merge(['painel_atual' => $painel]);
    }

    public static function prioridadeTotem(): string
    {
        return self::get()['prioridade_selecionada'] ?? 'normal';
    }

    public static function setPrioridadeTotem(string $prioridade): void
    {
        self::merge(['prioridade_selecionada' => $prioridade]);
    }

    /** @return list<array{tipo: string, msg: string, hora: string}> */
    public static function log(): array
    {
        return self::get()['log'] ?? [];
    }

    public static function pushLog(string $tipo, string $msg): void
    {
        $log = self::log();
        array_unshift($log, [
            'tipo' => $tipo,
            'msg' => $msg,
            'hora' => now()->format('H:i:s'),
        ]);
        self::merge(['log' => array_slice($log, 0, 50)]);
    }

    public static function clearLog(): void
    {
        self::merge(['log' => []]);
    }

    /** @return list<int> */
    public static function tempos(): array
    {
        return self::get()['tempos'] ?? [];
    }

    public static function pushTempo(int $segundos): void
    {
        $tempos = self::tempos();
        $tempos[] = $segundos;
        self::merge(['tempos' => array_slice($tempos, -100)]);
    }
}

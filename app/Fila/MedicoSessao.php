<?php

namespace App\Fila;

use App\Models\Consultorio;

class MedicoSessao
{
    public const KEY = 'medico_ui';

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

    public static function senhaAtualId(): ?int
    {
        $id = self::get()['senha_atual_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public static function setSenhaAtual(?int $senhaId): void
    {
        self::merge(['senha_atual_id' => $senhaId]);
    }

    public static function consultorioId(): ?int
    {
        $id = self::get()['consultorio_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public static function setContext(int $consultorioId): void
    {
        self::merge([
            'consultorio_id' => $consultorioId,
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

    /** @return array{tipo: string, codigo: string, servico: string, local: string}|null */
    public static function painelAtual(): ?array
    {
        $painel = self::get()['painel_atual'] ?? null;

        return is_array($painel) ? $painel : null;
    }

    public static function setPainelAtual(array $painel): void
    {
        self::merge(['painel_atual' => $painel]);
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

    public static function labelConsultorio(?Consultorio $consultorio): string
    {
        if (! $consultorio) {
            return '—';
        }

        $label = __('Consultório :num', [
            'num' => str_pad((string) $consultorio->numero, 2, '0', STR_PAD_LEFT),
        ]);

        if ($consultorio->medico) {
            $label .= ' — '.$consultorio->medico->nome;
        }

        return $label;
    }
}

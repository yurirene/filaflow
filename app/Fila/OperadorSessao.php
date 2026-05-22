<?php

namespace App\Fila;

use App\Support\FilaState;

class OperadorSessao
{
    public static function get(): array
    {
        return session(FilaState::SESSION_KEY, []);
    }

    public static function merge(array $data): void
    {
        session([FilaState::SESSION_KEY => array_merge(self::get(), $data)]);
    }

    public static function setSenhaAtual(?string $senhaId): void
    {
        self::merge(['senha_atual_id' => $senhaId]);
    }

    public static function setPainelAtual(array $painel): void
    {
        self::merge(['painel_atual' => $painel]);
    }

    public static function pushLog(string $tipo, string $msg): void
    {
        $ui = self::get();
        $log = $ui['log'] ?? [];
        array_unshift($log, [
            'tipo' => $tipo,
            'msg' => $msg,
            'hora' => now()->format('H:i:s'),
        ]);
        self::merge(['log' => array_slice($log, 0, 50)]);
    }

    public static function pushTempo(int $segundos): void
    {
        $ui = self::get();
        $tempos = $ui['tempos'] ?? [];
        $tempos[] = $segundos;
        self::merge(['tempos' => array_slice($tempos, -100)]);
    }
}

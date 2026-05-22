<?php

namespace App\Fila\Exceptions;

use RuntimeException;

class FilaException extends RuntimeException
{
    public static function servicoInativo(): self
    {
        return new self('Serviço indisponível ou inativo.');
    }

    public static function filaVazia(): self
    {
        return new self('Não há senhas aguardando neste serviço.');
    }

    public static function guicheInvalido(): self
    {
        return new self('Guichê inválido para esta empresa.');
    }

    public static function senhaNaoEncontrada(): self
    {
        return new self('Senha não encontrada.');
    }
}

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

    public static function guicheAlaIncompativel(): self
    {
        return new self('Este guichê não pertence à ala do serviço selecionado.');
    }

    public static function senhaNaoEncontrada(): self
    {
        return new self('Senha não encontrada.');
    }

    public static function consultorioInvalido(): self
    {
        return new self('Consultório inválido ou inativo.');
    }

    public static function alaNaoEhConsultorio(): self
    {
        return new self('A ala selecionada não é um setor de consultórios.');
    }

    public static function consultorioAlaIncompativel(): self
    {
        return new self('Este consultório não pertence à ala do serviço selecionado.');
    }

    public static function servicoNaoPermitidoNoConsultorio(): self
    {
        return new self('Este serviço não é permitido neste consultório.');
    }

    public static function senhaJaEncaminhada(): self
    {
        return new self('Esta senha já foi encaminhada a um consultório.');
    }

    public static function pacienteNomeIncompleto(): self
    {
        return new self('Informe o nome completo do paciente (nome e sobrenome).');
    }

    public static function senhaNaoPodeTransferir(): self
    {
        return new self('Senhas encaminhadas ao consultório não podem ser transferidas no guichê.');
    }

    public static function localChamadaInvalido(): self
    {
        return new self('Chamada sem guichê ou consultório associado.');
    }
}

<?php

namespace App\Fila\Enums;

enum StatusSenha: string
{
    case Aguardando = 'aguardando';
    case Chamado = 'chamado';
    case Atendimento = 'atendimento';
    case Finalizado = 'finalizado';
    case Ausente = 'ausente';

    public function isNaFila(): bool
    {
        return $this === self::Aguardando;
    }
}

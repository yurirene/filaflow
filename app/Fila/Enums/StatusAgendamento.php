<?php

namespace App\Fila\Enums;

enum StatusAgendamento: string
{
    case Agendado = 'agendado';
    case NaFila = 'na_fila';
    case Atendido = 'atendido';
    case Cancelado = 'cancelado';
}

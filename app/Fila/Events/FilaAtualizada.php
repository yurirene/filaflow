<?php

namespace App\Fila\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FilaAtualizada
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $empresaId,
        public string $servicoId,
        public int $tamanhoFila,
        public int $esperaEstimada,
    ) {}
}

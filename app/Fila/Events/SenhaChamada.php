<?php

namespace App\Fila\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SenhaChamada
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $codigo,
        public string $servico,
        public ?int $guiche,
        public bool $isPreferencial,
        public ?string $ala = null,
        public ?int $consultorio = null,
        public ?string $responsavel = null,
    ) {}
}

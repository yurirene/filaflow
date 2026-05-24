<?php

namespace App\Fila\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SenhaChamada implements ShouldBroadcastNow
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
        public ?int $alaId = null,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('fila'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'senha.chamada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'codigo' => $this->codigo,
            'servico' => $this->servico,
            'guiche' => $this->guiche,
            'isPreferencial' => $this->isPreferencial,
            'ala' => $this->ala,
            'consultorio' => $this->consultorio,
            'responsavel' => $this->responsavel,
            'alaId' => $this->alaId,
        ];
    }
}

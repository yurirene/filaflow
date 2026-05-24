<?php

namespace App\Fila\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FilaAtualizada implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $servicoId,
        public int $tamanhoFila,
        public int $esperaEstimada,
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
        return 'fila.atualizada';
    }

    /**
     * @return array<string, int>
     */
    public function broadcastWith(): array
    {
        return [
            'servicoId' => $this->servicoId,
            'tamanhoFila' => $this->tamanhoFila,
            'esperaEstimada' => $this->esperaEstimada,
        ];
    }
}

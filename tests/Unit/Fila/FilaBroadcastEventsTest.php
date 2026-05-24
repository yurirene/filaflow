<?php

namespace Tests\Unit\Fila;

use App\Fila\Events\FilaAtualizada;
use App\Fila\Events\SenhaChamada;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilaBroadcastEventsTest extends TestCase
{
    #[Test]
    public function fila_atualizada_transmite_no_canal_fila(): void
    {
        $event = new FilaAtualizada(servicoId: 1, tamanhoFila: 3, esperaEstimada: 9);

        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
        $this->assertSame('fila.atualizada', $event->broadcastAs());
        $this->assertEquals([new Channel('fila')], $event->broadcastOn());
        $this->assertSame([
            'servicoId' => 1,
            'tamanhoFila' => 3,
            'esperaEstimada' => 9,
        ], $event->broadcastWith());
    }

    #[Test]
    public function senha_chamada_transmite_no_canal_fila(): void
    {
        $event = new SenhaChamada(
            codigo: 'A001',
            servico: 'Clínico',
            guiche: 1,
            isPreferencial: false,
            ala: 'Recepção',
            alaId: 2,
        );

        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
        $this->assertSame('senha.chamada', $event->broadcastAs());
        $this->assertEquals([new Channel('fila')], $event->broadcastOn());
        $this->assertSame('A001', $event->broadcastWith()['codigo']);
        $this->assertSame(2, $event->broadcastWith()['alaId']);
    }
}

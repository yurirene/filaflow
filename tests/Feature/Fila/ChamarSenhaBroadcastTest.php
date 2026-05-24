<?php

namespace Tests\Feature\Fila;

use App\Fila\Actions\ChamarProximaSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Events\SenhaChamada;
use App\Models\Guiche;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChamarSenhaBroadcastTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function chamar_proxima_senha_dispara_eventos_de_fila(): void
    {
        $this->seed(FilaSeeder::class);

        Event::fake([SenhaChamada::class, FilaAtualizada::class]);

        $servico = Servico::query()->where('ativo', true)->first();
        $guiche = Guiche::query()->where('ala_id', $servico->ala_id)->where('ativo', true)->first();

        app(ChamarProximaSenha::class)->execute($guiche->id, $servico->id);

        Event::assertDispatched(SenhaChamada::class);
        Event::assertDispatched(FilaAtualizada::class);
    }
}

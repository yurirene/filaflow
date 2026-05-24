<?php

namespace Tests\Feature\Fila;

use App\Livewire\Fila\Painel;
use App\Models\Ala;
use App\Models\Chamada;
use App\Models\Guiche;
use App\Models\Operador;
use App\Models\Senha;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PainelAlaFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_painel_ignora_chamada_de_outra_ala(): void
    {
        $this->seed(FilaSeeder::class);

        $alaA = Ala::query()->where('nome', 'Ala A — Recepção e Triagem')->first();
        $alaB = Ala::query()->where('nome', 'Ala C — Imagem')->first();
        $servicoA = Servico::query()->where('ala_id', $alaA->id)->first();
        $guicheA = Guiche::query()->where('ala_id', $alaA->id)->first();
        $operador = Operador::query()->first();

        $senhaA = Senha::query()->create([
            'codigo' => 'A200',
            'servico_id' => $servicoA->id,
            'status' => 'chamado',
            'emitida_em' => now(),
        ]);

        Chamada::query()->create([
            'senha_id' => $senhaA->id,
            'guiche_id' => $guicheA->id,
            'operador_id' => $operador->id,
            'chamada_em' => now(),
        ]);

        Livewire::test(Painel::class)
            ->set('ala', (string) $alaA->id)
            ->set('lastCodigo', 'A200')
            ->call('onSenhaChamada', $alaB->id)
            ->assertNotDispatched('painel-alert');
    }

    public function test_painel_reage_a_chamada_da_propria_ala(): void
    {
        $this->seed(FilaSeeder::class);

        $alaA = Ala::query()->where('nome', 'Ala A — Recepção e Triagem')->first();
        $servicoA = Servico::query()->where('ala_id', $alaA->id)->first();
        $guicheA = Guiche::query()->where('ala_id', $alaA->id)->first();
        $operador = Operador::query()->first();

        $senhaA = Senha::query()->create([
            'codigo' => 'A300',
            'servico_id' => $servicoA->id,
            'status' => 'chamado',
            'emitida_em' => now(),
        ]);

        Chamada::query()->create([
            'senha_id' => $senhaA->id,
            'guiche_id' => $guicheA->id,
            'operador_id' => $operador->id,
            'chamada_em' => now(),
        ]);

        Livewire::test(Painel::class)
            ->set('ala', (string) $alaA->id)
            ->set('lastCodigo', 'A100')
            ->call('onSenhaChamada', $alaA->id)
            ->assertDispatched('painel-alert');
    }
}

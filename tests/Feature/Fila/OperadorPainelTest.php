<?php

namespace Tests\Feature\Fila;

use App\Fila\Enums\StatusOperador;
use App\Livewire\Fila\Operador;
use App\Models\Operador as OperadorModel;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperadorPainelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function operador_chama_proxima_senha_e_exibe_na_tela(): void
    {
        $this->seed(FilaSeeder::class);

        $operador = OperadorModel::query()->where('cpf', '52998224725')->first();
        $servico = Servico::query()->where('prefixo', 'T')->first();

        $this->actingAs($operador, 'operador');

        Livewire::test(Operador::class)
            ->set('servico', (string) $servico->id)
            ->set('guiche', 1)
            ->call('chamarProxima')
            ->assertSet('temSenhaAtual', true);
    }

    #[Test]
    public function operador_inativo_nao_autentica(): void
    {
        OperadorModel::factory()->create([
            'status' => StatusOperador::Inativo,
        ]);

        $this->assertGuest('operador');
    }
}

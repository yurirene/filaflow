<?php

namespace Tests\Feature\Fila;

use App\Fila\Enums\StatusOperador;
use App\Livewire\Fila\Operador;
use App\Models\Guiche;
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
        $guiche = Guiche::query()->where('ala_id', $servico->ala_id)->where('numero', 1)->first();

        $this->actingAs($operador, 'operador');

        Livewire::test(Operador::class)
            ->set('guicheId', $guiche->id)
            ->set('servico', (string) $servico->id)
            ->call('chamarProxima')
            ->assertSet('temSenhaAtual', true);
    }

    #[Test]
    public function operador_atualiza_servicos_ao_trocar_guiche(): void
    {
        $this->seed(FilaSeeder::class);

        $operador = OperadorModel::query()->where('cpf', '52998224725')->first();
        $guicheTriagem = Guiche::query()->where('numero', 3)->whereHas('ala', fn ($q) => $q->where('nome', 'Ala A — Recepção e Triagem'))->first();
        $guicheCaixa = Guiche::query()->where('numero', 1)->whereHas('ala', fn ($q) => $q->where('nome', 'Ala D — Administrativo'))->first();

        $this->actingAs($operador, 'operador');

        Livewire::test(Operador::class)
            ->set('guicheId', $guicheTriagem->id)
            ->assertSet('servico', (string) $guicheTriagem->servico_padrao_id)
            ->set('guicheId', $guicheCaixa->id)
            ->assertSet('servico', (string) $guicheCaixa->servico_padrao_id)
            ->assertSee('Caixa');
    }

    #[Test]
    public function operador_migra_snapshot_legado_de_guiche(): void
    {
        $this->seed(FilaSeeder::class);

        $operador = OperadorModel::query()->where('cpf', '52998224725')->first();
        $guiche = Guiche::query()->where('numero', 3)->whereHas('ala', fn ($q) => $q->where('nome', 'Ala A — Recepção e Triagem'))->first();

        $this->actingAs($operador, 'operador');

        Livewire::test(Operador::class)
            ->set('guicheId', null)
            ->set('guiche', $guiche->numero)
            ->assertSet('guicheId', $guiche->id);
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

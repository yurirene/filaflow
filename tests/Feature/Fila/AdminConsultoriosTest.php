<?php

namespace Tests\Feature\Fila;

use App\Livewire\Fila\Admin\Consultorios;
use App\Models\Ala;
use App\Models\Medico;
use App\Models\User;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminConsultoriosTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_pode_cadastrar_consultorio(): void
    {
        $this->seed(FilaSeeder::class);
        $ala = Ala::query()->where('is_consultorio', true)->first();
        $medico = Medico::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Consultorios::class)
            ->set('alaId', $ala->id)
            ->set('numero', 9)
            ->set('medicoId', $medico->id)
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('consultorios', [
            'ala_id' => $ala->id,
            'numero' => 9,
            'medico_id' => $medico->id,
        ]);
    }

    #[Test]
    public function rota_admin_consultorios_requer_autenticacao(): void
    {
        $this->get(route('admin.consultorios'))->assertRedirect(route('login'));
    }
}

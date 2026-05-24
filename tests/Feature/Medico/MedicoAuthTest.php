<?php

namespace Tests\Feature\Medico;

use App\Fila\Enums\StatusOperador;
use App\Livewire\Medico\Auth\Login;
use App\Models\Ala;
use App\Models\Consultorio;
use App\Models\Medico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MedicoAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function medico_com_consultorio_pode_fazer_login(): void
    {
        $ala = Ala::query()->create(['nome' => 'Ala Consultório', 'ativo' => true, 'is_consultorio' => true]);
        $medico = Medico::factory()->create([
            'cpf' => '11144477735',
            'password' => 'senha123',
            'status' => StatusOperador::Ativo,
        ]);
        Consultorio::query()->create([
            'ala_id' => $ala->id,
            'medico_id' => $medico->id,
            'numero' => 1,
            'ativo' => true,
        ]);

        Livewire::test(Login::class)
            ->set('cpf', '111.444.777-35')
            ->set('password', 'senha123')
            ->call('login')
            ->assertRedirect(route('medico.painel'));

        $this->assertAuthenticatedAs($medico, 'medico');
    }

    #[Test]
    public function medico_sem_consultorio_nao_pode_fazer_login(): void
    {
        Medico::factory()->create([
            'cpf' => '11144477735',
            'password' => 'senha123',
            'status' => StatusOperador::Ativo,
        ]);

        Livewire::test(Login::class)
            ->set('cpf', '11144477735')
            ->set('password', 'senha123')
            ->call('login')
            ->assertHasErrors(['cpf']);

        $this->assertGuest('medico');
    }

    #[Test]
    public function painel_medico_exige_autenticacao(): void
    {
        $this->get(route('medico.painel'))
            ->assertRedirect(route('medico.login'));
    }
}

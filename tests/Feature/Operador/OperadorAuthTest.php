<?php

namespace Tests\Feature\Operador;

use App\Fila\Enums\StatusOperador;
use App\Livewire\Operador\Auth\Login;
use App\Models\Operador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperadorAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function operador_ativo_pode_fazer_login(): void
    {
        $operador = Operador::factory()->create([
            'cpf' => '52998224725',
            'password' => 'senha123',
            'status' => StatusOperador::Ativo,
        ]);

        Livewire::test(Login::class)
            ->set('cpf', '529.982.247-25')
            ->set('password', 'senha123')
            ->call('login')
            ->assertRedirect(route('operador.painel'));

        $this->assertAuthenticatedAs($operador, 'operador');
    }

    #[Test]
    public function operador_inativo_nao_pode_fazer_login(): void
    {
        Operador::factory()->create([
            'cpf' => '52998224725',
            'password' => 'senha123',
            'status' => StatusOperador::Inativo,
        ]);

        Livewire::test(Login::class)
            ->set('cpf', '52998224725')
            ->set('password', 'senha123')
            ->call('login')
            ->assertHasErrors(['cpf']);

        $this->assertGuest('operador');
    }

    #[Test]
    public function painel_operador_exige_autenticacao(): void
    {
        $this->get(route('operador.painel'))
            ->assertRedirect(route('operador.login'));
    }
}

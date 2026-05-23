<?php

namespace Tests\Feature\Fila;

use App\Livewire\Fila\Admin\Consultorios;
use App\Livewire\Fila\Admin\Guiches;
use App\Livewire\Fila\Admin\Servicos;
use App\Models\Ala;
use App\Models\Consultorio;
use App\Models\Guiche;
use App\Models\Servico;
use App\Models\User;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_pode_editar_servico(): void
    {
        $this->seed(FilaSeeder::class);
        $servico = Servico::query()->first();
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Servicos::class)
            ->call('openEditModal', $servico->id)
            ->set('svcNome', 'Triagem Atualizada')
            ->call('salvarServico')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('servicos', [
            'id' => $servico->id,
            'nome' => 'Triagem Atualizada',
        ]);
    }

    #[Test]
    public function admin_pode_editar_e_excluir_guiche_sem_chamadas(): void
    {
        $this->seed(FilaSeeder::class);
        $guiche = Guiche::query()->first();
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Guiches::class)
            ->call('openEditModal', $guiche->id)
            ->set('guicheDesc', 'Recepção principal')
            ->call('salvarGuiche')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('guiches', [
            'id' => $guiche->id,
            'descricao' => 'Recepção principal',
        ]);

        Livewire::test(Guiches::class)
            ->call('excluir', $guiche->id);

        $this->assertDatabaseMissing('guiches', ['id' => $guiche->id]);
    }

    #[Test]
    public function admin_pode_editar_consultorio(): void
    {
        $this->seed(FilaSeeder::class);
        $consultorio = Consultorio::query()->first();
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Consultorios::class)
            ->call('openEditModal', $consultorio->id)
            ->set('responsavel', 'Dr. Atualizado')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('consultorios', [
            'id' => $consultorio->id,
            'responsavel' => 'Dr. Atualizado',
        ]);
    }

    #[Test]
    public function excluir_servico_anula_servico_id_das_senhas(): void
    {
        $ala = Ala::query()->create(['nome' => 'Ala X', 'ativo' => true]);
        $servico = Servico::query()->create([
            'ala_id' => $ala->id,
            'nome' => 'Teste',
            'prefixo' => 'Z',
            'ativo' => true,
        ]);

        $senha = \App\Models\Senha::query()->create([
            'codigo' => 'Z001',
            'servico_id' => $servico->id,
            'is_preferencial' => false,
            'is_agendado' => false,
            'status' => \App\Fila\Enums\StatusSenha::Aguardando,
            'emitida_em' => now(),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Servicos::class)->call('excluir', $servico->id);

        $this->assertDatabaseMissing('servicos', ['id' => $servico->id]);
        $this->assertDatabaseHas('senhas', [
            'id' => $senha->id,
            'servico_id' => null,
        ]);
    }
}

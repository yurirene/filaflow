<?php

namespace Tests\Feature\Fila;

use App\Fila\Actions\ChamarProximaSenha;
use App\Fila\Actions\EncaminharParaConsultorio;
use App\Fila\Enums\StatusSenha;
use App\Fila\OperadorSessao;
use App\Livewire\Fila\Operador;
use App\Models\Consultorio;
use App\Models\Guiche;
use App\Models\Operador as OperadorModel;
use App\Models\Senha;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperadorEncaminharTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function operador_guiche_encaminha_e_consultorio_ve_senha(): void
    {
        $this->seed(FilaSeeder::class);

        $operador = OperadorModel::query()->where('cpf', '52998224725')->first();
        $servico = Servico::query()->where('prefixo', 'T')->first();
        $guiche = Guiche::query()->where('ala_id', $servico->ala_id)->first();
        $consultorio = Consultorio::query()
            ->whereHas('servicos', fn ($q) => $q->whereKey($servico->id))
            ->first();

        $this->actingAs($operador, 'operador');

        $resultado = app(ChamarProximaSenha::class)->execute($guiche->id, $servico->id, $operador->id);
        $senha = $resultado['senha'];

        app(EncaminharParaConsultorio::class)->execute($senha, $servico->id, $consultorio->id, 'Ana Costa Lima');

        $component = Livewire::test(Operador::class)
            ->call('alternarModo', 'consultorio')
            ->set('consultorio', (string) $consultorio->id)
            ->set('servico', '');

        $this->assertCount(1, $component->instance()->filaAguardando);
    }

    #[Test]
    public function operador_consultorio_chama_proxima(): void
    {
        $this->seed(FilaSeeder::class);

        $operador = OperadorModel::query()->where('cpf', '52998224725')->first();
        $servico = Servico::query()->where('prefixo', 'T')->first();
        $consultorio = Consultorio::query()
            ->whereHas('servicos', fn ($q) => $q->whereKey($servico->id))
            ->first();

        $senha = Senha::query()->create([
            'codigo' => 'T999',
            'servico_id' => $servico->id,
            'consultorio_id' => $consultorio->id,
            'status' => StatusSenha::Aguardando,
            'emitida_em' => now(),
            'ordem_fila' => 1,
        ]);

        $this->actingAs($operador, 'operador');

        Livewire::test(Operador::class)
            ->call('alternarModo', 'consultorio')
            ->set('consultorio', (string) $consultorio->id)
            ->set('servico', (string) $servico->id)
            ->call('chamarProxima')
            ->assertSet('temSenhaAtual', true);

        $senha->refresh();
        $this->assertSame(StatusSenha::Chamado, $senha->status);
        $painel = OperadorSessao::painelAtual();
        $this->assertSame('consultorio', $painel['tipo']);
    }
}

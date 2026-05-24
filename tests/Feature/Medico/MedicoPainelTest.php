<?php

namespace Tests\Feature\Medico;

use App\Fila\Enums\StatusSenha;
use App\Livewire\Fila\Medico as MedicoPainel;
use App\Models\Consultorio;
use App\Models\Medico;
use App\Models\Senha;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MedicoPainelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function medico_ve_apenas_fila_do_seu_consultorio(): void
    {
        $this->seed(FilaSeeder::class);

        $medico = Medico::query()->where('cpf', '11144477735')->first();
        $consultorio = $medico->consultorio;
        $servico = Servico::query()->where('prefixo', 'T')->first();

        $outroConsultorio = Consultorio::query()
            ->where('id', '!=', $consultorio->id)
            ->first();

        Senha::query()->create([
            'codigo' => 'T100',
            'servico_id' => $servico->id,
            'consultorio_id' => $consultorio->id,
            'status' => StatusSenha::Aguardando,
            'emitida_em' => now(),
            'ordem_fila' => 1,
            'paciente_nome' => 'Maria Silva Santos',
        ]);

        Senha::query()->create([
            'codigo' => 'T101',
            'servico_id' => $servico->id,
            'consultorio_id' => $outroConsultorio->id,
            'status' => StatusSenha::Aguardando,
            'emitida_em' => now(),
            'ordem_fila' => 1,
        ]);

        $this->actingAs($medico, 'medico');

        $component = Livewire::test(MedicoPainel::class);

        $this->assertCount(1, $component->instance()->filaAguardando);
        $this->assertSame('Maria Silva Santos', $component->instance()->filaAguardando->first()->paciente_nome);
    }
}

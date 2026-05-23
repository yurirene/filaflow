<?php

namespace Tests\Unit\Fila;

use App\Fila\Actions\ChamarProximaSenha;
use App\Fila\Actions\EncaminharParaConsultorio;
use App\Fila\Enums\StatusSenha;
use App\Fila\Exceptions\FilaException;
use App\Models\Ala;
use App\Models\Consultorio;
use App\Models\Guiche;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EncaminharParaConsultorioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function encaminhar_move_senha_para_fila_do_consultorio(): void
    {
        $ala = Ala::query()->create(['nome' => 'Ala Teste', 'ativo' => true]);
        $servico = Servico::query()->create([
            'ala_id' => $ala->id,
            'nome' => 'Consulta',
            'prefixo' => 'Q',
            'ativo' => true,
        ]);
        $outro = Servico::query()->create([
            'ala_id' => $ala->id,
            'nome' => 'Exames',
            'prefixo' => 'E',
            'ativo' => true,
        ]);
        $consultorio = Consultorio::query()->create([
            'ala_id' => $ala->id,
            'numero' => 1,
            'responsavel' => 'Dr. Teste',
            'ativo' => true,
        ]);
        Guiche::query()->create([
            'ala_id' => $ala->id,
            'numero' => 1,
            'descricao' => 'Guichê 1',
            'ativo' => true,
        ]);

        $senha = Senha::query()->create([
            'codigo' => 'Q001',
            'servico_id' => $servico->id,
            'is_preferencial' => false,
            'is_agendado' => false,
            'status' => StatusSenha::Chamado,
            'emitida_em' => now(),
            'ordem_fila' => 1,
        ]);

        app(EncaminharParaConsultorio::class)->execute($senha, $outro->id, $consultorio->id);

        $senha->refresh();
        $this->assertSame($consultorio->id, $senha->consultorio_id);
        $this->assertSame($outro->id, $senha->servico_id);
        $this->assertSame(StatusSenha::Aguardando, $senha->status);

        $this->assertSame(0, Senha::query()->filaGuiche($servico->id)->count());
        $this->assertSame(1, Senha::query()->filaConsultorio($consultorio->id)->count());
    }

    #[Test]
    public function rejeita_servico_de_outra_ala(): void
    {
        $alaA = Ala::query()->create(['nome' => 'A', 'ativo' => true]);
        $alaB = Ala::query()->create(['nome' => 'B', 'ativo' => true]);
        $servicoA = Servico::query()->create(['ala_id' => $alaA->id, 'nome' => 'S A', 'prefixo' => 'A', 'ativo' => true]);
        $servicoB = Servico::query()->create(['ala_id' => $alaB->id, 'nome' => 'S B', 'prefixo' => 'B', 'ativo' => true]);
        $consultorio = Consultorio::query()->create([
            'ala_id' => $alaA->id,
            'numero' => 1,
            'responsavel' => 'Dr.',
            'ativo' => true,
        ]);
        $senha = Senha::query()->create([
            'codigo' => 'A001',
            'servico_id' => $servicoA->id,
            'is_preferencial' => false,
            'is_agendado' => false,
            'status' => StatusSenha::Chamado,
            'emitida_em' => now(),
        ]);

        $this->expectException(FilaException::class);
        app(EncaminharParaConsultorio::class)->execute($senha, $servicoB->id, $consultorio->id);
    }

    #[Test]
    public function chamar_guiche_ignora_senhas_encaminhadas(): void
    {
        $this->seed(\Database\Seeders\FilaSeeder::class);

        $servico = Servico::query()->where('prefixo', 'T')->first();
        $guiche = Guiche::query()->where('ala_id', $servico->ala_id)->first();
        $consultorio = Consultorio::query()->where('ala_id', $servico->ala_id)->first();

        $encaminhada = Senha::query()->filaGuiche($servico->id)->first();
        app(EncaminharParaConsultorio::class)->execute($encaminhada, $servico->id, $consultorio->id);

        $antes = Senha::query()->filaGuiche($servico->id)->count();
        $resultado = app(ChamarProximaSenha::class)->execute($servico->id, $guiche->id, 1);

        $this->assertNotSame($encaminhada->id, $resultado['senha']->id);
        $this->assertSame($antes - 1, Senha::query()->filaGuiche($servico->id)->count());
    }
}

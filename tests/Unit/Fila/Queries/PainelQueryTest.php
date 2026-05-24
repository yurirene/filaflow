<?php

namespace Tests\Unit\Fila\Queries;

use App\Fila\OperadorSessao;
use App\Fila\Queries\PainelQuery;
use App\Models\Ala;
use App\Models\Chamada;
use App\Models\Guiche;
use App\Models\Operador;
use App\Models\Senha;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_painel_atual_ignora_sessao_e_usa_ultima_chamada(): void
    {
        $this->seed(FilaSeeder::class);

        OperadorSessao::setPainelAtual([
            'codigo' => 'T001',
            'servico' => 'Triagem',
            'guiche' => '03',
        ]);

        $senha = Senha::query()->first();
        $guiche = Guiche::query()->first();
        $operador = Operador::query()->first();

        Chamada::query()->create([
            'senha_id' => $senha->id,
            'guiche_id' => $guiche->id,
            'operador_id' => $operador->id,
            'chamada_em' => now(),
        ]);

        $result = app(PainelQuery::class)->execute();

        $this->assertSame($senha->codigo, $result['painelAtual']['codigo']);
        $this->assertIsArray($result['historico']);
    }

    public function test_painel_atual_fallback_ultima_chamada(): void
    {
        $this->seed(FilaSeeder::class);

        $senha = Senha::query()->first();
        $guiche = Guiche::query()->first();
        $operador = Operador::query()->first();

        Chamada::query()->create([
            'senha_id' => $senha->id,
            'guiche_id' => $guiche->id,
            'operador_id' => $operador->id,
            'chamada_em' => now(),
        ]);

        $painel = app(PainelQuery::class)->painelAtual();

        $this->assertSame($senha->codigo, $painel['codigo']);
    }

    public function test_filtra_chamadas_por_ala(): void
    {
        $this->seed(FilaSeeder::class);

        $alaA = Ala::query()->where('nome', 'Ala A — Recepção e Triagem')->first();
        $alaC = Ala::query()->where('nome', 'Ala C — Imagem')->first();
        $servicoA = Servico::query()->where('ala_id', $alaA->id)->first();
        $servicoC = Servico::query()->where('ala_id', $alaC->id)->first();
        $guicheA = Guiche::query()->where('ala_id', $alaA->id)->first();
        $guicheC = Guiche::query()->where('ala_id', $alaC->id)->first();
        $operador = Operador::query()->first();

        $senhaA = Senha::query()->create([
            'codigo' => 'A100',
            'servico_id' => $servicoA->id,
            'status' => 'chamado',
            'emitida_em' => now(),
        ]);

        $senhaC = Senha::query()->create([
            'codigo' => 'C100',
            'servico_id' => $servicoC->id,
            'status' => 'chamado',
            'emitida_em' => now(),
        ]);

        Chamada::query()->create([
            'senha_id' => $senhaC->id,
            'guiche_id' => $guicheC->id,
            'operador_id' => $operador->id,
            'chamada_em' => now()->subMinute(),
        ]);

        Chamada::query()->create([
            'senha_id' => $senhaA->id,
            'guiche_id' => $guicheA->id,
            'operador_id' => $operador->id,
            'chamada_em' => now(),
        ]);

        $resultado = app(PainelQuery::class)->execute($alaA->id);

        $this->assertSame('A100', $resultado['painelAtual']['codigo']);
        $this->assertCount(1, $resultado['historico']);
        $this->assertSame('A100', $resultado['historico'][0]['codigo']);
        $this->assertTrue($resultado['servicos']->every(fn (Servico $s) => $s->ala_id === $alaA->id));
    }
}

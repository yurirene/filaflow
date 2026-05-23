<?php

namespace Tests\Unit\Fila\Queries;

use App\Fila\OperadorSessao;
use App\Fila\Queries\PainelQuery;
use App\Models\Chamada;
use App\Models\Guiche;
use App\Models\Operador;
use App\Models\Senha;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_historico_e_painel_atual_da_sessao(): void
    {
        $this->seed(FilaSeeder::class);

        OperadorSessao::setPainelAtual([
            'codigo' => 'T001',
            'servico' => 'Triagem',
            'guiche' => '03',
        ]);

        $result = app(PainelQuery::class)->execute();

        $this->assertSame('T001', $result['painelAtual']['codigo']);
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
}

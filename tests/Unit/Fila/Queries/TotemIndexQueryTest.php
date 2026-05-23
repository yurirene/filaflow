<?php

namespace Tests\Unit\Fila\Queries;

use App\Fila\Queries\TotemIndexQuery;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotemIndexQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retorna_servicos_e_resumo_da_fila(): void
    {
        $this->seed(FilaSeeder::class);

        $result = app(TotemIndexQuery::class)->execute();

        $this->assertNotEmpty($result['servicos']);
        $this->assertArrayHasKey('empresa', $result);
        $servico = $result['servicos']->first();
        $this->assertArrayHasKey($servico->id, $result['filasResumo']);
        $this->assertGreaterThanOrEqual(0, $result['filasResumo'][$servico->id]['tamanho']);
        $this->assertGreaterThanOrEqual(1, $result['filasResumo'][$servico->id]['esperaMin']);
    }
}

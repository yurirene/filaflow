<?php

namespace Tests\Unit\Fila\Queries;

use App\Fila\Enums\StatusSenha;
use App\Fila\Queries\DashboardKpisQuery;
use App\Models\Senha;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardKpisQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retorna_dados_dos_graficos(): void
    {
        $this->seed(FilaSeeder::class);

        $servico = Servico::query()->where('prefixo', 'T')->first();

        Senha::query()->create([
            'codigo' => 'T900',
            'servico_id' => $servico->id,
            'status' => StatusSenha::Finalizado,
            'emitida_em' => now()->subHour(),
            'chamada_em' => now()->subMinutes(50),
            'finalizada_em' => now()->setTime(10, 15),
        ]);

        $resultado = app(DashboardKpisQuery::class)->execute();

        $this->assertArrayHasKey('charts', $resultado);
        $this->assertNotEmpty($resultado['charts']['porHora']);
        $this->assertSame(1, collect($resultado['charts']['porServico'])->firstWhere('label', $servico->nome)['value'] ?? 0);
        $this->assertSame('10:00', $resultado['kpis']['pico']);
    }
}

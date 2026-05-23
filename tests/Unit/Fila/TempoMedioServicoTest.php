<?php

namespace Tests\Unit\Fila;

use App\Fila\Enums\StatusSenha;
use App\Fila\Services\TempoMedioServico;
use App\Models\Senha;
use App\Models\Servico;
use Database\Seeders\FilaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempoMedioServicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_retorna_padrao_sem_historico(): void
    {
        $this->seed(FilaSeeder::class);

        $servico = Servico::query()->where('prefixo', 'R')->first();

        Senha::query()->where('servico_id', $servico->id)->delete();

        $this->assertSame(TempoMedioServico::PADRAO_MINUTOS, app(TempoMedioServico::class)->paraServico($servico->id));
    }

    public function test_calcula_media_dos_atendimentos_finalizados(): void
    {
        $this->seed(FilaSeeder::class);

        $servico = Servico::query()->where('prefixo', 'T')->first();

        Senha::query()->where('servico_id', $servico->id)->delete();

        $agora = now();

        Senha::query()->create([
            'codigo' => 'T901',
            'servico_id' => $servico->id,
            'prioridade' => 'normal',
            'status' => StatusSenha::Finalizado,
            'emitida_em' => $agora->copy()->subMinutes(20),
            'chamada_em' => $agora->copy()->subMinutes(15),
            'finalizada_em' => $agora->copy()->subMinutes(5),
        ]);

        Senha::query()->create([
            'codigo' => 'T902',
            'servico_id' => $servico->id,
            'prioridade' => 'normal',
            'status' => StatusSenha::Finalizado,
            'emitida_em' => $agora->copy()->subMinutes(30),
            'chamada_em' => $agora->copy()->subMinutes(25),
            'finalizada_em' => $agora->copy()->subMinutes(5),
        ]);

        $this->assertSame(15, app(TempoMedioServico::class)->paraServico($servico->id));
        $this->assertSame(15, $servico->fresh()->tempo_medio_minutos);
    }
}

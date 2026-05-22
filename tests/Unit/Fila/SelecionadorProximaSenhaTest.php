<?php

namespace Tests\Unit\Fila;

use App\Fila\Services\SelecionadorProximaSenha;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SelecionadorProximaSenhaTest extends TestCase
{
    private SelecionadorProximaSenha $selecionador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->selecionador = new SelecionadorProximaSenha;
    }

    #[Test]
    public function retorna_primeira_senha_sem_regra(): void
    {
        $fila = $this->filaFake(['N1', 'P1']);

        $selecionada = $this->selecionador->selecionar($fila, null);

        $this->assertSame('N1', $selecionada->codigo);
    }

    #[Test]
    public function intercala_2_normais_1_preferencial_no_ciclo(): void
    {
        $regra = new RegraIntercalacao([
            'normais_por_ciclo' => 2,
            'preferenciais_por_ciclo' => 1,
            'ciclo_atual' => 0,
        ]);

        $fila = $this->filaFake(['N1', 'P1', 'N2']);

        $s1 = $this->selecionador->selecionar($fila, $regra);
        $this->assertSame('N1', $s1->codigo);
        $fila = $fila->reject(fn (Senha $s) => $s->codigo === $s1->codigo)->values();
        $regra->ciclo_atual++;

        $s2 = $this->selecionador->selecionar($fila, $regra);
        $this->assertSame('N2', $s2->codigo);
        $fila = $fila->reject(fn (Senha $s) => $s->codigo === $s2->codigo)->values();
        $regra->ciclo_atual++;

        $s3 = $this->selecionador->selecionar($fila, $regra);
        $this->assertSame('P1', $s3->codigo);
    }

    #[Test]
    public function usa_preferencial_quando_ciclo_pedir_normal_mas_nao_houver(): void
    {
        $regra = new RegraIntercalacao([
            'normais_por_ciclo' => 2,
            'preferenciais_por_ciclo' => 1,
            'ciclo_atual' => 0,
        ]);

        $fila = $this->filaFake(['P1', 'P2']);

        $this->assertSame('P1', $this->selecionador->selecionar($fila, $regra)->codigo);
    }

    #[Test]
    public function usa_normal_quando_ciclo_pedir_preferencial_mas_nao_houver(): void
    {
        $regra = new RegraIntercalacao([
            'normais_por_ciclo' => 2,
            'preferenciais_por_ciclo' => 1,
            'ciclo_atual' => 2,
        ]);

        $fila = $this->filaFake(['N1', 'N2']);

        $this->assertSame('N1', $this->selecionador->selecionar($fila, $regra)->codigo);
    }

    /**
     * @param  list<string>  $codigos  N=normal P=preferencial
     * @return Collection<int, Senha>
     */
    private function filaFake(array $codigos): Collection
    {
        return collect($codigos)->map(function (string $codigo): Senha {
            $isPref = str_starts_with($codigo, 'P');

            return new Senha([
                'codigo' => $codigo,
                'is_preferencial' => $isPref,
                'emitida_em' => now(),
            ]);
        });
    }
}

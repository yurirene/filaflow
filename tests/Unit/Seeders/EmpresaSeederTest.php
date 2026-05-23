<?php

namespace Tests\Unit\Seeders;

use App\Models\Empresa;
use Database\Seeders\EmpresaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_empresa_a_partir_do_env(): void
    {
        config([
            'app.name' => 'Clínica Teste',
        ]);

        putenv('FILA_CLINICA_NOME=Clínica do Env');
        putenv('FILA_CLINICA_CNPJ=12.345.678/0001-90');
        putenv('FILA_CLINICA_TICKER=Mensagem do ticker');

        $this->seed(EmpresaSeeder::class);

        $empresa = Empresa::instancia();

        $this->assertSame('Clínica do Env', $empresa->nome);
        $this->assertSame('12.345.678/0001-90', $empresa->cnpj);
        $this->assertSame('Mensagem do ticker', $empresa->ticker);

        putenv('FILA_CLINICA_NOME');
        putenv('FILA_CLINICA_CNPJ');
        putenv('FILA_CLINICA_TICKER');
    }

    public function test_atualiza_empresa_existente(): void
    {
        Empresa::query()->create([
            'nome' => 'Antiga',
            'ticker' => 'Antigo',
        ]);

        putenv('FILA_CLINICA_NOME=Atualizada');

        $this->seed(EmpresaSeeder::class);

        $this->assertSame('Atualizada', Empresa::instancia()->nome);
        $this->assertSame(1, Empresa::query()->count());

        putenv('FILA_CLINICA_NOME');
    }
}

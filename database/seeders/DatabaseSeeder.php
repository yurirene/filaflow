<?php

namespace Database\Seeders;

use App\Models\Operador;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EmpresaSeeder::class,
            UserSeeder::class,
            AlaSeeder::class,
        ]);

        Operador::query()->firstOrCreate(
            ['cpf' => env('FILA_OPERADOR_CPF', '90696573253')],
            [
                'nome' => env('FILA_OPERADOR_NOME', 'Yuri Ferreira'),
                'password' => env('FILA_OPERADOR_SENHA', '123'),
                'status' => 'ativo',
            ],
        );

        if (filter_var(env('FILA_SEED_DEMO', true), FILTER_VALIDATE_BOOL)) {
            $this->call(FilaSeeder::class);

            return;
        }

        // $this->call([
        //     GuicheSeeder::class,
        //     ConsultorioSeeder::class,
        // ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = self::attributesFromEnv();

        $empresa = Empresa::query()->first();

        if ($empresa) {
            $empresa->update($attributes);

            return;
        }

        Empresa::query()->create($attributes);
    }

    /** @return array<string, mixed> */
    public static function attributesFromEnv(): array
    {
        $nome = env('FILA_CLINICA_NOME')
            ?: env('APP_NAME', 'FilaFlow');

        $cnpj = env('FILA_CLINICA_CNPJ');
        $cnpj = is_string($cnpj) && $cnpj !== '' ? $cnpj : null;

        return [
            'nome' => $nome,
            'cnpj' => $cnpj,
            'ativo' => filter_var(env('FILA_CLINICA_ATIVO', true), FILTER_VALIDATE_BOOL),
            'hora_inicio' => env('FILA_CLINICA_HORA_INICIO', '07:00'),
            'hora_fim' => env('FILA_CLINICA_HORA_FIM', '19:00'),
            'ticker' => env(
                'FILA_CLINICA_TICKER',
                'Bem-vindo! Traga seus documentos e exames anteriores.',
            ),
            'reinicio_hora' => env('FILA_CLINICA_REINICIO_HORA', '00:00'),
            'som' => 'beep',
        ];
    }
}

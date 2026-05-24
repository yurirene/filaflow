<?php

namespace Database\Seeders;

use App\Fila\Enums\StatusOperador;
use App\Models\Ala;
use App\Models\Consultorio;
use App\Models\Medico;
use App\Models\Servico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConsultorioSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->dados() as $alaNome => $consultorios) {
            $ala = Ala::query()
                ->where('nome', $alaNome)
                ->where('is_consultorio', true)
                ->first();

            if (! $ala) {
                continue;
            }

            foreach ($consultorios as $dado) {
                $medico = Medico::query()->firstOrNew(['cpf' => $dado['medico_cpf']]);
                $medico->fill([
                    'nome' => $dado['medico_nome'],
                    'status' => StatusOperador::Ativo,
                ]);
                if (! $medico->exists || ! Hash::isHashed((string) $medico->getAuthPassword())) {
                    $medico->password = 'senha123';
                }
                $medico->save();

                $consultorio = Consultorio::query()->updateOrCreate(
                    [
                        'ala_id' => $ala->id,
                        'numero' => $dado['numero'],
                    ],
                    [
                        'medico_id' => $medico->id,
                        'ativo' => $dado['ativo'],
                    ],
                );

                $prefixos = $dado['servicos_prefixos'] ?? [];

                if ($prefixos === []) {
                    $consultorio->servicos()->detach();

                    continue;
                }

                $servicoIds = Servico::query()
                    ->whereIn('prefixo', $prefixos)
                    ->pluck('id');

                $consultorio->servicos()->sync($servicoIds);
            }
        }
    }

    /**
     * @return array<string, list<array{numero: int, medico_nome: string, medico_cpf: string, ativo: bool, servicos_prefixos?: list<string>}>>
     */
    protected function dados(): array
    {
        return [
            'Ala B — Consultas Médicas' => [
                ['numero' => 1, 'medico_nome' => 'Dr. João Silva', 'medico_cpf' => '11144477735', 'ativo' => true, 'servicos_prefixos' => ['T']],
                ['numero' => 2, 'medico_nome' => 'Dra. Maria Costa', 'medico_cpf' => '28625587803', 'ativo' => true, 'servicos_prefixos' => ['T']],
                ['numero' => 3, 'medico_nome' => 'Dr. Paulo Mendes', 'medico_cpf' => '15350946056', 'ativo' => true, 'servicos_prefixos' => ['T', 'C']],
            ],
            'Ala C — Imagem' => [
                ['numero' => 1, 'medico_nome' => 'Dr. Ricardo Alves', 'medico_cpf' => '23100299900', 'ativo' => true, 'servicos_prefixos' => ['R']],
                ['numero' => 2, 'medico_nome' => 'Dra. Fernanda Rios', 'medico_cpf' => '87748248800', 'ativo' => true, 'servicos_prefixos' => ['R']],
            ],
        ];
    }
}

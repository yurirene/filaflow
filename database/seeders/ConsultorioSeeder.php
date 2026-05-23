<?php

namespace Database\Seeders;

use App\Models\Ala;
use App\Models\Consultorio;
use App\Models\Servico;
use Illuminate\Database\Seeder;

class ConsultorioSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->dados() as $alaNome => $consultorios) {
            $ala = Ala::query()->where('nome', $alaNome)->first();

            if (! $ala) {
                continue;
            }

            foreach ($consultorios as $dado) {
                $consultorio = Consultorio::query()->updateOrCreate(
                    [
                        'ala_id' => $ala->id,
                        'numero' => $dado['numero'],
                    ],
                    [
                        'responsavel' => $dado['responsavel'],
                        'ativo' => $dado['ativo'],
                    ],
                );

                $prefixos = $dado['servicos_prefixos'] ?? [];

                if ($prefixos === []) {
                    $consultorio->servicos()->detach();

                    continue;
                }

                $servicoIds = Servico::query()
                    ->where('ala_id', $ala->id)
                    ->whereIn('prefixo', $prefixos)
                    ->pluck('id');

                $consultorio->servicos()->sync($servicoIds);
            }
        }
    }

    /**
     * @return array<string, list<array{numero: int, responsavel: string, ativo: bool, servicos_prefixos?: list<string>}>>
     */
    protected function dados(): array
    {
        return [
            'Ala A — Recepção e Triagem' => [
                ['numero' => 1, 'responsavel' => 'Dr. João Silva', 'ativo' => true, 'servicos_prefixos' => ['T']],
                ['numero' => 2, 'responsavel' => 'Dra. Maria Costa', 'ativo' => true, 'servicos_prefixos' => ['T']],
                ['numero' => 3, 'responsavel' => 'Dr. Paulo Mendes', 'ativo' => true],
            ],
            'Ala B — Laboratório' => [
                ['numero' => 1, 'responsavel' => 'Dr. Pedro Lima', 'ativo' => true, 'servicos_prefixos' => ['C']],
                ['numero' => 2, 'responsavel' => 'Dra. Ana Souza', 'ativo' => true, 'servicos_prefixos' => ['C']],
            ],
            'Ala C — Imagem' => [
                ['numero' => 1, 'responsavel' => 'Dr. Ricardo Alves', 'ativo' => true, 'servicos_prefixos' => ['R']],
                ['numero' => 2, 'responsavel' => 'Dra. Fernanda Rios', 'ativo' => true, 'servicos_prefixos' => ['R']],
            ],
            'Ala D — Administrativo' => [
                ['numero' => 1, 'responsavel' => 'Dra. Carla Mendes', 'ativo' => true, 'servicos_prefixos' => ['X']],
                ['numero' => 2, 'responsavel' => 'Dr. Felipe Rocha', 'ativo' => true, 'servicos_prefixos' => ['X']],
            ],
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Ala;
use App\Models\Guiche;
use App\Models\Servico;
use Illuminate\Database\Seeder;

class GuicheSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->dados() as $alaNome => $guiches) {
            $ala = Ala::query()->where('nome', $alaNome)->first();

            if (! $ala) {
                continue;
            }

            foreach ($guiches as $dado) {
                $servicoPadraoId = null;

                if (! empty($dado['servico_prefixo'])) {
                    $servicoPadraoId = Servico::query()
                        ->where('ala_id', $ala->id)
                        ->where('prefixo', $dado['servico_prefixo'])
                        ->value('id');
                }

                Guiche::query()->updateOrCreate(
                    [
                        'ala_id' => $ala->id,
                        'numero' => $dado['numero'],
                    ],
                    [
                        'descricao' => $dado['descricao'],
                        'servico_padrao_id' => $servicoPadraoId,
                        'ativo' => $dado['ativo'],
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, list<array{numero: int, descricao: string, servico_prefixo?: string, ativo: bool}>>
     */
    protected function dados(): array
    {
        return [
            'Ala A — Recepção e Triagem' => [
                ['numero' => 1, 'descricao' => 'Recepção principal', 'servico_prefixo' => 'T', 'ativo' => true],
                ['numero' => 2, 'descricao' => 'Triagem geral', 'servico_prefixo' => 'T', 'ativo' => true],
                ['numero' => 3, 'descricao' => 'Atendimento preferencial', 'servico_prefixo' => 'T', 'ativo' => true],
            ],
            'Ala B — Laboratório' => [
                ['numero' => 1, 'descricao' => 'Coleta de sangue', 'servico_prefixo' => 'C', 'ativo' => true],
                ['numero' => 2, 'descricao' => 'Entrega de materiais', 'servico_prefixo' => 'C', 'ativo' => true],
            ],
            'Ala C — Imagem' => [
                ['numero' => 1, 'descricao' => 'Raio-X', 'servico_prefixo' => 'R', 'ativo' => false],
                ['numero' => 2, 'descricao' => 'Ultrassom', 'servico_prefixo' => 'R', 'ativo' => true],
            ],
            'Ala D — Administrativo' => [
                ['numero' => 1, 'descricao' => 'Caixa 1', 'servico_prefixo' => 'X', 'ativo' => true],
                ['numero' => 2, 'descricao' => 'Caixa 2', 'servico_prefixo' => 'X', 'ativo' => true],
                ['numero' => 3, 'descricao' => 'Informações', 'servico_prefixo' => 'X', 'ativo' => true],
            ],
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Enums\StatusAgendamento;
use App\Fila\Enums\StatusOperador;
use App\Fila\Enums\StatusSenha;
use App\Models\Agendamento;
use App\Models\Ala;
use App\Models\Operador;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class FilaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EmpresaSeeder::class);
    }

    /** @param  Collection<string, Ala>  $alas */
    protected function seedServicos(Collection $alas): Collection
    {
        $mapaAlas = [
            'T' => 'Ala A — Recepção e Triagem',
            'C' => 'Ala B — Laboratório',
            'R' => 'Ala C — Imagem',
            'X' => 'Ala D — Administrativo',
        ];

        $servicosData = [
            ['nome' => 'Triagem', 'prefixo' => 'T', 'cor' => '#2563eb', 'icone' => '🩺'],
            ['nome' => 'Coleta', 'prefixo' => 'C', 'cor' => '#0ea5e9', 'icone' => '🧪'],
            ['nome' => 'Raio-X', 'prefixo' => 'R', 'cor' => '#7c3aed', 'icone' => '🔬'],
            ['nome' => 'Caixa', 'prefixo' => 'X', 'cor' => '#16a34a', 'icone' => '💳'],
        ];

        $servicos = collect();

        foreach ($servicosData as $data) {
            $alaNome = $mapaAlas[$data['prefixo']];
            $ala = $alas[$alaNome] ?? Ala::query()->where('nome', $alaNome)->first();

            $servico = Servico::query()->updateOrCreate(
                ['prefixo' => $data['prefixo']],
                [
                    'nome' => $data['nome'],
                    'ala_id' => $ala->id,
                    'cor' => $data['cor'],
                    'icone' => $data['icone'],
                    'ativo' => $data['nome'] !== 'Raio-X',
                ],
            );

            RegraIntercalacao::query()->updateOrCreate(
                ['servico_id' => $servico->id],
                [
                    'normais_por_ciclo' => 2,
                    'preferenciais_por_ciclo' => 1,
                ],
            );

            $servicos->push($servico);
        }

        return $servicos;
    }

    protected function seedOperadoresDemo(): void
    {
        Operador::query()->updateOrCreate(
            ['cpf' => '52998224725'],
            [
                'nome' => 'Ana Tereza',
                'password' => 'senha123',
                'status' => StatusOperador::Ativo,
            ],
        );

        Operador::query()->updateOrCreate(
            ['cpf' => '39053344705'],
            [
                'nome' => 'Carlos Mendes',
                'password' => 'senha123',
                'status' => StatusOperador::Ativo,
            ],
        );
    }

    /** @param  Collection<int, Servico>  $servicos */
    protected function seedSenhas(Collection $servicos): void
    {
        $prioridades = [
            PrioridadeSenha::Normal,
            PrioridadeSenha::Normal,
            PrioridadeSenha::Idoso,
            PrioridadeSenha::Normal,
            PrioridadeSenha::Pcd,
            PrioridadeSenha::Gestante,
        ];

        foreach ($servicos->where('ativo', true) as $servico) {
            $ordem = 0;
            foreach (range(1, random_int(2, 5)) as $i) {
                $prio = $prioridades[array_rand($prioridades)];
                $codigo = $servico->prefixo.str_pad((string) $i, 3, '0', STR_PAD_LEFT);

                Senha::query()->updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'servico_id' => $servico->id,
                        'prioridade' => $prio,
                        'is_preferencial' => $prio->isPreferencial(),
                        'is_agendado' => false,
                        'status' => StatusSenha::Aguardando,
                        'emitida_em' => now()->subMinutes(random_int(1, 30)),
                        'ordem_fila' => ++$ordem,
                    ],
                );
            }
        }
    }

    /** @param  Collection<int, Servico>  $servicos */
    protected function seedAgendamentos(Collection $servicos): void
    {
        $triagem = $servicos->firstWhere('prefixo', 'T');
        $coleta = $servicos->firstWhere('prefixo', 'C');

        if ($triagem) {
            Agendamento::query()->updateOrCreate(
                [
                    'paciente_nome' => 'Maria Silva',
                    'servico_id' => $triagem->id,
                    'data_hora' => now()->setTime(8, 30),
                ],
                ['status' => StatusAgendamento::Agendado],
            );
        }

        if ($coleta) {
            Agendamento::query()->updateOrCreate(
                [
                    'paciente_nome' => 'João Pereira',
                    'servico_id' => $coleta->id,
                    'data_hora' => now()->setTime(9, 0),
                ],
                ['status' => StatusAgendamento::NaFila],
            );
        }
    }

    protected function callWithReturn(string $seederClass): Collection
    {
        $seeder = $this->container->make($seederClass);
        $seeder->setContainer($this->container)->setCommand($this->command);

        return $seeder->run();
    }
}

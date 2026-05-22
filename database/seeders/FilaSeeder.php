<?php

namespace Database\Seeders;

use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Enums\StatusAgendamento;
use App\Fila\Enums\StatusSenha;
use App\Models\Agendamento;
use App\Models\Empresa;
use App\Models\Guiche;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Database\Seeder;

class FilaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->create([
            'nome' => 'Clínica São Lucas',
            'cnpj' => null,
            'ticker' => 'Bem-vindo! Traga seus documentos e exames anteriores.',
            'notificacoes' => [
                'whatsapp' => ['ativo' => false, 'provider' => 'z-api', 'antecedencia' => 3],
                'sms' => ['ativo' => false, 'provider' => 'twilio', 'antecedencia' => 5],
            ],
        ]);

        $servicosData = [
            ['nome' => 'Triagem', 'prefixo' => 'T', 'ala' => 'Ala A', 'tempo_medio_minutos' => 8, 'cor' => '#2563eb', 'icone' => '🩺'],
            ['nome' => 'Coleta', 'prefixo' => 'C', 'ala' => 'Ala B', 'tempo_medio_minutos' => 12, 'cor' => '#0ea5e9', 'icone' => '🧪'],
            ['nome' => 'Raio-X', 'prefixo' => 'R', 'ala' => 'Ala C', 'tempo_medio_minutos' => 20, 'cor' => '#7c3aed', 'icone' => '🔬'],
            ['nome' => 'Caixa', 'prefixo' => 'X', 'ala' => 'Ala D', 'tempo_medio_minutos' => 6, 'cor' => '#16a34a', 'icone' => '💳'],
        ];

        $servicos = collect();
        foreach ($servicosData as $data) {
            $servico = Servico::query()->create(array_merge($data, [
                'empresa_id' => $empresa->id,
                'ativo' => $data['nome'] !== 'Raio-X',
            ]));
            $servicos->push($servico);

            RegraIntercalacao::query()->create([
                'empresa_id' => $empresa->id,
                'servico_id' => $servico->id,
                'normais_por_ciclo' => 2,
                'preferenciais_por_ciclo' => 1,
            ]);
        }

        $triagem = $servicos->firstWhere('prefixo', 'T');
        $coleta = $servicos->firstWhere('prefixo', 'C');
        $raioX = $servicos->firstWhere('prefixo', 'R');
        $caixa = $servicos->firstWhere('prefixo', 'X');

        $guiches = [
            [1, 'Guichê de Triagem', $triagem->id, true],
            [2, 'Guichê de Triagem', $triagem->id, true],
            [3, 'Guichê de Coleta', $coleta->id, true],
            [4, 'Guichê de Raio-X', $raioX->id, false],
            [5, 'Guichê de Caixa', $caixa->id, true],
        ];

        foreach ($guiches as [$num, $desc, $servicoId, $ativo]) {
            Guiche::query()->create([
                'empresa_id' => $empresa->id,
                'numero' => $num,
                'descricao' => $desc,
                'servico_padrao_id' => $servicoId,
                'ativo' => $ativo,
            ]);
        }

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
                Senha::query()->create([
                    'empresa_id' => $empresa->id,
                    'codigo' => $servico->prefixo.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'servico_id' => $servico->id,
                    'prioridade' => $prio,
                    'is_preferencial' => $prio->isPreferencial(),
                    'is_agendado' => false,
                    'status' => StatusSenha::Aguardando,
                    'emitida_em' => now()->subMinutes(random_int(1, 30)),
                    'ordem_fila' => ++$ordem,
                ]);
            }
        }

        Agendamento::query()->create([
            'empresa_id' => $empresa->id,
            'paciente_nome' => 'Maria Silva',
            'servico_id' => $triagem->id,
            'data_hora' => now()->setTime(8, 30),
            'status' => StatusAgendamento::Agendado,
        ]);

        Agendamento::query()->create([
            'empresa_id' => $empresa->id,
            'paciente_nome' => 'João Pereira',
            'servico_id' => $coleta->id,
            'data_hora' => now()->setTime(9, 0),
            'status' => StatusAgendamento::NaFila,
        ]);

        User::query()->where('email', 'test@example.com')->update(['empresa_id' => $empresa->id]);
    }
}

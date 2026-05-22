<?php

namespace App\Fila;

use App\Fila\Enums\StatusSenha;
use App\Models\Agendamento;
use App\Models\Chamada;
use App\Models\Empresa;
use App\Models\Guiche;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use App\Support\FilaState;
use Illuminate\Support\Facades\Auth;

class FilaSnapshot
{
    public function build(): array
    {
        $empresaId = TenantContext::requireEmpresaId();
        $empresa = Empresa::query()->findOrFail($empresaId);
        $ui = session(FilaState::SESSION_KEY, []);

        $servicos = Servico::query()->orderBy('nome')->get();
        $servicoIds = $servicos->pluck('id');

        $senhasAguardando = Senha::query()
            ->aguardando()
            ->whereIn('servico_id', $servicoIds)
            ->orderBy('ordem_fila')
            ->orderBy('emitida_em')
            ->get()
            ->groupBy('servico_id');

        $filas = [];
        foreach ($servicos as $svc) {
            $filas[$svc->id] = ($senhasAguardando[$svc->id] ?? collect())
                ->values()
                ->map(fn (Senha $s, int $idx) => $this->senhaToArray($s, $idx + 1))
                ->all();
        }

        $intercalacao = RegraIntercalacao::query()
            ->whereIn('servico_id', $servicoIds)
            ->get()
            ->keyBy('servico_id');

        $guiches = Guiche::query()->orderBy('numero')->get();

        $senhaAtual = null;
        if (! empty($ui['senha_atual_id'])) {
            $s = Senha::query()->withoutGlobalScope('empresa')
                ->where('empresa_id', $empresaId)
                ->find($ui['senha_atual_id']);
            if ($s && in_array($s->status, [StatusSenha::Chamado, StatusSenha::Atendimento], true)) {
                $senhaAtual = $this->senhaToArray($s, 0);
                $senhaAtual['chamadaEm'] = $s->chamada_em?->toIso8601String();
            }
        }

        $painelAtual = $ui['painel_atual'] ?? ['codigo' => '---', 'servico' => 'Aguardando...', 'guiche' => '--'];

        $historico = Chamada::query()
            ->with(['senha.servico', 'guiche'])
            ->orderByDesc('chamada_em')
            ->limit(20)
            ->get()
            ->map(fn (Chamada $c) => [
                'codigo' => $c->senha->codigo,
                'servico' => $c->senha->servico->nome,
                'guiche' => $c->guiche->numero,
                'hora' => $c->chamada_em->timezone(config('app.timezone'))->format('H:i'),
            ])
            ->all();

        $hoje = now()->startOfDay();
        $senhasHoje = Senha::query()->where('emitida_em', '>=', $hoje);

        $agendamentos = Agendamento::query()
            ->with('servico')
            ->whereDate('data_hora', now()->toDateString())
            ->orderBy('data_hora')
            ->get()
            ->map(fn (Agendamento $a) => [
                'id' => $a->id,
                'hora' => $a->data_hora->format('H:i'),
                'nome' => $a->paciente_nome,
                'servico' => $a->servico_id,
                'status' => match ($a->status->value) {
                    'na_fila' => 'na-fila',
                    'atendido' => 'atendido',
                    default => 'aguardando',
                },
            ])
            ->all();

        $guicheOperador = $ui['guiche_id'] ?? $guiches->firstWhere('numero', 3)?->id ?? $guiches->first()?->id;
        $servicoOperador = $ui['servico_id'] ?? $servicos->first()?->id;

        $atendidos = Senha::query()->where('status', StatusSenha::Finalizado)
            ->where('finalizada_em', '>=', $hoje)->count();
        $ausentes = Senha::query()->where('status', StatusSenha::Ausente)
            ->where('finalizada_em', '>=', $hoje)->count();

        return [
            'empresa_id' => $empresaId,
            'clinicName' => $empresa->nome,
            'servicos' => $servicos->map(fn (Servico $s) => [
                'id' => $s->id,
                'nome' => $s->nome,
                'prefixo' => $s->prefixo,
                'ala' => $s->ala,
                'tMedio' => $s->tempo_medio_minutos,
                'cor' => $s->cor,
                'ativo' => $s->ativo,
                'icon' => $s->icone,
            ])->all(),
            'guiches' => $guiches->map(fn (Guiche $g) => [
                'id' => $g->id,
                'num' => $g->numero,
                'desc' => $g->descricao,
                'servico' => $g->servico_padrao_id,
                'ativo' => $g->ativo,
            ])->all(),
            'filas' => $filas,
            'contadores' => [],
            'historico' => $historico,
            'senhaAtual' => $senhaAtual,
            'timerSegundos' => (int) ($ui['timer_segundos'] ?? 0),
            'prioridadeSelecionada' => $ui['prioridade_selecionada'] ?? 'normal',
            'intercalacao' => $servicos->mapWithKeys(fn (Servico $s) => [
                $s->id => [
                    'normais' => $intercalacao[$s->id]->normais_por_ciclo ?? 2,
                    'preferenciais' => $intercalacao[$s->id]->preferenciais_por_ciclo ?? 1,
                    'cicloAtual' => $intercalacao[$s->id]->ciclo_atual ?? 0,
                ],
            ])->all(),
            'operador' => [
                'nome' => Auth::user()?->name ?? 'Operador',
                'guiche' => $guiches->firstWhere('id', $guicheOperador)?->numero ?? 1,
                'guiche_id' => $guicheOperador,
                'servico' => $servicoOperador,
            ],
            'stats' => [
                'atendidos' => $atendidos,
                'ausentes' => $ausentes,
                'tempos' => $ui['tempos'] ?? [],
            ],
            'agendamentos' => $agendamentos,
            'kpis' => [
                'totalHoje' => (clone $senhasHoje)->count(),
                'tMedio' => (int) round($servicos->avg('tempo_medio_minutos') ?: 10),
                'emEspera' => collect($filas)->flatten(1)->count(),
                'ausentes' => $ausentes,
                'pico' => '10:00',
                'guichesAtivos' => $guiches->where('ativo', true)->count(),
            ],
            'notificacoes' => $empresa->notificacoes ?? [
                'whatsapp' => ['ativo' => false, 'provider' => 'z-api', 'antecedencia' => 3],
                'sms' => ['ativo' => false, 'provider' => 'twilio', 'antecedencia' => 5],
            ],
            'queueFilter' => $ui['queue_filter'] ?? 'all',
            'painelAla' => $ui['painel_ala'] ?? 'all',
            'painelAtual' => $painelAtual,
            'log' => $ui['log'] ?? [],
            'config' => [
                'clinicName' => $empresa->nome,
                'cnpj' => $empresa->cnpj ?? '',
                'horaInicio' => $empresa->hora_inicio,
                'horaFim' => $empresa->hora_fim,
                'ticker' => $empresa->ticker ?? '',
                'reinicioHora' => $empresa->reinicio_hora,
                'som' => $empresa->som,
            ],
            'seeded' => true,
        ];
    }

    protected function senhaToArray(Senha $s, int $posicao): array
    {
        return [
            'id' => $s->id,
            'codigo' => $s->codigo,
            'servicoId' => $s->servico_id,
            'prioridade' => $s->prioridade->value,
            'isPreferencial' => $s->is_preferencial,
            'agendado' => $s->is_agendado,
            'status' => $s->status->value,
            'emitidaEm' => $s->emitida_em->toIso8601String(),
            'posicao' => $posicao,
        ];
    }

    public static function calcEspera(array $state, string $servicoId): int
    {
        $svc = FilaState::servico($state, $servicoId);
        $fila = $state['filas'][$servicoId] ?? [];

        return max(1, count($fila) * ($svc['tMedio'] ?? 10));
    }
}

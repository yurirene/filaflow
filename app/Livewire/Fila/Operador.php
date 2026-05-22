<?php

namespace App\Livewire\Fila;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Operador')]
class Operador extends Component
{
    use InteractsWithFilaState;

    public string $queueFilter = 'all';

    public int $guiche = 3;

    public string $servico = 'triagem';

    public bool $showTransferModal = false;

    public string $transferServico = 'triagem';

    public string $transferMotivo = '';

    public int $timerSegundos = 0;

    public function mount(): void
    {
        $this->bootFilaState();
        $state = FilaState::get();
        $this->queueFilter = $state['queueFilter'];
        $this->guiche = $state['operador']['guiche'];
        $this->servico = $state['operador']['servico'];
        $this->timerSegundos = $state['timerSegundos'];
    }

    public function tickTimer(): void
    {
        $state = FilaState::get();
        if ($state['senhaAtual']) {
            $this->timerSegundos = $state['timerSegundos'] + 1;
            $state['timerSegundos'] = $this->timerSegundos;
            FilaState::set($state);
        }
    }

    public function updatedGuiche(int $guiche): void
    {
        $state = FilaState::get();
        $state['operador']['guiche'] = $guiche;
        FilaState::set($state);
    }

    public function updatedServico(string $servico): void
    {
        $state = FilaState::get();
        $state['operador']['servico'] = $servico;
        FilaState::set($state);
    }

    public function filterQueue(string $filter): void
    {
        $this->queueFilter = $filter;
        $state = FilaState::get();
        $state['queueFilter'] = $filter;
        FilaState::set($state);
    }

    public function chamarProxima(): void
    {
        $state = FilaState::get();
        $servicoId = $state['operador']['servico'];
        $senha = FilaState::proximaSenhaIntercalada($state, $servicoId);

        if (! $senha) {
            Flux::toast(variant: 'warning', text: 'Fila vazia para este serviço.');

            return;
        }

        $state['filas'][$servicoId] = array_values(array_filter(
            $state['filas'][$servicoId],
            fn ($s) => $s['id'] !== $senha['id']
        ));

        if (isset($state['intercalacao'][$servicoId])) {
            $state['intercalacao'][$servicoId]['cicloAtual']++;
        }

        $state['senhaAtual'] = array_merge($senha, ['chamadaEm' => now()->toIso8601String()]);
        $state['timerSegundos'] = 0;
        $this->timerSegundos = 0;

        $svc = FilaState::servico($state, $senha['servicoId']);
        $guiche = $state['operador']['guiche'];

        $state['painelAtual'] = [
            'codigo' => $senha['codigo'],
            'servico' => $svc['nome'] ?? '',
            'guiche' => str_pad((string) $guiche, 2, '0', STR_PAD_LEFT),
        ];

        array_unshift($state['historico'], [
            'codigo' => $senha['codigo'],
            'servico' => $svc['nome'] ?? '',
            'guiche' => $guiche,
            'hora' => now()->format('H:i'),
        ]);
        $state['historico'] = array_slice($state['historico'], 0, 20);

        $state['log'][] = [
            'tipo' => 'call',
            'msg' => "Chamou {$senha['codigo']} — ".($svc['nome'] ?? ''),
            'hora' => now()->format('H:i:s'),
        ];

        $state['kpis']['emEspera'] = FilaState::totalEmEspera($state);
        FilaState::set($state);

        Flux::toast(variant: 'success', text: "Chamando {$senha['codigo']}");
    }

    public function rechamarAtual(): void
    {
        $state = FilaState::get();
        if (! $state['senhaAtual']) {
            return;
        }

        $senha = $state['senhaAtual'];
        $svc = FilaState::servico($state, $senha['servicoId']);
        $state['painelAtual'] = [
            'codigo' => $senha['codigo'],
            'servico' => $svc['nome'] ?? '',
            'guiche' => str_pad((string) $state['operador']['guiche'], 2, '0', STR_PAD_LEFT),
        ];
        FilaState::set($state);
        Flux::toast(text: "Rechamando {$senha['codigo']}");
    }

    public function finalizarAtendimento(): void
    {
        $state = FilaState::get();
        if (! $state['senhaAtual']) {
            return;
        }

        $state['stats']['atendidos']++;
        $state['stats']['tempos'][] = $state['timerSegundos'];
        $state['kpis']['totalHoje']++;
        $state['log'][] = [
            'tipo' => 'finish',
            'msg' => "Finalizou {$state['senhaAtual']['codigo']} em {$state['timerSegundos']}s",
            'hora' => now()->format('H:i:s'),
        ];
        $state['senhaAtual'] = null;
        $state['timerSegundos'] = 0;
        $this->timerSegundos = 0;
        FilaState::set($state);
        Flux::toast(variant: 'success', text: 'Atendimento finalizado.');
    }

    public function marcarAusente(): void
    {
        $state = FilaState::get();
        if (! $state['senhaAtual']) {
            return;
        }

        $state['stats']['ausentes']++;
        $state['kpis']['ausentes']++;
        $state['log'][] = [
            'tipo' => 'absent',
            'msg' => "Ausente: {$state['senhaAtual']['codigo']}",
            'hora' => now()->format('H:i:s'),
        ];
        $state['senhaAtual'] = null;
        $state['timerSegundos'] = 0;
        $this->timerSegundos = 0;
        FilaState::set($state);
        Flux::toast(variant: 'warning', text: 'Senha marcada como ausente.');
    }

    public function confirmarTransferencia(): void
    {
        $state = FilaState::get();
        if (! $state['senhaAtual']) {
            return;
        }

        $senha = $state['senhaAtual'];
        $senha['servicoId'] = $this->transferServico;
        $senha['status'] = 'aguardando';
        $state['filas'][$this->transferServico][] = $senha;
        $state['senhaAtual'] = null;
        $state['timerSegundos'] = 0;
        $this->timerSegundos = 0;
        $this->showTransferModal = false;
        FilaState::set($state);
        Flux::toast(variant: 'success', text: 'Senha transferida.');
    }

    public function clearLog(): void
    {
        $state = FilaState::get();
        $state['log'] = [];
        FilaState::set($state);
    }

    #[Computed]
    public function filaFiltrada(): array
    {
        $state = $this->filaState;
        $fila = $state['filas'][$state['operador']['servico']] ?? [];

        return match ($this->queueFilter) {
            'preferencial' => array_values(array_filter($fila, fn ($s) => $s['isPreferencial'])),
            'normal' => array_values(array_filter($fila, fn ($s) => ! $s['isPreferencial'])),
            'agendado' => array_values(array_filter($fila, fn ($s) => $s['agendado'] ?? false)),
            default => $fila,
        };
    }

    #[Computed]
    public function temSenhaAtual(): bool
    {
        return $this->filaState['senhaAtual'] !== null;
    }

    #[Computed]
    public function timerFormatado(): string
    {
        $m = str_pad((string) intdiv($this->timerSegundos, 60), 2, '0', STR_PAD_LEFT);
        $s = str_pad((string) ($this->timerSegundos % 60), 2, '0', STR_PAD_LEFT);

        return "{$m}:{$s}";
    }

    #[Computed]
    public function intercalacaoBadge(): string
    {
        $ic = $this->filaState['intercalacao'][$this->servico] ?? ['normais' => 2, 'preferenciais' => 1];

        return "{$ic['normais']} normal : {$ic['preferenciais']} preferencial";
    }

    #[Computed]
    public function tMedio(): string
    {
        $tempos = $this->filaState['stats']['tempos'];
        if (count($tempos) === 0) {
            return '--';
        }

        return (string) (int) round(array_sum($tempos) / count($tempos)).'s';
    }

    public function render()
    {
        return view('livewire.fila.operador');
    }
}

<?php

namespace App\Livewire\Fila;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Totem')]
class Totem extends Component
{
    use InteractsWithFilaState;

    public string $prioridadeSelecionada = 'normal';

    public string $screen = 'home';

    public ?array $ticket = null;

    public function mount(): void
    {
        $this->bootFilaState();
        $state = FilaState::get();
        $this->prioridadeSelecionada = $state['prioridadeSelecionada'];
    }

    public function setPriority(string $tipo): void
    {
        $this->prioridadeSelecionada = $tipo;
        $state = FilaState::get();
        $state['prioridadeSelecionada'] = $tipo;
        FilaState::set($state);
    }

    public function emitirSenha(string $servicoId): void
    {
        $state = FilaState::get();
        $svc = FilaState::servico($state, $servicoId);
        if (! $svc) {
            return;
        }

        $prioridade = $this->prioridadeSelecionada;
        $isPreferencial = in_array($prioridade, ['idoso', 'pcd', 'gestante'], true);

        $state['contadores'][$servicoId] = ($state['contadores'][$servicoId] ?? 0) + 1;
        $num = str_pad((string) $state['contadores'][$servicoId], 3, '0', STR_PAD_LEFT);
        $codigo = "{$svc['prefixo']}{$num}";

        $senha = [
            'id' => "{$servicoId}_".now()->timestamp,
            'codigo' => $codigo,
            'servicoId' => $servicoId,
            'prioridade' => $prioridade,
            'isPreferencial' => $isPreferencial,
            'agendado' => false,
            'status' => 'aguardando',
            'emitidaEm' => now()->toIso8601String(),
            'posicao' => count($state['filas'][$servicoId] ?? []) + 1,
        ];

        if ($isPreferencial) {
            $idx = collect($state['filas'][$servicoId])->search(fn ($s) => ! $s['isPreferencial']);
            if ($idx === false) {
                $state['filas'][$servicoId][] = $senha;
            } else {
                array_splice($state['filas'][$servicoId], $idx, 0, [$senha]);
            }
        } else {
            $state['filas'][$servicoId][] = $senha;
        }

        $state['kpis']['emEspera'] = FilaState::totalEmEspera($state);
        FilaState::set($state);

        $this->ticket = [
            'codigo' => $codigo,
            'servico' => $svc['nome'],
            'prioridade' => $prioridade,
            'badge' => FilaState::prioridadeBadge($prioridade),
            'espera' => FilaState::calcEspera($state, $servicoId),
            'posicao' => $senha['posicao'],
            'data' => now()->format('d/m/Y H:i'),
        ];
        $this->screen = 'confirm';
    }

    public function resetTotem(): void
    {
        $this->screen = 'home';
        $this->ticket = null;
        $this->prioridadeSelecionada = 'normal';
        $state = FilaState::get();
        $state['prioridadeSelecionada'] = 'normal';
        FilaState::set($state);
    }

    public function render()
    {
        return view('livewire.fila.totem');
    }
}

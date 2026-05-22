<?php

namespace App\Livewire\Fila;

use App\Fila\Actions\EmitirSenha;
use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Exceptions\FilaException;
use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
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
        $ui = session(FilaState::SESSION_KEY, []);
        $this->prioridadeSelecionada = $ui['prioridade_selecionada'] ?? 'normal';
    }

    public function setPriority(string $tipo): void
    {
        $this->prioridadeSelecionada = $tipo;
        FilaState::set(['prioridadeSelecionada' => $tipo]);
    }

    public function emitirSenha(string $servicoId, EmitirSenha $emitir): void
    {
        try {
            $prioridade = PrioridadeSenha::from($this->prioridadeSelecionada);
            $resultado = $emitir->execute($servicoId, $prioridade);

            $this->ticket = [
                'codigo' => $resultado['codigo'],
                'servico' => $resultado['servico_nome'],
                'prioridade' => $resultado['prioridade'],
                'badge' => FilaState::prioridadeBadge($resultado['prioridade']),
                'espera' => $resultado['espera_estimada_minutos'],
                'posicao' => $resultado['posicao_fila'],
                'data' => now()->format('d/m/Y H:i'),
            ];
            $this->screen = 'confirm';
            unset($this->filaState);
        } catch (FilaException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function resetTotem(): void
    {
        $this->screen = 'home';
        $this->ticket = null;
        $this->prioridadeSelecionada = 'normal';
        FilaState::set(['prioridadeSelecionada' => 'normal']);
    }

    public function render()
    {
        return view('livewire.fila.totem');
    }
}

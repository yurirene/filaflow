<?php

namespace App\Livewire\Fila;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Painel TV')]
class Painel extends Component
{
    use InteractsWithFilaState;

    public string $ala = 'all';

    public string $clock = '--:--:--';

    public string $date = '';

    public string $lastCodigo = '---';

    public function mount(): void
    {
        $this->bootFilaState();
        $state = $this->filaState;
        $this->ala = $state['painelAla'];
        $this->lastCodigo = $state['painelAtual']['codigo'] ?? '---';
        $this->tickClock();
    }

    public function tickClock(): void
    {
        $now = now();
        $this->clock = $now->format('H:i:s');
        $this->date = $now->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y');

        unset($this->filaState);
        $codigo = $this->filaState['painelAtual']['codigo'] ?? '---';
        if ($codigo !== $this->lastCodigo && $codigo !== '---') {
            $this->lastCodigo = $codigo;
            $this->dispatch('painel-alert');
        }
    }

    public function updatedAla(string $ala): void
    {
        FilaState::set(['painelAla' => $ala]);
        unset($this->filaState);
    }

    public function render()
    {
        return view('livewire.fila.painel');
    }
}

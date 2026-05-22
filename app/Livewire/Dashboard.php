<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    use InteractsWithFilaState;

    public function mount(): void
    {
        $this->bootFilaState();
    }

    #[Computed]
    public function tMedioOperador(): string
    {
        $tempos = $this->filaState['stats']['tempos'];
        if (count($tempos) === 0) {
            return '--';
        }

        return (string) (int) round(array_sum($tempos) / count($tempos)).'s';
    }

    #[Computed]
    public function emEsperaAtual(): int
    {
        return FilaState::totalEmEspera($this->filaState);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

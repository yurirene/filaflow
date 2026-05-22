<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Intercalação')]
class Intercalacao extends Component
{
    use InteractsWithFilaState;

    public string $intServico = 'all';

    public int $intNormais = 2;

    public int $intPreferenciais = 1;

    public function mount(): void
    {
        $this->bootFilaState();
    }

    public function salvarIntercalacao(): void
    {
        $state = FilaState::get();
        $servicos = $this->intServico === 'all'
            ? array_keys($state['intercalacao'])
            : [$this->intServico];

        foreach ($servicos as $svcId) {
            if (isset($state['intercalacao'][$svcId])) {
                $state['intercalacao'][$svcId]['normais'] = $this->intNormais;
                $state['intercalacao'][$svcId]['preferenciais'] = $this->intPreferenciais;
            }
        }

        FilaState::set($state);
        Flux::toast(variant: 'success', text: __('Regra de intercalação salva.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.intercalacao');
    }
}

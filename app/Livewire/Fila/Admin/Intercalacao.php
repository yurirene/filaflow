<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Models\RegraIntercalacao;
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
        $query = RegraIntercalacao::query();

        if ($this->intServico !== 'all') {
            $query->where('servico_id', $this->intServico);
        }

        $query->update([
            'normais_por_ciclo' => $this->intNormais,
            'preferenciais_por_ciclo' => $this->intPreferenciais,
        ]);

        unset($this->filaState);
        Flux::toast(variant: 'success', text: __('Regra de intercalação salva.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.intercalacao');
    }
}

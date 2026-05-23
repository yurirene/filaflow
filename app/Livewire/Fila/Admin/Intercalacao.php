<?php

namespace App\Livewire\Fila\Admin;

use App\Models\RegraIntercalacao;
use App\Models\Servico;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Intercalação')]
class Intercalacao extends Component
{
    public string $intServico = 'all';

    public int $intNormais = 2;

    public int $intPreferenciais = 1;

    #[Computed]
    public function servicos()
    {
        return Servico::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function regras()
    {
        return RegraIntercalacao::query()->with('servico')->get();
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

        unset($this->regras);
        Flux::toast(variant: 'success', text: __('Regra de intercalação salva.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.intercalacao');
    }
}

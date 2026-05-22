<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Relatórios')]
class Relatorios extends Component
{
    use InteractsWithFilaState;

    public string $relPeriodo = 'hoje';

    public string $relServico = 'all';

    public string $relOperador = 'all';

    public ?string $relatorioResultado = null;

    public function mount(): void
    {
        $this->bootFilaState();
    }

    public function gerarRelatorio(): void
    {
        $state = $this->filaState;

        $this->relatorioResultado = __('Período: :periodo · Total atendimentos: :total · Em espera agora: :espera · Ausentes: :ausentes', [
            'periodo' => $this->relPeriodo,
            'total' => $state['kpis']['totalHoje'],
            'espera' => FilaState::totalEmEspera($state),
            'ausentes' => $state['kpis']['ausentes'],
        ]);
    }

    public function exportarRelatorio(): void
    {
        if (! $this->relatorioResultado) {
            Flux::toast(variant: 'warning', text: __('Gere o relatório antes de exportar.'));

            return;
        }

        Flux::toast(variant: 'success', text: __('Exportação CSV simulada.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.relatorios');
    }
}

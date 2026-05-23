<?php

namespace App\Livewire\Fila\Admin;

use App\Fila\Queries\RelatorioResumoQuery;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Relatórios')]
class Relatorios extends Component
{
    public string $relPeriodo = 'hoje';

    public string $relServico = 'all';

    public string $relOperador = 'all';

    public ?string $relatorioResultado = null;

    #[Computed]
    public function servicos()
    {
        return app(RelatorioResumoQuery::class)->servicosAtivos();
    }

    public function gerarRelatorio(): void
    {
        $servicoId = $this->relServico !== 'all' ? (int) $this->relServico : null;
        $resumo = app(RelatorioResumoQuery::class)->execute($servicoId);

        $this->relatorioResultado = __('Período: :periodo · Total atendimentos: :total · Em espera agora: :espera · Ausentes: :ausentes', [
            'periodo' => $this->relPeriodo,
            'total' => $resumo['totalHoje'],
            'espera' => $resumo['emEspera'],
            'ausentes' => $resumo['ausentes'],
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

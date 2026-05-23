<?php

namespace App\Livewire\Fila;

use App\Fila\OperadorSessao;
use App\Fila\Queries\PainelQuery;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Painel TV')]
class Painel extends Component
{
    public string $ala = 'all';

    public string $clock = '--:--:--';

    public string $date = '';

    public string $lastCodigo = '---';

    public function mount(): void
    {
        $this->ala = OperadorSessao::painelAla();
        $this->lastCodigo = app(PainelQuery::class)->painelAtual()['codigo'];
        $this->tickClock();
    }

    #[Computed]
    public function painelData(): array
    {
        return app(PainelQuery::class)->execute();
    }

    public function tickClock(): void
    {
        $now = now();
        $this->clock = $now->format('H:i:s');
        $this->date = $now->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y');
    }

    public function refreshPainel(): void
    {
        $this->tickClock();

        unset($this->painelData);
        $codigo = app(PainelQuery::class)->painelAtual()['codigo'];
        if ($codigo !== $this->lastCodigo && $codigo !== '---') {
            $this->lastCodigo = $codigo;
            $this->dispatch('painel-alert');
        }
    }

    public function updatedAla(string $ala): void
    {
        OperadorSessao::setPainelAla($ala);
    }

    #[Computed]
    public function historicoFiltrado(): array
    {
        $historico = $this->painelData['historico'];

        if ($this->ala === 'all') {
            return $historico;
        }

        $alaId = (int) $this->ala;

        return array_values(array_filter(
            $historico,
            fn (array $item) => ($item['alaId'] ?? null) === $alaId,
        ));
    }

    public function render()
    {
        return view('livewire.fila.painel');
    }
}

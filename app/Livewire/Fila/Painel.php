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

    public string $lastCodigo = '---';

    public function mount(): void
    {
        $this->ala = OperadorSessao::painelAla();
        $this->lastCodigo = app(PainelQuery::class)->painelAtual()['codigo'];
    }

    #[Computed]
    public function painelData(): array
    {
        $alaId = $this->ala === 'all' ? null : (int) $this->ala;

        return app(PainelQuery::class)->execute($alaId);
    }

    public function onFilaAtualizada(): void
    {
        $this->refreshPainel();
    }

    public function onSenhaChamada(): void
    {
        $this->refreshPainel();
    }

    public function refreshPainel(): void
    {
        unset($this->painelData);

        $alaId = $this->ala === 'all' ? null : (int) $this->ala;
        $codigo = app(PainelQuery::class)->painelAtual($alaId)['codigo'];
        if ($codigo !== $this->lastCodigo && $codigo !== '---') {
            $this->lastCodigo = $codigo;
            $this->dispatch('painel-alert');
        }
    }

    public function updatedAla(): void
    {
        OperadorSessao::setPainelAla($this->ala);
        unset($this->painelData);
    }

    public function render()
    {
        return view('livewire.fila.painel');
    }
}

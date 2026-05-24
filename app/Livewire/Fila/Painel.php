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

    public function onSenhaChamada(?int $alaId = null): void
    {
        if ($this->ala !== 'all' && $alaId !== null && (int) $this->ala !== $alaId) {
            return;
        }

        unset($this->painelData);

        $this->dispararAlertaPainel(forcar: true);
    }

    public function refreshPainel(): void
    {
        unset($this->painelData);

        $alaId = $this->ala === 'all' ? null : (int) $this->ala;
        $painelAtual = app(PainelQuery::class)->painelAtual($alaId);

        if ($painelAtual['codigo'] !== $this->lastCodigo && $painelAtual['codigo'] !== '---') {
            $this->dispararAlertaPainel($painelAtual);
        }
    }

    /** @param array{tipo: string, codigo: string, servico: string, local: string, paciente?: string|null}|null $painelAtual */
    protected function dispararAlertaPainel(?array $painelAtual = null, bool $forcar = false): void
    {
        $alaId = $this->ala === 'all' ? null : (int) $this->ala;
        $painelAtual ??= app(PainelQuery::class)->painelAtual($alaId);

        if ($painelAtual['codigo'] === '---') {
            return;
        }

        if (! $forcar && $painelAtual['codigo'] === $this->lastCodigo) {
            return;
        }

        $this->lastCodigo = $painelAtual['codigo'];
        $this->dispatch('painel-alert', painel: $painelAtual);
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

<?php

namespace App\Livewire\Fila;

use App\Fila\Actions\EmitirSenha;
use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Exceptions\FilaException;
use App\Fila\OperadorSessao;
use App\Fila\Queries\TotemIndexQuery;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Totem')]
class Totem extends Component
{
    public string $prioridadeSelecionada = 'normal';

    public string $screen = 'home';

    public ?array $ticket = null;

    public function mount(): void
    {
        $this->prioridadeSelecionada = OperadorSessao::prioridadeTotem();
    }

    #[Computed]
    public function totemData(): array
    {
        return app(TotemIndexQuery::class)->execute();
    }

    public function setPriority(string $tipo): void
    {
        $this->prioridadeSelecionada = $tipo;
        OperadorSessao::setPrioridadeTotem($tipo);
    }

    public function emitirSenha(int $servicoId, EmitirSenha $emitir): void
    {
        try {
            $prioridade = PrioridadeSenha::from($this->prioridadeSelecionada);
            $resultado = $emitir->execute($servicoId, $prioridade);

            $this->ticket = [
                'codigo' => $resultado['codigo'],
                'servico' => $resultado['servico_nome'],
                'prioridade' => $resultado['prioridade'],
                'badge' => PrioridadeSenha::badgeFrom($resultado['prioridade']),
                'espera' => $resultado['espera_estimada_minutos'],
                'posicao' => $resultado['posicao_fila'],
                'data' => now()->format('d/m/Y H:i'),
            ];
            $this->screen = 'confirm';
            unset($this->totemData);
        } catch (FilaException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function resetTotem(): void
    {
        $this->screen = 'home';
        $this->ticket = null;
        $this->prioridadeSelecionada = 'normal';
        OperadorSessao::setPrioridadeTotem('normal');
    }

    public function onFilaAtualizada(): void
    {
        unset($this->totemData);
    }

    public function render()
    {
        return view('livewire.fila.totem');
    }
}

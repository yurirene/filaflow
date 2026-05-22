<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Models\Guiche;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Guichês')]
class Guiches extends Component
{
    use InteractsWithFilaState;

    public bool $showGuicheModal = false;

    public int $guicheNum = 1;

    public string $guicheDesc = '';

    public string $guicheServico = '';

    public function mount(): void
    {
        $this->bootFilaState();
        $this->guicheServico = $this->filaState['servicos'][0]['id'] ?? '';
    }

    public function openGuicheModal(): void
    {
        $this->guicheNum = count($this->filaState['guiches']) + 1;
        $this->guicheDesc = '';
        $this->showGuicheModal = true;
    }

    public function salvarGuiche(): void
    {
        Guiche::query()->create([
            'numero' => $this->guicheNum,
            'descricao' => $this->guicheDesc ?: __('Guichê :num', ['num' => $this->guicheNum]),
            'servico_padrao_id' => $this->guicheServico ?: null,
            'ativo' => true,
        ]);

        $this->showGuicheModal = false;
        unset($this->filaState);
        Flux::toast(variant: 'success', text: __('Guichê adicionado.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.guiches');
    }
}

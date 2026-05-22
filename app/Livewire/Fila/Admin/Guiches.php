<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
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

    public string $guicheServico = 'triagem';

    public function mount(): void
    {
        $this->bootFilaState();
    }

    public function openGuicheModal(): void
    {
        $this->guicheNum = count(FilaState::get()['guiches']) + 1;
        $this->guicheDesc = '';
        $this->guicheServico = 'triagem';
        $this->showGuicheModal = true;
    }

    public function salvarGuiche(): void
    {
        $state = FilaState::get();
        $state['guiches'][] = [
            'id' => count($state['guiches']) + 1,
            'num' => $this->guicheNum,
            'desc' => $this->guicheDesc ?: __('Guichê :num', ['num' => $this->guicheNum]),
            'servico' => $this->guicheServico,
            'ativo' => true,
        ];
        FilaState::set($state);
        $this->showGuicheModal = false;
        Flux::toast(variant: 'success', text: __('Guichê adicionado.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.guiches');
    }
}

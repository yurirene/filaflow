<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Serviços')]
class Servicos extends Component
{
    use InteractsWithFilaState;

    public bool $showServiceModal = false;

    public string $svcNome = '';

    public string $svcPrefixo = '';

    public string $svcAla = '';

    public int $svcTMedio = 10;

    public string $svcCor = '#2563eb';

    public bool $svcAtivo = true;

    public function mount(): void
    {
        $this->bootFilaState();
    }

    public function openServiceModal(): void
    {
        $this->reset(['svcNome', 'svcPrefixo', 'svcAla']);
        $this->svcTMedio = 10;
        $this->svcCor = '#2563eb';
        $this->svcAtivo = true;
        $this->showServiceModal = true;
    }

    public function salvarServico(): void
    {
        $this->validate([
            'svcNome' => 'required|min:2',
            'svcPrefixo' => 'required|max:2',
        ]);

        $state = FilaState::get();
        $id = strtolower(str_replace(' ', '-', $this->svcNome));

        $state['servicos'][] = [
            'id' => $id,
            'nome' => $this->svcNome,
            'prefixo' => strtoupper($this->svcPrefixo),
            'ala' => $this->svcAla,
            'tMedio' => $this->svcTMedio,
            'cor' => $this->svcCor,
            'ativo' => $this->svcAtivo,
            'icon' => '🏥',
        ];

        $state['filas'][$id] = [];
        $state['contadores'][$id] = 0;
        $state['intercalacao'][$id] = ['normais' => 2, 'preferenciais' => 1, 'cicloAtual' => 0];
        FilaState::set($state);

        $this->showServiceModal = false;
        Flux::toast(variant: 'success', text: __('Serviço adicionado.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.servicos');
    }
}

<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Models\RegraIntercalacao;
use App\Models\Servico;
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

        $servico = Servico::query()->create([
            'nome' => $this->svcNome,
            'prefixo' => strtoupper($this->svcPrefixo),
            'ala' => $this->svcAla,
            'tempo_medio_minutos' => $this->svcTMedio,
            'cor' => $this->svcCor,
            'ativo' => $this->svcAtivo,
            'icone' => '🏥',
        ]);

        RegraIntercalacao::query()->create([
            'servico_id' => $servico->id,
            'normais_por_ciclo' => 2,
            'preferenciais_por_ciclo' => 1,
        ]);

        $this->showServiceModal = false;
        unset($this->filaState);
        Flux::toast(variant: 'success', text: __('Serviço adicionado.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.servicos');
    }
}

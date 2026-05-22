<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configurações')]
class Configuracoes extends Component
{
    use InteractsWithFilaState;

    public string $clinicName = '';

    public string $cnpj = '';

    public string $horaInicio = '07:00';

    public string $horaFim = '19:00';

    public string $ticker = '';

    public string $reinicioHora = '00:00';

    public string $som = 'beep';

    public function mount(): void
    {
        $this->bootFilaState();
        $config = FilaState::get()['config'];
        $this->clinicName = $config['clinicName'];
        $this->cnpj = $config['cnpj'] ?? '';
        $this->horaInicio = $config['horaInicio'];
        $this->horaFim = $config['horaFim'];
        $this->ticker = $config['ticker'];
        $this->reinicioHora = $config['reinicioHora'] ?? '00:00';
        $this->som = $config['som'] ?? 'beep';
    }

    public function salvarConfiguracoes(): void
    {
        $state = FilaState::get();
        $state['config'] = [
            'clinicName' => $this->clinicName,
            'cnpj' => $this->cnpj,
            'horaInicio' => $this->horaInicio,
            'horaFim' => $this->horaFim,
            'ticker' => $this->ticker,
            'reinicioHora' => $this->reinicioHora,
            'som' => $this->som,
        ];
        $state['clinicName'] = $this->clinicName;
        FilaState::set($state);
        Flux::toast(variant: 'success', text: __('Configurações salvas.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.configuracoes');
    }
}

<?php

namespace App\Livewire\Fila\Admin;

use App\Models\Empresa;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configurações')]
class Configuracoes extends Component
{
    public string $clinicName = '';

    public string $cnpj = '';

    public string $horaInicio = '07:00';

    public string $horaFim = '19:00';

    public string $ticker = '';

    public string $reinicioHora = '00:00';

    public string $som = 'beep';

    public function mount(): void
    {
        $empresa = Empresa::instancia();
        $this->clinicName = $empresa->nome;
        $this->cnpj = $empresa->cnpj ?? '';
        $this->horaInicio = $empresa->hora_inicio;
        $this->horaFim = $empresa->hora_fim;
        $this->ticker = $empresa->ticker ?? '';
        $this->reinicioHora = $empresa->reinicio_hora;
        $this->som = $empresa->som;
    }

    public function salvarConfiguracoes(): void
    {
        $empresa = Empresa::instancia();
        $empresa->update([
            'nome' => $this->clinicName,
            'cnpj' => $this->cnpj ?: null,
            'hora_inicio' => $this->horaInicio,
            'hora_fim' => $this->horaFim,
            'ticker' => $this->ticker,
            'reinicio_hora' => $this->reinicioHora,
            'som' => $this->som,
        ]);

        Flux::toast(variant: 'success', text: __('Configurações salvas.'));
    }

    public function render()
    {
        return view('livewire.fila.admin.configuracoes');
    }
}

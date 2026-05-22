<?php

namespace App\Livewire\Fila\Admin;

use App\Livewire\Concerns\InteractsWithFilaState;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Notificações')]
class Notificacoes extends Component
{
    use InteractsWithFilaState;

    public bool $whatsappAtivo = false;

    public string $whatsappProvider = 'z-api';

    public string $whatsappAntecedencia = '3';

    public string $whatsappMsg = '';

    public bool $smsAtivo = false;

    public string $smsProvider = 'twilio';

    public string $smsAntecedencia = '5';

    public function mount(): void
    {
        $this->bootFilaState();
        $state = FilaState::get();

        $wa = $state['notificacoes']['whatsapp'];
        $this->whatsappAtivo = $wa['ativo'];
        $this->whatsappProvider = $wa['provider'];
        $this->whatsappAntecedencia = (string) ($wa['antecedencia'] ?? 3);
        $this->whatsappMsg = $wa['mensagem'] ?? 'Olá {nome}! Sua senha {senha} está quase sendo chamada na {clinica}.';

        $sms = $state['notificacoes']['sms'];
        $this->smsAtivo = $sms['ativo'];
        $this->smsProvider = $sms['provider'];
        $this->smsAntecedencia = (string) ($sms['antecedencia'] ?? 5);
    }

    public function salvarNotificacao(string $tipo): void
    {
        $state = FilaState::get();

        if ($tipo === 'whatsapp') {
            $state['notificacoes']['whatsapp'] = [
                'ativo' => $this->whatsappAtivo,
                'provider' => $this->whatsappProvider,
                'antecedencia' => (int) $this->whatsappAntecedencia,
                'mensagem' => $this->whatsappMsg,
            ];
        } else {
            $state['notificacoes']['sms'] = [
                'ativo' => $this->smsAtivo,
                'provider' => $this->smsProvider,
                'antecedencia' => (int) $this->smsAntecedencia,
            ];
        }

        FilaState::set($state);
        Flux::toast(variant: 'success', text: __('Notificações salvas.'));
    }

    public function testarNotificacao(string $tipo): void
    {
        Flux::toast(text: __('Teste de :tipo enviado (simulação).', ['tipo' => strtoupper($tipo)]));
    }

    public function render()
    {
        return view('livewire.fila.admin.notificacoes');
    }
}

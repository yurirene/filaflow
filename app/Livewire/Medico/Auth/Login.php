<?php

namespace App\Livewire\Medico\Auth;

use App\Models\Medico;
use App\Support\VerificadorSenha;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login — Médico')]
class Login extends Component
{
    public string $cpf = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::guard('medico')->check()) {
            $this->redirect(route('medico.painel'), navigate: true);
        }
    }

    public function login(): void
    {
        $this->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $cpf = Medico::normalizarCpf($this->cpf);
        $throttleKey = 'medico-login:'.$cpf.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'cpf' => __('Muitas tentativas. Tente novamente em :seconds segundos.', ['seconds' => $seconds]),
            ]);
        }

        $medico = Medico::query()->with('consultorio')->where('cpf', $cpf)->first();

        if (! $medico || ! VerificadorSenha::validar($medico, $this->password)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'cpf' => __('CPF ou senha inválidos.'),
            ]);
        }

        if (! $medico->isAtivo()) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'cpf' => __('Médico desativado. Entre em contato com a administração.'),
            ]);
        }

        if (! $medico->consultorio) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'cpf' => __('Nenhum consultório vinculado a este médico. Entre em contato com a administração.'),
            ]);
        }

        if (! $medico->consultorio->ativo) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'cpf' => __('Consultório inativo. Entre em contato com a administração.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('medico')->login($medico, $this->remember);

        session()->regenerate();

        $this->redirect(route('medico.painel'), navigate: true);
    }

    public function render()
    {
        return view('livewire.medico.auth.login');
    }
}

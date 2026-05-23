<?php

namespace App\Livewire\Operador\Auth;

use App\Models\Operador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login — Operador')]
class Login extends Component
{
    public string $cpf = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::guard('operador')->check()) {
            $this->redirect(route('operador.painel'), navigate: true);
        }
    }

    public function login(): void
    {
        $this->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $cpf = Operador::normalizarCpf($this->cpf);
        $throttleKey = 'operador-login:'.$cpf.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'cpf' => __('Muitas tentativas. Tente novamente em :seconds segundos.', ['seconds' => $seconds]),
            ]);
        }

        $operador = Operador::query()->where('cpf', $cpf)->first();

        if (! $operador || ! Auth::guard('operador')->getProvider()->validateCredentials($operador, ['password' => $this->password])) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'cpf' => __('CPF ou senha inválidos.'),
            ]);
        }

        if (! $operador->isAtivo()) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'cpf' => __('Operador desativado. Entre em contato com a administração.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('operador')->login($operador, $this->remember);

        session()->regenerate();

        $this->redirect(route('operador.painel'), navigate: true);
    }

    public function render()
    {
        return view('livewire.operador.auth.login');
    }
}

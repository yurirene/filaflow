<?php

namespace App\Livewire\Concerns;

use App\Support\FilaState;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait InteractsWithFilaState
{
    protected function bootFilaState(): void
    {
        FilaState::ensureSeeded();

        if (Auth::check()) {
            $state = FilaState::get();
            $state['operador']['nome'] = Auth::user()->name;
            FilaState::set($state);
        }
    }

    #[Computed]
    public function filaState(): array
    {
        return FilaState::ensureSeeded();
    }
}

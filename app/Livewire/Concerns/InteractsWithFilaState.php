<?php

namespace App\Livewire\Concerns;

use App\Fila\TenantContext;
use App\Models\Empresa;
use App\Support\FilaState;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait InteractsWithFilaState
{
    protected function bootFilaState(): void
    {
        if ($user = Auth::user()) {
            TenantContext::set($user->empresa_id);
        } elseif (! TenantContext::empresaId()) {
            TenantContext::set(Empresa::query()->value('id'));
        }
    }

    #[Computed]
    public function filaState(): array
    {
        $this->bootFilaState();

        return FilaState::ensureSeeded();
    }
}

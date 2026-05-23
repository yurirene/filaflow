<?php

namespace App\Livewire;

use App\Fila\OperadorSessao;
use App\Fila\Queries\DashboardKpisQuery;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function dashboardData(): array
    {
        return app(DashboardKpisQuery::class)->execute();
    }

    #[Computed]
    public function tMedioOperador(): string
    {
        $tempos = OperadorSessao::tempos();
        if (count($tempos) === 0) {
            return '--';
        }

        return (string) (int) round(array_sum($tempos) / count($tempos)).'s';
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

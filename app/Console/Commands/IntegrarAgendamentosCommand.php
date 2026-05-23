<?php

namespace App\Console\Commands;

use App\Fila\Actions\IntegrarAgendamentos;
use Illuminate\Console\Command;

class IntegrarAgendamentosCommand extends Command
{
    protected $signature = 'fila:integrar-agendamentos';

    protected $description = 'Coloca agendamentos do dia na fila (antecedência configurável)';

    public function handle(IntegrarAgendamentos $integrar): int
    {
        $n = $integrar->execute();
        $this->info("{$n} agendamento(s) integrado(s).");

        return self::SUCCESS;
    }
}

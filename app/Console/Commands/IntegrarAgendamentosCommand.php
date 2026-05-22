<?php

namespace App\Console\Commands;

use App\Fila\Actions\IntegrarAgendamentos;
use App\Fila\TenantContext;
use App\Models\Empresa;
use Illuminate\Console\Command;

class IntegrarAgendamentosCommand extends Command
{
    protected $signature = 'fila:integrar-agendamentos {--empresa= : UUID da empresa}';

    protected $description = 'Coloca agendamentos do dia na fila (antecedência configurável)';

    public function handle(IntegrarAgendamentos $integrar): int
    {
        $empresas = $this->option('empresa')
            ? Empresa::query()->where('id', $this->option('empresa'))->get()
            : Empresa::query()->where('ativo', true)->get();

        $total = 0;
        foreach ($empresas as $empresa) {
            TenantContext::set($empresa->id);
            $n = $integrar->execute();
            $total += $n;
            $this->info("Empresa {$empresa->nome}: {$n} agendamento(s) integrado(s).");
        }

        return self::SUCCESS;
    }
}

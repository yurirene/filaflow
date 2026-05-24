<?php

namespace App\Fila\Actions;

use App\Fila\Enums\PrioridadeSenha;
use App\Fila\Enums\StatusAgendamento;
use App\Models\Agendamento;
use Illuminate\Support\Carbon;

class IntegrarAgendamentos
{
    public function __construct(
        protected EmitirSenha $emitirSenha,
    ) {}

    /** Antecedência em minutos para colocar agendamento na fila. */
    public int $antecedenciaMinutos = 15;

    public function execute(?Carbon $referencia = null): int
    {
        $referencia ??= now();
        $limite = $referencia->copy()->addMinutes($this->antecedenciaMinutos);
        $integrados = 0;

        Agendamento::query()
            ->where('status', StatusAgendamento::Agendado)
            ->where('data_hora', '<=', $limite)
            ->orderBy('data_hora')
            ->each(function (Agendamento $agendamento) use (&$integrados): void {
                $this->emitirSenha->execute(
                    servicoId: $agendamento->servico_id,
                    prioridade: PrioridadeSenha::Normal,
                    pacienteCelular: $agendamento->paciente_celular,
                    isAgendado: true,
                    pacienteNome: $agendamento->paciente_nome,
                );

                $agendamento->update(['status' => StatusAgendamento::NaFila]);
                $integrados++;
            });

        return $integrados;
    }
}

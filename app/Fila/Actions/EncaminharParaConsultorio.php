<?php

namespace App\Fila\Actions;

use App\Fila\Enums\StatusSenha;
use App\Fila\Events\FilaAtualizada;
use App\Fila\Exceptions\FilaException;
use App\Fila\Services\OrdemFilaService;
use App\Models\Consultorio;
use App\Models\Senha;
use App\Models\Servico;

class EncaminharParaConsultorio
{
    public function __construct(
        protected OrdemFilaService $ordemFila,
    ) {}

    public function execute(Senha $senha, int $servicoDestinoId, int $consultorioId, string $pacienteNome): Senha
    {
        if ($senha->consultorio_id !== null) {
            throw FilaException::senhaJaEncaminhada();
        }

        $pacienteNome = $this->normalizarPacienteNome($pacienteNome);

        $destino = Servico::query()->where('id', $servicoDestinoId)->where('ativo', true)->first()
            ?? throw FilaException::servicoInativo();

        $consultorio = Consultorio::query()
            ->with(['servicos', 'ala'])
            ->where('id', $consultorioId)
            ->where('ativo', true)
            ->first()
            ?? throw FilaException::consultorioInvalido();

        if (! $consultorio->ala?->is_consultorio) {
            throw FilaException::alaNaoEhConsultorio();
        }

        if (! $consultorio->aceitaServico($destino)) {
            throw FilaException::servicoNaoPermitidoNoConsultorio();
        }

        $ordem = $this->ordemFila->proximaOrdem(
            $destino->id,
            (bool) $senha->is_preferencial,
            (bool) $senha->is_agendado,
            $consultorio->id,
        );

        $senha->update([
            'servico_id' => $destino->id,
            'consultorio_id' => $consultorio->id,
            'paciente_nome' => $pacienteNome,
            'status' => StatusSenha::Aguardando,
            'chamada_em' => null,
            'ordem_fila' => $ordem,
        ]);

        $tamanho = Senha::query()
            ->aguardando()
            ->where('consultorio_id', $consultorio->id)
            ->count();

        FilaAtualizada::dispatch(
            servicoId: $destino->id,
            tamanhoFila: $tamanho,
            esperaEstimada: 0,
        );

        return $senha->fresh(['servico', 'consultorio']);
    }

    protected function normalizarPacienteNome(string $nome): string
    {
        $nome = trim(preg_replace('/\s+/u', ' ', $nome) ?? '');
        $partes = $nome === '' ? [] : explode(' ', $nome);

        if (count($partes) < 2 || mb_strlen($nome) < 5) {
            throw FilaException::pacienteNomeIncompleto();
        }

        foreach ($partes as $parte) {
            if (mb_strlen($parte) < 2) {
                throw FilaException::pacienteNomeIncompleto();
            }
        }

        return $nome;
    }
}

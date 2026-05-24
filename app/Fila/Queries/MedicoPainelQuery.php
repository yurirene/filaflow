<?php

namespace App\Fila\Queries;

use App\Fila\Enums\StatusSenha;
use App\Fila\MedicoSessao;
use App\Models\Consultorio;
use App\Models\Empresa;
use App\Models\Senha;

class MedicoPainelQuery
{
    /**
     * @return array{
     *     empresa: Empresa,
     *     consultorio: Consultorio,
     *     senhaAtual: ?Senha,
     *     atendidosHoje: int
     * }
     */
    public function execute(Consultorio $consultorio): array
    {
        $consultorio->loadMissing(['ala', 'medico']);

        $empresa = Empresa::instancia();
        $hoje = now()->startOfDay();

        $atendidosHoje = Senha::query()
            ->where('consultorio_id', $consultorio->id)
            ->where('status', StatusSenha::Finalizado)
            ->where('finalizada_em', '>=', $hoje)
            ->count();

        return [
            'empresa' => $empresa,
            'consultorio' => $consultorio,
            'senhaAtual' => $this->senhaAtual($consultorio->id),
            'atendidosHoje' => $atendidosHoje,
        ];
    }

    public function senhaAtual(int $consultorioId): ?Senha
    {
        $id = MedicoSessao::senhaAtualId();
        if (! $id) {
            return null;
        }

        $senha = Senha::query()
            ->with(['servico', 'consultorio.medico'])
            ->where('consultorio_id', $consultorioId)
            ->find($id);

        if (! $senha || ! in_array($senha->status, [StatusSenha::Chamado, StatusSenha::Atendimento], true)) {
            return null;
        }

        return $senha;
    }
}

<?php

namespace App\Fila\Queries;

use App\Fila\Enums\StatusAgendamento;
use App\Fila\Enums\StatusSenha;
use App\Fila\OperadorSessao;
use App\Models\Agendamento;
use App\Models\Consultorio;
use App\Models\Empresa;
use App\Models\Guiche;
use App\Models\RegraIntercalacao;
use App\Models\Senha;
use App\Models\Servico;
use Illuminate\Support\Collection;

class OperadorPainelQuery
{
    /**
     * @return array{
     *     empresa: Empresa,
     *     servicos: Collection<int, Servico>,
     *     consultorios: Collection<int, Consultorio>,
     *     senhaAtual: ?Senha,
     *     atendidosHoje: int,
     *     agendamentos: Collection<int, Agendamento>
     * }
     */
    public function execute(?int $servicoId = null): array
    {
        $empresa = Empresa::instancia();

        $servicos = Servico::query()
            ->with('ala')
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $consultorios = Consultorio::query()
            ->with(['ala', 'medico'])
            ->where('ativo', true)
            ->orderBy('ala_id')
            ->orderBy('numero')
            ->get();

        $servicoId ??= OperadorSessao::servicoId() ?? $servicos->first()?->id;

        $hoje = now()->startOfDay();

        $atendidosHoje = Senha::query()
            ->where('status', StatusSenha::Finalizado)
            ->where('finalizada_em', '>=', $hoje)
            ->count();

        $agendamentos = Agendamento::query()
            ->with('servico')
            ->whereDate('data_hora', now()->toDateString())
            ->orderBy('data_hora')
            ->get();

        return [
            'empresa' => $empresa,
            'servicos' => $servicos,
            'consultorios' => $consultorios,
            'senhaAtual' => $this->senhaAtual(),
            'atendidosHoje' => $atendidosHoje,
            'agendamentos' => $agendamentos,
        ];
    }

    public function senhaAtual(): ?Senha
    {
        $id = OperadorSessao::senhaAtualId();
        if (! $id) {
            return null;
        }

        $senha = Senha::query()->with(['servico', 'consultorio'])->find($id);
        if (! $senha || ! in_array($senha->status, [StatusSenha::Chamado, StatusSenha::Atendimento], true)) {
            return null;
        }

        return $senha;
    }

    /** @return Collection<int, Senha> */
    public function filaAguardandoGuiche(Guiche $guiche, ?int $servicoId = null): Collection
    {
        $query = Senha::query()
            ->with('servico')
            ->aguardando()
            ->whereNull('consultorio_id')
            ->orderBy('ordem_fila')
            ->orderBy('emitida_em');

        if ($servicoId) {
            $query->where('servico_id', $servicoId);
        } else {
            $query->whereIn('servico_id', $this->servicosDoGuiche($guiche)->pluck('id'));
        }

        return $query->get();
    }

    /** @return Collection<int, Senha> */
    public function filaAguardandoConsultorio(int $consultorioId, ?int $servicoId = null): Collection
    {
        $query = Senha::query()
            ->with('servico')
            ->aguardando()
            ->where('consultorio_id', $consultorioId)
            ->orderBy('ordem_fila')
            ->orderBy('emitida_em');

        if ($servicoId) {
            $query->where('servico_id', $servicoId);
        }

        return $query->get();
    }

    /** @return Collection<int, Guiche> */
    public function guichesAtivos(): Collection
    {
        return Guiche::query()
            ->with('ala')
            ->where('ativo', true)
            ->orderBy('ala_id')
            ->orderBy('numero')
            ->get();
    }

    /** @return Collection<int, Servico> */
    public function servicosDoGuiche(Guiche $guiche): Collection
    {
        return Servico::query()
            ->where('ala_id', $guiche->ala_id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    /** @return Collection<int, Guiche> */
    public function guichesDaAla(?int $alaId): Collection
    {
        if (! $alaId) {
            return collect();
        }

        return Guiche::query()
            ->with('ala')
            ->where('ala_id', $alaId)
            ->where('ativo', true)
            ->orderBy('numero')
            ->get();
    }

    /** @return Collection<int, Consultorio> */
    public function consultoriosDaAla(?int $alaId): Collection
    {
        if (! $alaId) {
            return collect();
        }

        return Consultorio::query()
            ->with(['ala', 'medico'])
            ->where('ala_id', $alaId)
            ->where('ativo', true)
            ->orderBy('numero')
            ->get();
    }

    /** @return Collection<int, Servico> */
    public function servicosPermitidosConsultorio(Consultorio $consultorio): Collection
    {
        if ($consultorio->servicos()->exists()) {
            return $consultorio->servicos()
                ->where('ativo', true)
                ->orderBy('nome')
                ->get();
        }

        return Servico::query()
            ->where('ala_id', $consultorio->ala_id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    public function regraIntercalacao(int $servicoId): ?RegraIntercalacao
    {
        return RegraIntercalacao::query()->where('servico_id', $servicoId)->first();
    }

    public static function agendamentoStatusLabel(StatusAgendamento $status): string
    {
        return match ($status) {
            StatusAgendamento::NaFila => 'na-fila',
            StatusAgendamento::Atendido => 'atendido',
            default => 'aguardando',
        };
    }
}

<?php

namespace App\Livewire\Fila;

use App\Fila\Actions\ChamarProximaConsultorio;
use App\Fila\Actions\ChamarProximaSenha;
use App\Fila\Actions\EncaminharParaConsultorio;
use App\Fila\Actions\FinalizarSenha;
use App\Fila\Actions\MarcarSenhaAusente;
use App\Fila\Actions\RechamarSenha;
use App\Fila\Actions\TransferirSenha;
use App\Fila\Exceptions\FilaException;
use App\Fila\MedicoSessao;
use App\Fila\OperadorSessao;
use App\Fila\Queries\OperadorPainelQuery;
use App\Models\Ala;
use App\Models\Chamada;
use App\Models\Consultorio;
use App\Models\Guiche;
use App\Models\Operador as OperadorModel;
use App\Models\Senha;
use App\Models\Servico;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Operador')]
class Operador extends Component
{
    public string $modo = OperadorSessao::MODO_GUICHE;

    public bool $showPasswordModal = false;

    public string $senhaAtual = '';

    public string $senhaNova = '';

    public string $senhaNovaConfirmation = '';

    public string $queueFilter = 'all';

    public ?int $guicheId = null;

    public ?int $guiche = null;

    public string $servico = '';

    public string $consultorio = '';

    public bool $showTransferModal = false;

    public bool $showEncaminharModal = false;

    public string $transferAla = '';

    public string $transferServico = '';

    public string $transferMotivo = '';

    public string $encAla = '';

    public string $encServico = '';

    public string $encConsultorio = '';

    public string $encPacienteNome = '';

    public int $timerSegundos = 0;

    public function mount(): void
    {
        $this->modo = OperadorSessao::modo();
        $this->queueFilter = OperadorSessao::queueFilter();
        $this->timerSegundos = OperadorSessao::timerSegundos();

        $this->guicheId = OperadorSessao::guicheId()
            ?? app(OperadorPainelQuery::class)->guichesAtivos()->first()?->id;

        $guicheModel = $this->guicheId
            ? Guiche::query()->with('servicoPadrao')->find($this->guicheId)
            : null;

        if ($guicheModel && ! $guicheModel->ativo) {
            $guicheModel = app(OperadorPainelQuery::class)->guichesAtivos()->first();
            $this->guicheId = $guicheModel?->id;
        }

        $servicosDaAla = $guicheModel
            ? app(OperadorPainelQuery::class)->servicosDoGuiche($guicheModel)
            : collect();

        $servicoId = OperadorSessao::servicoId();
        if ($servicoId && $servicosDaAla->contains('id', $servicoId)) {
            $this->servico = (string) $servicoId;
        } else {
            $this->aplicarServicoPadraoGuiche($guicheModel, $servicosDaAla);
        }

        $this->transferServico = $this->servico ?: (string) ($servicosDaAla->first()?->id ?? '');

        $consultorioId = OperadorSessao::consultorioId()
            ?? Consultorio::query()->where('ativo', true)->orderBy('numero')->value('id');

        $this->consultorio = (string) ($consultorioId ?? '');

        if ($this->modo === OperadorSessao::MODO_GUICHE && $this->guicheId) {
            OperadorSessao::setOperadorContext(
                $this->guicheId,
                $this->servico ? (int) $this->servico : null,
            );
        }

        if ($this->modo === OperadorSessao::MODO_CONSULTORIO && $consultorioId) {
            OperadorSessao::setConsultorioContext($consultorioId, $this->servico ? (int) $this->servico : null);
        }
    }

    #[Computed]
    public function operadorPainel(): array
    {
        return app(OperadorPainelQuery::class)->execute($this->servico ? (int) $this->servico : null);
    }

    #[Computed]
    public function filaAguardando(): Collection
    {
        if ($this->modo === OperadorSessao::MODO_CONSULTORIO) {
            if (! $this->consultorio) {
                return collect();
            }

            $servicoId = $this->servico ? (int) $this->servico : null;

            return app(OperadorPainelQuery::class)->filaAguardandoConsultorio((int) $this->consultorio, $servicoId);
        }

        $guiche = $this->guicheAtualModel;
        if (! $guiche) {
            return collect();
        }

        $servicoId = $this->servico ? (int) $this->servico : null;

        return app(OperadorPainelQuery::class)->filaAguardandoGuiche($guiche, $servicoId);
    }

    #[Computed]
    public function guicheAtualModel(): ?Guiche
    {
        if (! $this->guicheId) {
            return null;
        }

        return Guiche::query()
            ->with(['ala', 'servicoPadrao'])
            ->where('id', $this->guicheId)
            ->where('ativo', true)
            ->first();
    }

    /** @return Collection<int, Servico> */
    #[Computed]
    public function servicosGuiche(): Collection
    {
        $guiche = $this->guicheAtualModel;
        if (! $guiche) {
            return collect();
        }

        return app(OperadorPainelQuery::class)->servicosDoGuiche($guiche);
    }

    #[Computed]
    public function guichesDaAlaAtual(): Collection
    {
        $guiche = $this->guicheAtualModel;
        if (! $guiche) {
            return app(OperadorPainelQuery::class)->guichesAtivos();
        }

        return app(OperadorPainelQuery::class)->guichesDaAla($guiche->ala_id);
    }

    #[Computed]
    public function consultoriosDisponiveis(): Collection
    {
        return $this->operadorPainel['consultorios'];
    }

    /** @return Collection<int, Ala> */
    #[Computed]
    public function alasAtivas(): Collection
    {
        return Ala::query()->ativa()->orderBy('nome')->get();
    }

    /** @return Collection<int, Ala> */
    #[Computed]
    public function alasConsultorio(): Collection
    {
        return Ala::query()->ativa()->consultorio()->orderBy('nome')->get();
    }

    /** @return Collection<int, Servico> */
    #[Computed]
    public function servicosTransferir(): Collection
    {
        if (! $this->transferAla) {
            return collect();
        }

        return Servico::query()
            ->where('ala_id', (int) $this->transferAla)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function consultoriosEncaminhar(): Collection
    {
        if (! $this->encAla) {
            return collect();
        }

        return app(OperadorPainelQuery::class)->consultoriosDaAla((int) $this->encAla);
    }

    #[Computed]
    public function servicosEncaminhar(): Collection
    {
        if (! $this->encConsultorio) {
            return collect();
        }

        $consultorio = Consultorio::query()->find((int) $this->encConsultorio);
        if (! $consultorio) {
            return collect();
        }

        return app(OperadorPainelQuery::class)->servicosPermitidosConsultorio($consultorio);
    }

    public function onFilaAtualizada(): void
    {
        $this->invalidateOperadorComputeds();
    }

    public function alternarModo(string $modo): void
    {
        $this->modo = $modo;
        OperadorSessao::setModo($modo);

        if ($modo === OperadorSessao::MODO_GUICHE && $this->guicheId) {
            OperadorSessao::setOperadorContext(
                $this->guicheId,
                $this->servico ? (int) $this->servico : null,
            );
        }

        if ($modo === OperadorSessao::MODO_CONSULTORIO && $this->consultorio) {
            OperadorSessao::setConsultorioContext(
                (int) $this->consultorio,
                $this->servico ? (int) $this->servico : null,
            );
        }

        $this->invalidateOperadorComputeds();
    }

    public function hydrate(): void
    {
        $this->migrarGuicheLegado();
    }

    public function updatedGuiche(?int $guiche): void
    {
        if ($guiche === null || $this->modo !== OperadorSessao::MODO_GUICHE) {
            return;
        }

        $guicheModel = $this->resolverGuichePorNumero($guiche);
        if (! $guicheModel) {
            return;
        }

        $this->guicheId = $guicheModel->id;
        $this->guiche = null;
        $this->updatedGuicheId($this->guicheId);
    }

    public function updatedGuicheId(?int $guicheId): void
    {
        if ($this->modo !== OperadorSessao::MODO_GUICHE || ! $guicheId) {
            $this->invalidateOperadorComputeds();

            return;
        }

        unset($this->guicheAtualModel, $this->servicosGuiche, $this->guichesDaAlaAtual);

        $guicheModel = $this->guicheAtualModel;
        $this->aplicarServicoPadraoGuiche($guicheModel, $this->servicosGuiche);

        OperadorSessao::setOperadorContext(
            $guicheId,
            $this->servico ? (int) $this->servico : null,
        );

        $this->invalidateOperadorComputeds();
    }

    public function updatedServico(string $servico): void
    {
        if ($this->modo === OperadorSessao::MODO_GUICHE && $this->guicheId) {
            OperadorSessao::setOperadorContext(
                $this->guicheId,
                $servico ? (int) $servico : null,
            );

            if ($servico) {
                $this->transferServico = $servico;
            }
        }

        if ($this->modo === OperadorSessao::MODO_CONSULTORIO && $this->consultorio) {
            OperadorSessao::setConsultorioContext(
                (int) $this->consultorio,
                $servico ? (int) $servico : null,
            );
        }

        $this->invalidateOperadorComputeds();
    }

    public function updatedConsultorio(string $consultorio): void
    {
        if ($consultorio) {
            OperadorSessao::setConsultorioContext(
                (int) $consultorio,
                $this->servico ? (int) $this->servico : null,
            );
        }
        $this->invalidateOperadorComputeds();
    }

    public function filterQueue(string $filter): void
    {
        $this->queueFilter = $filter;
        OperadorSessao::setQueueFilter($filter);
    }

    public function abrirEncaminhar(): void
    {
        $senha = $this->senhaAtualModel;
        if (! $senha) {
            return;
        }

        $senha->load('servico.ala');
        $alasConsultorio = $this->alasConsultorio;

        if ($alasConsultorio->isEmpty()) {
            Flux::toast(variant: 'warning', text: __('Nenhuma ala de consultório cadastrada.'));

            return;
        }

        $alaAtual = $senha->servico->ala;
        $this->encAla = (string) (
            $alaAtual?->is_consultorio
                ? $alaAtual->id
                : $alasConsultorio->first()->id
        );
        $this->encServico = (string) $senha->servico_id;
        $consultorios = app(OperadorPainelQuery::class)->consultoriosDaAla((int) $this->encAla);
        $this->encConsultorio = (string) ($consultorios->first()?->id ?? '');
        $this->encPacienteNome = $senha->paciente_nome ?? '';
        $this->showEncaminharModal = true;
        unset($this->alasConsultorio, $this->consultoriosEncaminhar, $this->servicosEncaminhar);
    }

    public function abrirTransferir(): void
    {
        $senha = $this->senhaAtualModel;
        if (! $senha) {
            return;
        }

        $senha->load('servico');
        $this->transferAla = (string) $senha->servico->ala_id;
        $this->transferServico = (string) $senha->servico_id;
        $this->showTransferModal = true;
        unset($this->servicosTransferir);
    }

    public function updatedTransferAla(): void
    {
        $servicos = $this->servicosTransferir;
        $this->transferServico = (string) ($servicos->first()?->id ?? '');
        unset($this->servicosTransferir);
    }

    public function updatedEncAla(): void
    {
        unset($this->consultoriosEncaminhar, $this->servicosEncaminhar);
        $consultorios = $this->consultoriosEncaminhar;
        $this->encConsultorio = (string) ($consultorios->first()?->id ?? '');
        $this->updatedEncConsultorio();
    }

    public function updatedEncConsultorio(): void
    {
        $servicos = $this->servicosEncaminhar;
        if ($servicos->isNotEmpty() && ! $servicos->contains('id', (int) $this->encServico)) {
            $this->encServico = (string) $servicos->first()->id;
        }
    }

    public function confirmarEncaminhamento(EncaminharParaConsultorio $encaminhar): void
    {
        $senhaId = OperadorSessao::senhaAtualId();
        if (! $senhaId || ! $this->encConsultorio || ! $this->encServico) {
            return;
        }

        $this->validate([
            'encPacienteNome' => ['required', 'string', 'max:150'],
        ], [], [
            'encPacienteNome' => __('nome do paciente'),
        ]);

        $senha = Senha::query()->find($senhaId);
        if (! $senha) {
            return;
        }

        try {
            $encaminhar->execute(
                $senha,
                (int) $this->encServico,
                (int) $this->encConsultorio,
                $this->encPacienteNome,
            );
            OperadorSessao::setSenhaAtual(null);
            $this->timerSegundos = 0;
            OperadorSessao::setTimerSegundos(0);
            $this->showEncaminharModal = false;
            $this->invalidateOperadorComputeds();
            Flux::toast(variant: 'success', text: __('Senha encaminhada ao consultório.'));
        } catch (FilaException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function chamarProxima(ChamarProximaSenha $chamarGuiche, ChamarProximaConsultorio $chamarConsultorio): void
    {
        try {
            if ($this->modo === OperadorSessao::MODO_CONSULTORIO) {
                $this->chamarProximaConsultorio($chamarConsultorio);

                return;
            }

            $guiche = $this->guicheAtualModel
                ?? throw FilaException::guicheInvalido();

            $servicoId = $this->servico ? (int) $this->servico : null;
            $resultado = $chamarGuiche->execute($guiche->id, $servicoId);

            $senha = $resultado['senha'];
            $svc = $resultado['servico'];

            OperadorSessao::setSenhaAtual($senha->id);
            OperadorSessao::setPainelAtual([
                'tipo' => OperadorSessao::MODO_GUICHE,
                'codigo' => $senha->codigo,
                'servico' => $svc->nome,
                'local' => str_pad((string) $guiche->numero, 2, '0', STR_PAD_LEFT),
            ]);
            OperadorSessao::pushLog('call', "Chamou {$senha->codigo} — {$svc->nome}");

            $this->timerSegundos = 0;
            OperadorSessao::setTimerSegundos(0);
            $this->invalidateOperadorComputeds();

            Flux::toast(variant: 'success', text: "Chamando {$senha->codigo}");
        } catch (FilaException $e) {
            Flux::toast(variant: 'warning', text: $e->getMessage());
        }
    }

    protected function chamarProximaConsultorio(ChamarProximaConsultorio $chamar): void
    {
        if (! $this->consultorio) {
            throw FilaException::consultorioInvalido();
        }

        $servicoId = $this->servico ? (int) $this->servico : null;
        $resultado = $chamar->execute((int) $this->consultorio, $servicoId);

        $senha = $resultado['senha'];
        $svc = $resultado['servico'];
        $consultorio = $resultado['consultorio'];

        $local = str_pad((string) $consultorio->numero, 2, '0', STR_PAD_LEFT);
        if ($consultorio->medico) {
            $local .= ' — '.$consultorio->medico->nome;
        }

        OperadorSessao::setSenhaAtual($senha->id);
        OperadorSessao::setPainelAtual([
            'tipo' => OperadorSessao::MODO_CONSULTORIO,
            'codigo' => $senha->codigo,
            'servico' => $svc->nome,
            'local' => $local,
        ]);
        OperadorSessao::pushLog('call', "Chamou {$senha->codigo} — consultório {$consultorio->numero}");

        $this->timerSegundos = 0;
        OperadorSessao::setTimerSegundos(0);
        $this->invalidateOperadorComputeds();

        Flux::toast(variant: 'success', text: "Chamando {$senha->codigo}");
    }

    public function rechamarAtual(RechamarSenha $rechamar): void
    {
        $senhaId = OperadorSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->with('servico')->find($senhaId);
        $chamada = Chamada::query()
            ->with(['guiche', 'consultorio.medico'])
            ->where('senha_id', $senhaId)
            ->latest('chamada_em')
            ->first();

        if ($senha && $chamada) {
            $rechamar->execute($senha, $chamada);

            if ($chamada->consultorio_id && $chamada->consultorio) {
                $local = str_pad((string) $chamada->consultorio->numero, 2, '0', STR_PAD_LEFT);
                if ($chamada->consultorio->medico) {
                    $local .= ' — '.$chamada->consultorio->medico->nome;
                }
                OperadorSessao::setPainelAtual([
                    'tipo' => OperadorSessao::MODO_CONSULTORIO,
                    'codigo' => $senha->codigo,
                    'servico' => $senha->servico->nome,
                    'local' => $local,
                ]);
            } elseif ($chamada->guiche) {
                OperadorSessao::setPainelAtual([
                    'tipo' => OperadorSessao::MODO_GUICHE,
                    'codigo' => $senha->codigo,
                    'servico' => $senha->servico->nome,
                    'local' => str_pad((string) $chamada->guiche->numero, 2, '0', STR_PAD_LEFT),
                ]);
            }

            Flux::toast(text: "Rechamando {$senha->codigo}");
        }
    }

    public function finalizarAtendimento(FinalizarSenha $finalizar): void
    {
        $senhaId = OperadorSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        if ($senha) {
            $finalizar->execute($senha);
            $duracao = $this->segundosAtendimentoAtual();
            OperadorSessao::pushLog('finish', "Finalizou {$senha->codigo} em {$duracao}s");
            OperadorSessao::pushTempo($duracao);
        }

        OperadorSessao::setSenhaAtual(null);
        $this->timerSegundos = 0;
        OperadorSessao::setTimerSegundos(0);
        $this->invalidateOperadorComputeds();
        Flux::toast(variant: 'success', text: 'Atendimento finalizado.');
    }

    public function marcarAusente(MarcarSenhaAusente $marcar): void
    {
        $senhaId = OperadorSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        if ($senha) {
            $marcar->execute($senha);
            OperadorSessao::pushLog('absent', "Ausente: {$senha->codigo}");
        }

        OperadorSessao::setSenhaAtual(null);
        $this->timerSegundos = 0;
        OperadorSessao::setTimerSegundos(0);
        $this->invalidateOperadorComputeds();
        Flux::toast(variant: 'warning', text: 'Senha marcada como ausente.');
    }

    public function confirmarTransferencia(TransferirSenha $transferir): void
    {
        $senhaId = OperadorSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        if ($senha) {
            try {
                $transferir->execute($senha, (int) $this->transferServico);
                OperadorSessao::setSenhaAtual(null);
                $this->timerSegundos = 0;
                $this->showTransferModal = false;
                $this->invalidateOperadorComputeds();
                Flux::toast(variant: 'success', text: __('Fila alterada no guichê.'));
            } catch (FilaException $e) {
                Flux::toast(variant: 'danger', text: $e->getMessage());
            }
        }
    }

    public function clearLog(): void
    {
        OperadorSessao::clearLog();
    }

    public function trocarSenha(): void
    {
        $this->validate([
            'senhaAtual' => ['required', 'string'],
            'senhaNova' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        /** @var OperadorModel $operador */
        $operador = Auth::guard('operador')->user();

        if (! Hash::check($this->senhaAtual, $operador->password)) {
            $this->addError('senhaAtual', __('Senha atual incorreta.'));

            return;
        }

        $operador->update(['password' => $this->senhaNova]);

        $this->reset(['senhaAtual', 'senhaNova', 'senhaNovaConfirmation', 'showPasswordModal']);
        Flux::toast(variant: 'success', text: __('Senha alterada com sucesso.'));
    }

    #[Computed]
    public function operadorLogado(): OperadorModel
    {
        return Auth::guard('operador')->user();
    }

    #[Computed]
    public function senhaAtualModel(): ?Senha
    {
        return app(OperadorPainelQuery::class)->senhaAtual();
    }

    #[Computed]
    public function servicoAtualNome(): string
    {
        if (! $this->servico) {
            return $this->modo === OperadorSessao::MODO_GUICHE
                ? __('Todos da fila')
                : '—';
        }

        $servicos = $this->modo === OperadorSessao::MODO_GUICHE
            ? $this->servicosGuiche
            : $this->operadorPainel['servicos'];

        return $servicos->firstWhere('id', (int) $this->servico)?->nome ?? '—';
    }

    #[Computed]
    public function consultorioAtualLabel(): string
    {
        $c = $this->consultoriosDisponiveis->firstWhere('id', (int) $this->consultorio);
        if (! $c) {
            return '—';
        }

        return MedicoSessao::labelConsultorio($c);
    }

    #[Computed]
    public function servicosConsultorio(): Collection
    {
        if (! $this->consultorio) {
            return collect();
        }

        $consultorio = Consultorio::query()->find((int) $this->consultorio);
        if (! $consultorio) {
            return collect();
        }

        return app(OperadorPainelQuery::class)->servicosPermitidosConsultorio($consultorio);
    }

    #[Computed]
    public function filaFiltrada(): Collection
    {
        return match ($this->queueFilter) {
            'preferencial' => $this->filaAguardando->where('is_preferencial', true)->values(),
            'normal' => $this->filaAguardando->where('is_preferencial', false)->values(),
            'agendado' => $this->filaAguardando->where('is_agendado', true)->values(),
            default => $this->filaAguardando,
        };
    }

    #[Computed]
    public function temSenhaAtual(): bool
    {
        return $this->senhaAtualModel !== null;
    }

    #[Computed]
    public function timerFormatado(): string
    {
        $m = str_pad((string) intdiv($this->timerSegundos, 60), 2, '0', STR_PAD_LEFT);
        $s = str_pad((string) ($this->timerSegundos % 60), 2, '0', STR_PAD_LEFT);

        return "{$m}:{$s}";
    }

    #[Computed]
    public function intercalacaoBadge(): string
    {
        if (! $this->servico) {
            return '—';
        }

        $regra = app(OperadorPainelQuery::class)->regraIntercalacao((int) $this->servico);
        $normais = $regra?->normais_por_ciclo ?? 2;
        $preferenciais = $regra?->preferenciais_por_ciclo ?? 1;

        return "{$normais} normal : {$preferenciais} preferencial";
    }

    #[Computed]
    public function tMedio(): string
    {
        $tempos = OperadorSessao::tempos();
        if (count($tempos) === 0) {
            return '--';
        }

        return (string) (int) round(array_sum($tempos) / count($tempos)).'s';
    }

    protected function segundosAtendimentoAtual(): int
    {
        $senha = $this->senhaAtualModel;

        if ($senha?->chamada_em) {
            return (int) $senha->chamada_em->diffInSeconds(now());
        }

        return $this->timerSegundos;
    }

    protected function aplicarServicoPadraoGuiche(?Guiche $guiche, Collection $servicos): void
    {
        if ($guiche?->servico_padrao_id && $servicos->contains('id', $guiche->servico_padrao_id)) {
            $this->servico = (string) $guiche->servico_padrao_id;

            return;
        }

        $this->servico = '';
    }

    protected function migrarGuicheLegado(): void
    {
        if ($this->guicheId || ! $this->guiche) {
            return;
        }

        $guicheModel = $this->resolverGuichePorNumero($this->guiche);
        if ($guicheModel) {
            $this->guicheId = $guicheModel->id;
        }

        $this->guiche = null;
    }

    protected function resolverGuichePorNumero(int $numero): ?Guiche
    {
        $alaId = $this->guicheId
            ? Guiche::query()->whereKey($this->guicheId)->value('ala_id')
            : (OperadorSessao::guicheId()
                ? Guiche::query()->whereKey(OperadorSessao::guicheId())->value('ala_id')
                : null);

        if ($alaId) {
            $guiche = Guiche::query()
                ->where('ala_id', $alaId)
                ->where('numero', $numero)
                ->where('ativo', true)
                ->first();

            if ($guiche) {
                return $guiche;
            }
        }

        return Guiche::query()
            ->where('numero', $numero)
            ->where('ativo', true)
            ->orderBy('id')
            ->first();
    }

    protected function invalidateOperadorComputeds(): void
    {
        unset(
            $this->operadorPainel,
            $this->filaAguardando,
            $this->senhaAtualModel,
            $this->filaFiltrada,
            $this->temSenhaAtual,
            $this->alasAtivas,
            $this->alasConsultorio,
            $this->consultoriosEncaminhar,
            $this->servicosEncaminhar,
            $this->servicosTransferir,
            $this->servicosConsultorio,
            $this->servicosGuiche,
            $this->guicheAtualModel,
            $this->guichesDaAlaAtual,
        );
    }

    public function render()
    {
        return view('livewire.fila.operador');
    }
}

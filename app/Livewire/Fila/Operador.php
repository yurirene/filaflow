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
use App\Fila\OperadorSessao;
use App\Fila\Queries\OperadorPainelQuery;
use App\Models\Chamada;
use App\Models\Consultorio;
use App\Models\Guiche;
use App\Models\Senha;
use App\Models\Servico;
use App\Models\Operador as OperadorModel;
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

    public int $guiche = 3;

    public string $servico = '';

    public string $consultorio = '';

    public bool $showTransferModal = false;

    public bool $showEncaminharModal = false;

    public string $transferServico = '';

    public string $transferMotivo = '';

    public string $encAla = '';

    public string $encServico = '';

    public string $encConsultorio = '';

    public int $timerSegundos = 0;

    public function mount(): void
    {
        $this->modo = OperadorSessao::modo();
        $this->queueFilter = OperadorSessao::queueFilter();
        $this->guiche = OperadorSessao::guicheNumero();
        $this->timerSegundos = OperadorSessao::timerSegundos();

        $servicoId = OperadorSessao::servicoId()
            ?? Servico::query()->where('ativo', true)->orderBy('nome')->value('id');

        $this->servico = (string) ($servicoId ?? '');
        $this->transferServico = $this->servico;

        $consultorioId = OperadorSessao::consultorioId()
            ?? Consultorio::query()->where('ativo', true)->orderBy('numero')->value('id');

        $this->consultorio = (string) ($consultorioId ?? '');

        if ($this->modo === OperadorSessao::MODO_GUICHE && $servicoId && ! OperadorSessao::guicheId()) {
            OperadorSessao::setOperadorContext($this->guiche, $servicoId);
        }

        if ($this->modo === OperadorSessao::MODO_CONSULTORIO && $consultorioId) {
            OperadorSessao::setConsultorioContext($consultorioId, $servicoId ?: null);
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

        if (! $this->servico) {
            return collect();
        }

        return app(OperadorPainelQuery::class)->filaAguardandoGuiche((int) $this->servico);
    }

    #[Computed]
    public function guichesDaAlaAtual(): Collection
    {
        $servico = Servico::query()->find((int) $this->servico);

        return app(OperadorPainelQuery::class)->guichesDaAla($servico?->ala_id);
    }

    #[Computed]
    public function consultoriosDisponiveis(): Collection
    {
        return $this->operadorPainel['consultorios'];
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

    public function tickTimer(): void
    {
        if ($this->senhaAtualModel) {
            $this->timerSegundos++;
            OperadorSessao::setTimerSegundos($this->timerSegundos);
        }
    }

    public function alternarModo(string $modo): void
    {
        $this->modo = $modo;
        OperadorSessao::setModo($modo);

        if ($modo === OperadorSessao::MODO_GUICHE && $this->servico) {
            OperadorSessao::setOperadorContext($this->guiche, (int) $this->servico);
        }

        if ($modo === OperadorSessao::MODO_CONSULTORIO && $this->consultorio) {
            OperadorSessao::setConsultorioContext(
                (int) $this->consultorio,
                $this->servico ? (int) $this->servico : null,
            );
        }

        $this->invalidateOperadorComputeds();
    }

    public function updatedGuiche(int $guiche): void
    {
        if ($this->servico && $this->modo === OperadorSessao::MODO_GUICHE) {
            OperadorSessao::setOperadorContext($guiche, (int) $this->servico);
        }
        $this->invalidateOperadorComputeds();
    }

    public function updatedServico(string $servico): void
    {
        if ($this->modo === OperadorSessao::MODO_GUICHE) {
            $servicoModel = Servico::query()->find((int) $servico);
            $numeros = app(OperadorPainelQuery::class)->guichesDaAla($servicoModel?->ala_id)->pluck('numero');

            if ($numeros->isNotEmpty() && ! $numeros->contains($this->guiche)) {
                $this->guiche = $numeros->first();
            }

            OperadorSessao::setOperadorContext($this->guiche, (int) $servico);
            $this->transferServico = $servico;
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

        $senha->load('servico');
        $this->encAla = (string) $senha->servico->ala_id;
        $this->encServico = (string) $senha->servico_id;
        $consultorios = app(OperadorPainelQuery::class)->consultoriosDaAla($senha->servico->ala_id);
        $this->encConsultorio = (string) ($consultorios->first()?->id ?? '');
        $this->showEncaminharModal = true;
    }

    public function updatedEncAla(): void
    {
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

        $senha = Senha::query()->find($senhaId);
        if (! $senha) {
            return;
        }

        try {
            $encaminhar->execute($senha, (int) $this->encServico, (int) $this->encConsultorio);
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

            $guiche = Guiche::query()->where('numero', $this->guiche)->first()
                ?? throw FilaException::guicheInvalido();

            $resultado = $chamarGuiche->execute((int) $this->servico, $guiche->id);

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
        if ($consultorio->responsavel) {
            $local .= ' — '.$consultorio->responsavel;
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
        $chamada = Chamada::query()->where('senha_id', $senhaId)->latest('chamada_em')->first();

        if ($senha && $chamada) {
            $rechamar->execute($senha, $chamada);

            if ($chamada->consultorio_id && $chamada->consultorio) {
                $local = str_pad((string) $chamada->consultorio->numero, 2, '0', STR_PAD_LEFT);
                if ($chamada->consultorio->responsavel) {
                    $local .= ' — '.$chamada->consultorio->responsavel;
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
            OperadorSessao::pushLog('finish', "Finalizou {$senha->codigo} em {$this->timerSegundos}s");
            OperadorSessao::pushTempo($this->timerSegundos);
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
        return $this->operadorPainel['servicos']->firstWhere('id', (int) $this->servico)?->nome ?? '—';
    }

    #[Computed]
    public function consultorioAtualLabel(): string
    {
        $c = $this->consultoriosDisponiveis->firstWhere('id', (int) $this->consultorio);
        if (! $c) {
            return '—';
        }

        return __('Consultório :num', ['num' => str_pad((string) $c->numero, 2, '0', STR_PAD_LEFT)]).' — '.$c->responsavel;
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

    protected function invalidateOperadorComputeds(): void
    {
        unset(
            $this->operadorPainel,
            $this->filaAguardando,
            $this->senhaAtualModel,
            $this->filaFiltrada,
            $this->temSenhaAtual,
            $this->consultoriosEncaminhar,
            $this->servicosEncaminhar,
            $this->servicosConsultorio,
        );
    }

    public function render()
    {
        return view('livewire.fila.operador');
    }
}

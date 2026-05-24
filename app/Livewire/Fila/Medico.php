<?php

namespace App\Livewire\Fila;

use App\Fila\Actions\ChamarProximaConsultorio;
use App\Fila\Actions\FinalizarSenha;
use App\Fila\Actions\MarcarSenhaAusente;
use App\Fila\Actions\RechamarSenha;
use App\Fila\Exceptions\FilaException;
use App\Fila\MedicoSessao;
use App\Fila\OperadorSessao;
use App\Fila\Queries\MedicoPainelQuery;
use App\Fila\Queries\OperadorPainelQuery;
use App\Models\Chamada;
use App\Models\Consultorio;
use App\Models\Medico as MedicoModel;
use App\Models\Senha;
use App\Support\VerificadorSenha;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Médico')]
class Medico extends Component
{
    public bool $showPasswordModal = false;

    public string $senhaAtual = '';

    public string $senhaNova = '';

    public string $senhaNovaConfirmation = '';

    public string $queueFilter = 'all';

    public int $timerSegundos = 0;

    public function mount(): void
    {
        $consultorio = $this->consultorioVinculado;
        if (! $consultorio) {
            Auth::guard('medico')->logout();

            $this->redirect(route('medico.login'), navigate: true);

            return;
        }

        $this->queueFilter = MedicoSessao::queueFilter();
        $this->timerSegundos = MedicoSessao::timerSegundos();

        MedicoSessao::setContext($consultorio->id);
    }

    #[Computed]
    public function medicoLogado(): MedicoModel
    {
        return Auth::guard('medico')->user()->load('consultorio.ala');
    }

    #[Computed]
    public function consultorioVinculado(): ?Consultorio
    {
        $consultorio = $this->medicoLogado->consultorio;

        return ($consultorio && $consultorio->ativo) ? $consultorio : null;
    }

    #[Computed]
    public function medicoPainel(): array
    {
        $consultorio = $this->consultorioVinculado;
        if (! $consultorio) {
            return [];
        }

        return app(MedicoPainelQuery::class)->execute($consultorio);
    }

    #[Computed]
    public function filaAguardando(): Collection
    {
        $consultorio = $this->consultorioVinculado;
        if (! $consultorio) {
            return collect();
        }

        return app(OperadorPainelQuery::class)->filaAguardandoConsultorio($consultorio->id);
    }

    #[Computed]
    public function senhaAtualModel(): ?Senha
    {
        $consultorio = $this->consultorioVinculado;
        if (! $consultorio) {
            return null;
        }

        return app(MedicoPainelQuery::class)->senhaAtual($consultorio->id);
    }

    #[Computed]
    public function consultorioLabel(): string
    {
        return MedicoSessao::labelConsultorio($this->consultorioVinculado);
    }

    #[Computed]
    public function filaFiltrada(): Collection
    {
        return match ($this->queueFilter) {
            'preferencial' => $this->filaAguardando->where('is_preferencial', true)->values(),
            'normal' => $this->filaAguardando->where('is_preferencial', false)->values(),
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
    public function tMedio(): string
    {
        $tempos = MedicoSessao::tempos();
        if (count($tempos) === 0) {
            return '--';
        }

        return (string) (int) round(array_sum($tempos) / count($tempos)).'s';
    }

    public function onFilaAtualizada(): void
    {
        $this->invalidateMedicoComputeds();
    }

    public function filterQueue(string $filter): void
    {
        $this->queueFilter = $filter;
        MedicoSessao::setQueueFilter($filter);
    }

    public function chamarProxima(ChamarProximaConsultorio $chamar): void
    {
        $consultorio = $this->consultorioVinculado;
        if (! $consultorio) {
            return;
        }

        try {
            $resultado = $chamar->execute($consultorio->id);

            $senha = $resultado['senha'];
            $svc = $resultado['servico'];
            $consultorio = $resultado['consultorio'];

            $local = str_pad((string) $consultorio->numero, 2, '0', STR_PAD_LEFT);
            if ($consultorio->medico) {
                $local .= ' — '.$consultorio->medico->nome;
            }

            MedicoSessao::setSenhaAtual($senha->id);
            MedicoSessao::setPainelAtual([
                'tipo' => OperadorSessao::MODO_CONSULTORIO,
                'codigo' => $senha->codigo,
                'servico' => $svc->nome,
                'local' => $local,
            ]);
            MedicoSessao::pushLog('call', "Chamou {$senha->codigo} — consultório {$consultorio->numero}");

            $this->timerSegundos = 0;
            MedicoSessao::setTimerSegundos(0);
            $this->invalidateMedicoComputeds();

            Flux::toast(variant: 'success', text: "Chamando {$senha->codigo}");
        } catch (FilaException $e) {
            Flux::toast(variant: 'warning', text: $e->getMessage());
        }
    }

    public function rechamarAtual(RechamarSenha $rechamar): void
    {
        $senhaId = MedicoSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->with('servico')->find($senhaId);
        $chamada = Chamada::query()
            ->with('consultorio.medico')
            ->where('senha_id', $senhaId)
            ->latest('chamada_em')
            ->first();

        if ($senha && $chamada && $chamada->consultorio_id) {
            $rechamar->execute($senha, $chamada);

            if ($chamada->consultorio->medico) {
                $local = str_pad((string) $chamada->consultorio->numero, 2, '0', STR_PAD_LEFT);
                $local .= ' — '.$chamada->consultorio->medico->nome;
                MedicoSessao::setPainelAtual([
                    'tipo' => OperadorSessao::MODO_CONSULTORIO,
                    'codigo' => $senha->codigo,
                    'servico' => $senha->servico->nome,
                    'local' => $local,
                ]);
            }

            Flux::toast(text: "Rechamando {$senha->codigo}");
        }
    }

    public function finalizarAtendimento(FinalizarSenha $finalizar): void
    {
        $senhaId = MedicoSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        if ($senha) {
            $finalizar->execute($senha);
            $duracao = $this->segundosAtendimentoAtual();
            MedicoSessao::pushLog('finish', "Finalizou {$senha->codigo} em {$duracao}s");
            MedicoSessao::pushTempo($duracao);
        }

        MedicoSessao::setSenhaAtual(null);
        $this->timerSegundos = 0;
        MedicoSessao::setTimerSegundos(0);
        $this->invalidateMedicoComputeds();
        Flux::toast(variant: 'success', text: __('Atendimento finalizado.'));
    }

    public function marcarAusente(MarcarSenhaAusente $marcar): void
    {
        $senhaId = MedicoSessao::senhaAtualId();
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        if ($senha) {
            $marcar->execute($senha);
            MedicoSessao::pushLog('absent', "Ausente: {$senha->codigo}");
        }

        MedicoSessao::setSenhaAtual(null);
        $this->timerSegundos = 0;
        MedicoSessao::setTimerSegundos(0);
        $this->invalidateMedicoComputeds();
        Flux::toast(variant: 'warning', text: __('Senha marcada como ausente.'));
    }

    public function clearLog(): void
    {
        MedicoSessao::clearLog();
    }

    public function trocarSenha(): void
    {
        $this->validate([
            'senhaAtual' => ['required', 'string'],
            'senhaNova' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        /** @var MedicoModel $medico */
        $medico = Auth::guard('medico')->user();

        if (! VerificadorSenha::validar($medico, $this->senhaAtual)) {
            $this->addError('senhaAtual', __('Senha atual incorreta.'));

            return;
        }

        $medico->update(['password' => $this->senhaNova]);

        $this->reset(['senhaAtual', 'senhaNova', 'senhaNovaConfirmation', 'showPasswordModal']);
        Flux::toast(variant: 'success', text: __('Senha alterada com sucesso.'));
    }

    protected function segundosAtendimentoAtual(): int
    {
        $senha = $this->senhaAtualModel;

        if ($senha?->chamada_em) {
            return (int) $senha->chamada_em->diffInSeconds(now());
        }

        return $this->timerSegundos;
    }

    protected function invalidateMedicoComputeds(): void
    {
        unset(
            $this->medicoPainel,
            $this->filaAguardando,
            $this->senhaAtualModel,
            $this->filaFiltrada,
            $this->temSenhaAtual,
        );
    }

    public function render()
    {
        return view('livewire.fila.medico');
    }
}

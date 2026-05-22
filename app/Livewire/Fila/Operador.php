<?php

namespace App\Livewire\Fila;

use App\Fila\Actions\ChamarProximaSenha;
use App\Fila\Actions\FinalizarSenha;
use App\Fila\Actions\MarcarSenhaAusente;
use App\Fila\Actions\RechamarSenha;
use App\Fila\Actions\TransferirSenha;
use App\Fila\Exceptions\FilaException;
use App\Fila\OperadorSessao;
use App\Livewire\Concerns\InteractsWithFilaState;
use App\Models\Chamada;
use App\Models\Guiche;
use App\Models\Senha;
use App\Support\FilaState;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Operador')]
class Operador extends Component
{
    use InteractsWithFilaState;

    public string $queueFilter = 'all';

    public int $guiche = 3;

    public string $servico = '';

    public bool $showTransferModal = false;

    public string $transferServico = '';

    public string $transferMotivo = '';

    public int $timerSegundos = 0;

    public function mount(): void
    {
        $this->bootFilaState();
        $state = $this->filaState;
        $this->queueFilter = $state['queueFilter'];
        $this->guiche = $state['operador']['guiche'];
        $this->servico = $state['operador']['servico'];
        $this->transferServico = $this->servico;
        $this->timerSegundos = $state['timerSegundos'];
    }

    public function tickTimer(): void
    {
        if ($this->filaState['senhaAtual']) {
            $this->timerSegundos++;
            FilaState::set(['timerSegundos' => $this->timerSegundos]);
        }
    }

    public function updatedGuiche(int $guiche): void
    {
        FilaState::set(['operador' => ['guiche' => $guiche, 'servico' => $this->servico]]);
        unset($this->filaState);
    }

    public function updatedServico(string $servico): void
    {
        FilaState::set(['operador' => ['guiche' => $this->guiche, 'servico' => $servico]]);
        $this->transferServico = $servico;
        unset($this->filaState);
    }

    public function filterQueue(string $filter): void
    {
        $this->queueFilter = $filter;
        FilaState::set(['queueFilter' => $filter]);
    }

    public function chamarProxima(ChamarProximaSenha $chamar): void
    {
        try {
            $guiche = Guiche::query()->where('numero', $this->guiche)->first()
                ?? throw FilaException::guicheInvalido();

            $resultado = $chamar->execute($this->servico, $guiche->id);

            $senha = $resultado['senha'];
            $svc = $resultado['servico'];

            OperadorSessao::setSenhaAtual($senha->id);
            OperadorSessao::setPainelAtual([
                'codigo' => $senha->codigo,
                'servico' => $svc->nome,
                'guiche' => str_pad((string) $guiche->numero, 2, '0', STR_PAD_LEFT),
            ]);
            OperadorSessao::pushLog('call', "Chamou {$senha->codigo} — {$svc->nome}");

            $this->timerSegundos = 0;
            FilaState::set(['timerSegundos' => 0]);
            unset($this->filaState);

            Flux::toast(variant: 'success', text: "Chamando {$senha->codigo}");
        } catch (FilaException $e) {
            Flux::toast(variant: 'warning', text: $e->getMessage());
        }
    }

    public function rechamarAtual(RechamarSenha $rechamar): void
    {
        $senhaId = session(FilaState::SESSION_KEY)['senha_atual_id'] ?? null;
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        $chamada = Chamada::query()->where('senha_id', $senhaId)->latest('chamada_em')->first();

        if ($senha && $chamada) {
            $rechamar->execute($senha, $chamada);
            $guiche = $chamada->guiche;
            OperadorSessao::setPainelAtual([
                'codigo' => $senha->codigo,
                'servico' => $senha->servico->nome,
                'guiche' => str_pad((string) $guiche->numero, 2, '0', STR_PAD_LEFT),
            ]);
            Flux::toast(text: "Rechamando {$senha->codigo}");
        }
    }

    public function finalizarAtendimento(FinalizarSenha $finalizar): void
    {
        $senhaId = session(FilaState::SESSION_KEY)['senha_atual_id'] ?? null;
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
        FilaState::set(['timerSegundos' => 0]);
        unset($this->filaState);
        Flux::toast(variant: 'success', text: 'Atendimento finalizado.');
    }

    public function marcarAusente(MarcarSenhaAusente $marcar): void
    {
        $senhaId = session(FilaState::SESSION_KEY)['senha_atual_id'] ?? null;
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
        FilaState::set(['timerSegundos' => 0]);
        unset($this->filaState);
        Flux::toast(variant: 'warning', text: 'Senha marcada como ausente.');
    }

    public function confirmarTransferencia(TransferirSenha $transferir): void
    {
        $senhaId = session(FilaState::SESSION_KEY)['senha_atual_id'] ?? null;
        if (! $senhaId) {
            return;
        }

        $senha = Senha::query()->find($senhaId);
        if ($senha) {
            try {
                $transferir->execute($senha, $this->transferServico);
                OperadorSessao::setSenhaAtual(null);
                $this->timerSegundos = 0;
                $this->showTransferModal = false;
                unset($this->filaState);
                Flux::toast(variant: 'success', text: 'Senha transferida.');
            } catch (FilaException $e) {
                Flux::toast(variant: 'danger', text: $e->getMessage());
            }
        }
    }

    public function clearLog(): void
    {
        FilaState::set(['log' => []]);
        unset($this->filaState);
    }

    #[Computed]
    public function filaFiltrada(): array
    {
        $state = $this->filaState;
        $fila = $state['filas'][$this->servico] ?? [];

        return match ($this->queueFilter) {
            'preferencial' => array_values(array_filter($fila, fn ($s) => $s['isPreferencial'])),
            'normal' => array_values(array_filter($fila, fn ($s) => ! $s['isPreferencial'])),
            'agendado' => array_values(array_filter($fila, fn ($s) => $s['agendado'] ?? false)),
            default => $fila,
        };
    }

    #[Computed]
    public function temSenhaAtual(): bool
    {
        return $this->filaState['senhaAtual'] !== null;
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
        $ic = $this->filaState['intercalacao'][$this->servico] ?? ['normais' => 2, 'preferenciais' => 1];

        return "{$ic['normais']} normal : {$ic['preferenciais']} preferencial";
    }

    #[Computed]
    public function tMedio(): string
    {
        $tempos = $this->filaState['stats']['tempos'];
        if (count($tempos) === 0) {
            return '--';
        }

        return (string) (int) round(array_sum($tempos) / count($tempos)).'s';
    }

    public function render()
    {
        return view('livewire.fila.operador');
    }
}

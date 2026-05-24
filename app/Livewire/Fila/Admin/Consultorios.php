<?php

namespace App\Livewire\Fila\Admin;

use App\Fila\Enums\StatusOperador;
use App\Models\Ala;
use App\Models\Consultorio;
use App\Models\Medico;
use App\Models\Servico;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Consultórios')]
class Consultorios extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public int $numero = 1;

    public ?int $medicoId = null;

    public ?int $alaId = null;

    public bool $ativo = true;

    /** @var list<int> */
    public array $servicosSelecionados = [];

    #[Computed]
    public function alasConsultorio()
    {
        return Ala::query()->ativa()->consultorio()->orderBy('nome')->get();
    }

    #[Computed]
    public function consultorios()
    {
        return Consultorio::query()->with(['ala', 'medico', 'servicos'])->orderBy('ala_id')->orderBy('numero')->get();
    }

    #[Computed]
    public function servicosAtivos()
    {
        return Servico::query()->where('ativo', true)->orderBy('nome')->get();
    }

    #[Computed]
    public function medicosDisponiveis()
    {
        return Medico::query()
            ->where('status', StatusOperador::Ativo)
            ->where(function ($q) {
                $q->whereDoesntHave('consultorio');
                if ($this->editingId) {
                    $q->orWhereRelation('consultorio', 'id', $this->editingId);
                }
            })
            ->orderBy('nome')
            ->get();
    }

    public function mount(): void
    {
        $this->alaId = Ala::query()->ativa()->consultorio()->orderBy('nome')->value('id');
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->numero = (Consultorio::query()->where('ala_id', $this->alaId)->max('numero') ?? 0) + 1;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $consultorio = Consultorio::query()->with('servicos')->findOrFail($id);

        $this->editingId = $consultorio->id;
        $this->numero = $consultorio->numero;
        $this->medicoId = $consultorio->medico_id;
        $this->alaId = $consultorio->ala_id;
        $this->ativo = $consultorio->ativo;
        $this->servicosSelecionados = $consultorio->servicos->pluck('id')->all();
        $this->showModal = true;
    }

    public function salvar(): void
    {
        $this->validate([
            'numero' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('consultorios', 'numero')
                    ->where('ala_id', $this->alaId)
                    ->ignore($this->editingId),
            ],
            'alaId' => 'required|exists:alas,id',
            'medicoId' => [
                'required',
                'exists:medicos,id',
                Rule::unique('consultorios', 'medico_id')->ignore($this->editingId),
            ],
            'ativo' => 'boolean',
            'servicosSelecionados' => 'array',
            'servicosSelecionados.*' => 'exists:servicos,id',
        ], [], [
            'medicoId' => __('médico'),
        ]);

        $ala = Ala::query()->find($this->alaId);
        if (! $ala?->is_consultorio) {
            $this->addError('alaId', __('Consultórios só podem ser cadastrados em alas de consultório.'));

            return;
        }

        $medico = Medico::query()->find($this->medicoId);
        if (! $medico?->isAtivo()) {
            $this->addError('medicoId', __('O médico selecionado está inativo.'));

            return;
        }

        $dados = [
            'ala_id' => $this->alaId,
            'medico_id' => $this->medicoId,
            'numero' => $this->numero,
            'ativo' => $this->ativo,
        ];

        if ($this->editingId) {
            $consultorio = Consultorio::query()->findOrFail($this->editingId);
            $consultorio->update($dados);
            $consultorio->servicos()->sync($this->servicosSelecionados);
            Flux::toast(variant: 'success', text: __('Consultório atualizado.'));
        } else {
            $consultorio = Consultorio::query()->create($dados);
            if ($this->servicosSelecionados !== []) {
                $consultorio->servicos()->sync($this->servicosSelecionados);
            }
            Flux::toast(variant: 'success', text: __('Consultório adicionado.'));
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->consultorios, $this->medicosDisponiveis);
    }

    public function excluir(int $id): void
    {
        Consultorio::query()->whereKey($id)->delete();
        unset($this->consultorios, $this->medicosDisponiveis);
        Flux::toast(variant: 'success', text: __('Consultório excluído.'));
    }

    public function alternarStatus(int $id): void
    {
        $consultorio = Consultorio::query()->findOrFail($id);
        $consultorio->update(['ativo' => ! $consultorio->ativo]);
        unset($this->consultorios);
        Flux::toast(variant: 'success', text: __('Status atualizado.'));
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'medicoId', 'servicosSelecionados']);
        $this->ativo = true;
    }

    public function render()
    {
        return view('livewire.fila.admin.consultorios');
    }
}

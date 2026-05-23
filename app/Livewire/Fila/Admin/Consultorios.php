<?php

namespace App\Livewire\Fila\Admin;

use App\Models\Ala;
use App\Models\Consultorio;
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

    public string $responsavel = '';

    public ?int $alaId = null;

    public bool $ativo = true;

    /** @var list<int> */
    public array $servicosSelecionados = [];

    #[Computed]
    public function alas()
    {
        return Ala::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function consultorios()
    {
        return Consultorio::query()->with(['ala', 'servicos'])->orderBy('ala_id')->orderBy('numero')->get();
    }

    #[Computed]
    public function servicosDaAla()
    {
        if (! $this->alaId) {
            return collect();
        }

        return Servico::query()
            ->where('ala_id', $this->alaId)
            ->orderBy('nome')
            ->get();
    }

    public function mount(): void
    {
        $this->alaId = Ala::query()->where('ativo', true)->orderBy('nome')->value('id');
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
        $this->responsavel = $consultorio->responsavel;
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
            'responsavel' => 'required|string|max:150',
            'ativo' => 'boolean',
            'servicosSelecionados' => 'array',
            'servicosSelecionados.*' => 'exists:servicos,id',
        ]);

        foreach ($this->servicosSelecionados as $servicoId) {
            $servico = Servico::query()->find($servicoId);
            if ($servico?->ala_id !== $this->alaId) {
                $this->addError('servicosSelecionados', __('Todos os serviços devem pertencer à mesma ala do consultório.'));

                return;
            }
        }

        $dados = [
            'ala_id' => $this->alaId,
            'numero' => $this->numero,
            'responsavel' => $this->responsavel,
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
        unset($this->consultorios);
    }

    public function excluir(int $id): void
    {
        Consultorio::query()->whereKey($id)->delete();
        unset($this->consultorios);
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
        $this->reset(['editingId', 'responsavel', 'servicosSelecionados']);
        $this->ativo = true;
    }

    public function render()
    {
        return view('livewire.fila.admin.consultorios');
    }
}

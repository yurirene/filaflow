<?php

namespace App\Livewire\Fila\Admin;

use App\Models\Ala;
use App\Models\Guiche;
use App\Models\Servico;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Guichês')]
class Guiches extends Component
{
    public bool $showGuicheModal = false;

    public ?int $editingId = null;

    public int $guicheNum = 1;

    public string $guicheDesc = '';

    public ?int $guicheAlaId = null;

    public ?int $guicheServico = null;

    public bool $guicheAtivo = true;

    #[Computed]
    public function alas()
    {
        return Ala::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function guiches()
    {
        return Guiche::query()->with(['ala', 'servicoPadrao'])->orderBy('ala_id')->orderBy('numero')->get();
    }

    #[Computed]
    public function servicosDaAla()
    {
        if (! $this->guicheAlaId) {
            return collect();
        }

        return Servico::query()
            ->where('ala_id', $this->guicheAlaId)
            ->orderBy('nome')
            ->get();
    }

    public function mount(): void
    {
        $this->guicheAlaId = Ala::query()->where('ativo', true)->orderBy('nome')->value('id');
    }

    public function updatedGuicheAlaId(): void
    {
        if (! $this->servicosDaAla->contains('id', $this->guicheServico)) {
            $this->guicheServico = $this->servicosDaAla->first()?->id;
        }
    }

    public function openGuicheModal(): void
    {
        $this->resetForm();
        $this->guicheNum = (Guiche::query()->where('ala_id', $this->guicheAlaId)->max('numero') ?? 0) + 1;
        $this->showGuicheModal = true;
    }

    public function openEditModal(int $id): void
    {
        $guiche = Guiche::query()->findOrFail($id);

        $this->editingId = $guiche->id;
        $this->guicheNum = $guiche->numero;
        $this->guicheDesc = $guiche->descricao ?? '';
        $this->guicheAlaId = $guiche->ala_id;
        $this->guicheServico = $guiche->servico_padrao_id;
        $this->guicheAtivo = $guiche->ativo;
        $this->showGuicheModal = true;
    }

    public function salvarGuiche(): void
    {
        $this->validate([
            'guicheNum' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('guiches', 'numero')
                    ->where('ala_id', $this->guicheAlaId)
                    ->ignore($this->editingId),
            ],
            'guicheAlaId' => 'required|exists:alas,id',
            'guicheServico' => 'nullable|exists:servicos,id',
            'guicheAtivo' => 'boolean',
        ]);

        if ($this->guicheServico) {
            $servico = Servico::query()->find($this->guicheServico);
            if ($servico?->ala_id !== $this->guicheAlaId) {
                $this->addError('guicheServico', __('O serviço padrão deve pertencer à mesma ala do guichê.'));

                return;
            }
        }

        $dados = [
            'numero' => $this->guicheNum,
            'descricao' => $this->guicheDesc ?: __('Guichê :num', ['num' => $this->guicheNum]),
            'ala_id' => $this->guicheAlaId,
            'servico_padrao_id' => $this->guicheServico ?: null,
            'ativo' => $this->guicheAtivo,
        ];

        if ($this->editingId) {
            Guiche::query()->whereKey($this->editingId)->update($dados);
            Flux::toast(variant: 'success', text: __('Guichê atualizado.'));
        } else {
            Guiche::query()->create($dados);
            Flux::toast(variant: 'success', text: __('Guichê adicionado.'));
        }

        $this->showGuicheModal = false;
        $this->resetForm();
        unset($this->guiches);
    }

    public function excluir(int $id): void
    {
        Guiche::query()->whereKey($id)->delete();
        unset($this->guiches);
        Flux::toast(variant: 'success', text: __('Guichê excluído.'));
    }

    public function alternarStatus(int $id): void
    {
        $guiche = Guiche::query()->findOrFail($id);
        $guiche->update(['ativo' => ! $guiche->ativo]);
        unset($this->guiches);
        Flux::toast(variant: 'success', text: __('Status atualizado.'));
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'guicheDesc', 'guicheServico']);
        $this->guicheAtivo = true;
    }

    public function render()
    {
        return view('livewire.fila.admin.guiches');
    }
}

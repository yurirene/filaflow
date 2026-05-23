<?php

namespace App\Livewire\Fila\Admin;

use App\Models\Ala;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Alas / Setores')]
class Alas extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nome = '';

    public bool $ativo = true;

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $ala = Ala::query()->findOrFail($id);

        $this->editingId = $ala->id;
        $this->nome = $ala->nome;
        $this->ativo = $ala->ativo;
        $this->showModal = true;
    }

    public function salvar(): void
    {
        $this->validate([
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('alas', 'nome')->ignore($this->editingId),
            ],
            'ativo' => ['boolean'],
        ]);

        $dados = [
            'nome' => $this->nome,
            'ativo' => $this->ativo,
        ];

        if ($this->editingId) {
            Ala::query()->whereKey($this->editingId)->update($dados);
            Flux::toast(variant: 'success', text: __('Ala atualizada.'));
        } else {
            Ala::query()->create($dados);
            Flux::toast(variant: 'success', text: __('Ala cadastrada.'));
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function excluir(int $id): void
    {
        $ala = Ala::query()->withCount(['servicos', 'guiches', 'consultorios'])->findOrFail($id);

        if ($ala->servicos_count > 0 || $ala->guiches_count > 0 || $ala->consultorios_count > 0) {
            Flux::toast(variant: 'warning', text: __('Não é possível excluir: há serviços, guichês ou consultórios vinculados.'));

            return;
        }

        $ala->delete();
        Flux::toast(variant: 'success', text: __('Ala excluída.'));
    }

    public function alternarStatus(int $id): void
    {
        $ala = Ala::query()->findOrFail($id);
        $ala->update(['ativo' => ! $ala->ativo]);
        Flux::toast(variant: 'success', text: __('Status atualizado.'));
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'nome', 'ativo']);
        $this->ativo = true;
    }

    public function render()
    {
        return view('livewire.fila.admin.alas', [
            'alas' => Ala::query()->orderBy('nome')->get(),
        ]);
    }
}

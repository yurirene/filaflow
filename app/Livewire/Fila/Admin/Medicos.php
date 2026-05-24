<?php

namespace App\Livewire\Fila\Admin;

use App\Fila\Enums\StatusOperador;
use App\Models\Medico;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Médicos')]
class Medicos extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nome = '';

    public string $cpf = '';

    public string $password = '';

    public string $status = 'ativo';

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $medico = Medico::query()->with('consultorio')->findOrFail($id);

        $this->editingId = $medico->id;
        $this->nome = $medico->nome;
        $this->cpf = $medico->cpfFormatado();
        $this->password = '';
        $this->status = $medico->status->value;
        $this->showModal = true;
    }

    public function salvar(): void
    {
        $cpf = Medico::normalizarCpf($this->cpf);

        $rules = [
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'cpf' => ['required', 'string', Rule::unique('medicos', 'cpf')->ignore($this->editingId)],
            'status' => ['required', Rule::enum(StatusOperador::class)],
        ];

        if ($this->editingId) {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        } else {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $this->validate($rules, [], [
            'nome' => __('nome'),
            'cpf' => __('CPF'),
            'password' => __('senha'),
            'status' => __('status'),
        ]);

        if (strlen($cpf) !== 11) {
            $this->addError('cpf', __('CPF inválido.'));

            return;
        }

        $dados = [
            'nome' => $this->nome,
            'cpf' => $cpf,
            'status' => StatusOperador::from($this->status),
        ];

        if ($this->password !== '') {
            $dados['password'] = $this->password;
        }

        if ($this->editingId) {
            Medico::query()->whereKey($this->editingId)->update($dados);
            Flux::toast(variant: 'success', text: __('Médico atualizado.'));
        } else {
            Medico::query()->create($dados);
            Flux::toast(variant: 'success', text: __('Médico criado.'));
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function excluir(int $id): void
    {
        $medico = Medico::query()->with('consultorio')->findOrFail($id);

        if ($medico->consultorio) {
            Flux::toast(variant: 'warning', text: __('Desvincule o médico do consultório antes de excluir.'));

            return;
        }

        $medico->delete();
        Flux::toast(variant: 'success', text: __('Médico excluído.'));
    }

    public function alternarStatus(int $id): void
    {
        $medico = Medico::query()->findOrFail($id);
        $medico->update([
            'status' => $medico->isAtivo() ? StatusOperador::Inativo : StatusOperador::Ativo,
        ]);

        Flux::toast(variant: 'success', text: __('Status atualizado.'));
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'nome', 'cpf', 'password', 'status']);
        $this->status = StatusOperador::Ativo->value;
    }

    public function render()
    {
        return view('livewire.fila.admin.medicos', [
            'medicos' => Medico::query()->with('consultorio.ala')->orderBy('nome')->get(),
        ]);
    }
}

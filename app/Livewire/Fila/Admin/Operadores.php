<?php

namespace App\Livewire\Fila\Admin;

use App\Fila\Enums\StatusOperador;
use App\Models\Operador;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Operadores')]
class Operadores extends Component
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
        $operador = Operador::query()->findOrFail($id);

        $this->editingId = $operador->id;
        $this->nome = $operador->nome;
        $this->cpf = $operador->cpfFormatado();
        $this->password = '';
        $this->status = $operador->status->value;
        $this->showModal = true;
    }

    public function salvar(): void
    {
        $cpf = Operador::normalizarCpf($this->cpf);

        $rules = [
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'cpf' => ['required', 'string', Rule::unique('operadores', 'cpf')->ignore($this->editingId)],
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
            Operador::query()->whereKey($this->editingId)->update($dados);
            Flux::toast(variant: 'success', text: __('Operador atualizado.'));
        } else {
            Operador::query()->create($dados);
            Flux::toast(variant: 'success', text: __('Operador criado.'));
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function excluir(int $id): void
    {
        Operador::query()->whereKey($id)->delete();
        Flux::toast(variant: 'success', text: __('Operador excluído.'));
    }

    public function alternarStatus(int $id): void
    {
        $operador = Operador::query()->findOrFail($id);
        $operador->update([
            'status' => $operador->isAtivo() ? StatusOperador::Inativo : StatusOperador::Ativo,
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
        return view('livewire.fila.admin.operadores', [
            'operadores' => Operador::query()->orderBy('nome')->get(),
        ]);
    }
}

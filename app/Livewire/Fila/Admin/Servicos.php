<?php

namespace App\Livewire\Fila\Admin;

use App\Models\Ala;
use App\Models\RegraIntercalacao;
use App\Models\Servico;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Serviços')]
class Servicos extends Component
{
    public bool $showServiceModal = false;

    public ?int $editingId = null;

    public string $svcNome = '';

    public string $svcPrefixo = '';

    public ?int $svcAlaId = null;

    public string $svcCor = '#2563eb';

    public bool $svcAtivo = true;

    #[Computed]
    public function alas()
    {
        return Ala::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function servicos()
    {
        return Servico::query()->with('ala')->orderBy('nome')->get();
    }

    public function openServiceModal(): void
    {
        $this->resetForm();
        $this->svcAlaId = $this->alas->firstWhere('ativo', true)?->id;
        $this->showServiceModal = true;
    }

    public function openEditModal(int $id): void
    {
        $servico = Servico::query()->findOrFail($id);

        $this->editingId = $servico->id;
        $this->svcNome = $servico->nome;
        $this->svcPrefixo = $servico->prefixo;
        $this->svcAlaId = $servico->ala_id;
        $this->svcCor = $servico->cor;
        $this->svcAtivo = $servico->ativo;
        $this->showServiceModal = true;
    }

    public function salvarServico(): void
    {
        $this->validate([
            'svcNome' => 'required|min:2',
            'svcPrefixo' => [
                'required',
                'max:2',
                Rule::unique('servicos', 'prefixo')->ignore($this->editingId),
            ],
            'svcAlaId' => 'required|exists:alas,id',
            'svcAtivo' => 'boolean',
        ]);

        $dados = [
            'nome' => $this->svcNome,
            'prefixo' => strtoupper($this->svcPrefixo),
            'ala_id' => $this->svcAlaId,
            'cor' => $this->svcCor,
            'ativo' => $this->svcAtivo,
        ];

        if ($this->editingId) {
            Servico::query()->whereKey($this->editingId)->update($dados);
            Flux::toast(variant: 'success', text: __('Serviço atualizado.'));
        } else {
            $servico = Servico::query()->create(array_merge($dados, ['icone' => '🏥']));

            RegraIntercalacao::query()->create([
                'servico_id' => $servico->id,
                'normais_por_ciclo' => 2,
                'preferenciais_por_ciclo' => 1,
            ]);

            Flux::toast(variant: 'success', text: __('Serviço adicionado.'));
        }

        $this->showServiceModal = false;
        $this->resetForm();
        unset($this->servicos);
    }

    public function excluir(int $id): void
    {
        Servico::query()->whereKey($id)->delete();
        unset($this->servicos);
        Flux::toast(variant: 'success', text: __('Serviço excluído.'));
    }

    public function alternarStatus(int $id): void
    {
        $servico = Servico::query()->findOrFail($id);
        $servico->update(['ativo' => ! $servico->ativo]);
        unset($this->servicos);
        Flux::toast(variant: 'success', text: __('Status atualizado.'));
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'svcNome', 'svcPrefixo', 'svcAlaId']);
        $this->svcCor = '#2563eb';
        $this->svcAtivo = true;
    }

    public function render()
    {
        return view('livewire.fila.admin.servicos');
    }
}

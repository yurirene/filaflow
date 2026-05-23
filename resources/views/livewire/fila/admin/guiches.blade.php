<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Gerenciar guichês')" class="max-w-5xl">
        <flux:text class="mb-4 text-sm text-zinc-500">
            {{ __('Ponto de recepção ou triagem na ala. A fila do guichê contém senhas ainda não encaminhadas a um consultório.') }}
        </flux:text>
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openGuicheModal" :disabled="$this->alas->isEmpty()">
                {{ __('Novo guichê') }}
            </flux:button>
        </div>
        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-3 font-medium">{{ __('Número') }}</th>
                            <th class="pb-3 font-medium">{{ __('Descrição') }}</th>
                            <th class="pb-3 font-medium">{{ __('Ala / setor') }}</th>
                            <th class="pb-3 font-medium">{{ __('Serviço padrão') }}</th>
                            <th class="pb-3 font-medium">{{ __('Status') }}</th>
                            <th class="pb-3 font-medium text-end">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->guiches as $g)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="guiche-{{ $g->id }}">
                                <td class="py-3 font-mono">{{ str_pad((string) $g->numero, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3">{{ $g->descricao }}</td>
                                <td class="py-3">{{ $g->ala?->nome ?? '—' }}</td>
                                <td class="py-3">{{ $g->servicoPadrao?->nome ?? '—' }}</td>
                                <td class="py-3">
                                    <flux:badge :color="$g->ativo ? 'green' : 'zinc'">
                                        {{ $g->ativo ? __('Ativo') : __('Inativo') }}
                                    </flux:badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="alternarStatus({{ $g->id }})">
                                            {{ $g->ativo ? __('Desativar') : __('Ativar') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $g->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="excluir({{ $g->id }})"
                                            wire:confirm="{{ __('Excluir este guichê?') }}"
                                        >
                                            {{ __('Excluir') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-500">
                                    {{ __('Nenhum guichê cadastrado.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showGuicheModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingId ? __('Editar guichê') : __('Novo guichê') }}</flux:heading>
        <form wire:submit="salvarGuiche" class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Ala / setor') }}</flux:label>
                <flux:select wire:model.live="guicheAlaId">
                    @foreach ($this->alas->where('ativo', true) as $ala)
                        <flux:select.option value="{{ $ala->id }}">{{ $ala->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Número') }}</flux:label>
                <flux:input type="number" wire:model="guicheNum" min="1" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Descrição') }}</flux:label>
                <flux:input wire:model="guicheDesc" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Serviço padrão') }}</flux:label>
                <flux:select wire:model="guicheServico">
                    <flux:select.option value="">{{ __('Nenhum') }}</flux:select.option>
                    @foreach ($this->servicosDaAla as $svc)
                        <flux:select.option value="{{ $svc->id }}">{{ $svc->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:checkbox wire:model="guicheAtivo" :label="__('Ativo')" />
            </flux:field>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showGuicheModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

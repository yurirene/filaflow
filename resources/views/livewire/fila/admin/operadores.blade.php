<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Operadores')" class="max-w-5xl">
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openCreateModal">{{ __('Novo operador') }}</flux:button>
        </div>

        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-3 font-medium">{{ __('Nome') }}</th>
                            <th class="pb-3 font-medium">{{ __('CPF') }}</th>
                            <th class="pb-3 font-medium">{{ __('Status') }}</th>
                            <th class="pb-3 font-medium text-end">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($operadores as $operador)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="op-{{ $operador->id }}">
                                <td class="py-3">{{ $operador->nome }}</td>
                                <td class="py-3 font-mono">{{ $operador->cpfFormatado() }}</td>
                                <td class="py-3">
                                    <flux:badge :color="$operador->isAtivo() ? 'green' : 'zinc'">
                                        {{ $operador->status->label() }}
                                    </flux:badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="alternarStatus('{{ $operador->id }}')">
                                            {{ $operador->isAtivo() ? __('Desativar') : __('Ativar') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="openEditModal('{{ $operador->id }}')">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="excluir('{{ $operador->id }}')"
                                            wire:confirm="{{ __('Excluir este operador?') }}"
                                        >
                                            {{ __('Excluir') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-zinc-500">{{ __('Nenhum operador cadastrado.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingId ? __('Editar operador') : __('Novo operador') }}</flux:heading>
        <form wire:submit="salvar" class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Nome') }}</flux:label>
                <flux:input wire:model="nome" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('CPF') }}</flux:label>
                <flux:input wire:model="cpf" placeholder="000.000.000-00" />
            </flux:field>
            <flux:field>
                <flux:label>{{ $editingId ? __('Nova senha (opcional)') : __('Senha') }}</flux:label>
                <flux:input type="password" wire:model="password" viewable />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status">
                    <flux:select.option value="ativo">{{ __('Ativo') }}</flux:select.option>
                    <flux:select.option value="inativo">{{ __('Inativo') }}</flux:select.option>
                </flux:select>
            </flux:field>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

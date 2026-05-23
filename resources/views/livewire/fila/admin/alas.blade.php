<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Alas / setores')" class="max-w-4xl">
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openCreateModal">{{ __('Nova ala') }}</flux:button>
        </div>

        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-3 font-medium">{{ __('Nome') }}</th>
                            <th class="pb-3 font-medium">{{ __('Status') }}</th>
                            <th class="pb-3 font-medium text-end">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alas as $ala)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="ala-{{ $ala->id }}">
                                <td class="py-3">{{ $ala->nome }}</td>
                                <td class="py-3">
                                    <flux:badge :color="$ala->ativo ? 'green' : 'zinc'">
                                        {{ $ala->ativo ? __('Ativa') : __('Inativa') }}
                                    </flux:badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="alternarStatus({{ $ala->id }})">
                                            {{ $ala->ativo ? __('Desativar') : __('Ativar') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $ala->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="excluir({{ $ala->id }})"
                                            wire:confirm="{{ __('Excluir esta ala?') }}"
                                        >
                                            {{ __('Excluir') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-zinc-500">
                                    {{ __('Nenhuma ala cadastrada. Cadastre alas antes de criar serviços e guichês.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingId ? __('Editar ala') : __('Nova ala') }}</flux:heading>
        <form wire:submit="salvar" class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Nome') }}</flux:label>
                <flux:input wire:model="nome" placeholder="{{ __('Ex: Ala A, Setor Pediatria') }}" />
            </flux:field>
            <flux:field>
                <flux:checkbox wire:model="ativo" :label="__('Ativa')" />
            </flux:field>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

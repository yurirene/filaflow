<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Médicos')" class="max-w-5xl">
        <flux:text class="mb-4 text-sm text-zinc-500">
            {{ __('Cadastre os médicos e vincule cada um a um consultório na tela de consultórios.') }}
        </flux:text>
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openCreateModal">{{ __('Novo médico') }}</flux:button>
        </div>

        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-3 font-medium">{{ __('Nome') }}</th>
                            <th class="pb-3 font-medium">{{ __('CPF') }}</th>
                            <th class="pb-3 font-medium">{{ __('Consultório') }}</th>
                            <th class="pb-3 font-medium">{{ __('Status') }}</th>
                            <th class="pb-3 font-medium text-end">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicos as $medico)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="medico-{{ $medico->id }}">
                                <td class="py-3">{{ $medico->nome }}</td>
                                <td class="py-3 font-mono">{{ $medico->cpfFormatado() }}</td>
                                <td class="py-3">
                                    @if ($medico->consultorio)
                                        {{ __('Consultório') }} {{ str_pad((string) $medico->consultorio->numero, 2, '0', STR_PAD_LEFT) }}
                                        — {{ $medico->consultorio->ala?->nome }}
                                    @else
                                        <span class="text-zinc-500">{{ __('Sem vínculo') }}</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <flux:badge :color="$medico->isAtivo() ? 'green' : 'zinc'">
                                        {{ $medico->status->label() }}
                                    </flux:badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="alternarStatus({{ $medico->id }})">
                                            {{ $medico->isAtivo() ? __('Desativar') : __('Ativar') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $medico->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="excluir({{ $medico->id }})"
                                            wire:confirm="{{ __('Excluir este médico?') }}"
                                        >
                                            {{ __('Excluir') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-500">{{ __('Nenhum médico cadastrado.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingId ? __('Editar médico') : __('Novo médico') }}</flux:heading>
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

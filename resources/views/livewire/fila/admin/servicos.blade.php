<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Gerenciar serviços')" class="max-w-5xl">
        <flux:text class="mb-4 text-sm text-zinc-500">
            {{ __('Define o tipo de fila e o prefixo da senha (ex: T001). Cada serviço pertence a uma ala.') }}
        </flux:text>
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openServiceModal" :disabled="$this->alas->isEmpty()">
                {{ __('Novo serviço') }}
            </flux:button>
        </div>
        @if ($this->alas->isEmpty())
            <flux:callout variant="warning" class="mb-4">
                {{ __('Cadastre ao menos uma ala em') }}
                <flux:link :href="route('admin.alas')" wire:navigate>{{ __('Alas / setores') }}</flux:link>
                {{ __('antes de criar serviços.') }}
            </flux:callout>
        @endif
        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-2 font-medium">{{ __('Serviço') }}</th>
                            <th class="pb-2 font-medium">{{ __('Prefixo') }}</th>
                            <th class="pb-2 font-medium">{{ __('Ala / setor') }}</th>
                            <th class="pb-2 font-medium" title="{{ __('Calculado com base nos atendimentos finalizados (últimos 30 dias)') }}">{{ __('T. médio') }}</th>
                            <th class="pb-2 font-medium">{{ __('Status') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->servicos as $svc)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="svc-{{ $svc->id }}">
                                <td class="py-3">{{ $svc->nome }}</td>
                                <td class="py-3 font-mono">{{ $svc->prefixo }}</td>
                                <td class="py-3">{{ $svc->ala?->nome ?? '—' }}</td>
                                <td class="py-3">{{ $svc->tempo_medio_minutos }} min</td>
                                <td class="py-3">
                                    <flux:badge :color="$svc->ativo ? 'green' : 'zinc'">
                                        {{ $svc->ativo ? __('Ativo') : __('Inativo') }}
                                    </flux:badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="alternarStatus({{ $svc->id }})">
                                            {{ $svc->ativo ? __('Desativar') : __('Ativar') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $svc->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="excluir({{ $svc->id }})"
                                            wire:confirm="{{ __('Excluir este serviço?') }}"
                                        >
                                            {{ __('Excluir') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showServiceModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingId ? __('Editar serviço') : __('Novo serviço') }}</flux:heading>
        <form wire:submit="salvarServico" class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Nome') }}</flux:label>
                <flux:input wire:model="svcNome" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Prefixo') }}</flux:label>
                <flux:input wire:model="svcPrefixo" maxlength="2" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Ala / setor') }}</flux:label>
                <flux:select wire:model="svcAlaId">
                    @foreach ($this->alas->where('ativo', true) as $ala)
                        <flux:select.option value="{{ $ala->id }}">{{ $ala->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Cor') }}</flux:label>
                <flux:input type="color" wire:model="svcCor" />
            </flux:field>
            <flux:field>
                <flux:checkbox wire:model="svcAtivo" :label="__('Ativo')" />
            </flux:field>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showServiceModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

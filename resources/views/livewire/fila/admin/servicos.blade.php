<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Gerenciar serviços')" class="max-w-5xl">
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openServiceModal">{{ __('Novo serviço') }}</flux:button>
        </div>
        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="pb-2 font-medium">{{ __('Nome') }}</th>
                            <th class="pb-2 font-medium">{{ __('Prefixo') }}</th>
                            <th class="pb-2 font-medium">{{ __('Ala') }}</th>
                            <th class="pb-2 font-medium">{{ __('T. médio') }}</th>
                            <th class="pb-2 font-medium">{{ __('Ativo') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->filaState['servicos'] as $svc)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="svc-row-{{ $svc['id'] }}">
                                <td class="py-3">{{ $svc['icon'] }} {{ $svc['nome'] }}</td>
                                <td class="py-3 font-mono">{{ $svc['prefixo'] }}</td>
                                <td class="py-3">{{ $svc['ala'] }}</td>
                                <td class="py-3">{{ $svc['tMedio'] }} min</td>
                                <td class="py-3">
                                    <flux:badge :color="$svc['ativo'] ? 'green' : 'zinc'">{{ $svc['ativo'] ? __('Sim') : __('Não') }}</flux:badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showServiceModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Novo serviço') }}</flux:heading>
        <form wire:submit="salvarServico" class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Nome') }}</flux:label>
                <flux:input wire:model="svcNome" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Prefixo da senha') }}</flux:label>
                <flux:input wire:model="svcPrefixo" maxlength="2" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Ala / setor') }}</flux:label>
                <flux:input wire:model="svcAla" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Tempo médio (min)') }}</flux:label>
                <flux:input type="number" wire:model="svcTMedio" min="1" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Cor') }}</flux:label>
                <flux:input type="color" wire:model="svcCor" />
            </flux:field>
            <flux:checkbox wire:model="svcAtivo" :label="__('Serviço ativo')" />
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showServiceModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

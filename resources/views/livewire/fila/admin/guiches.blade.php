<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Gerenciar guichês')" class="max-w-5xl">
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openGuicheModal">{{ __('Novo guichê') }}</flux:button>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->filaState['guiches'] as $g)
                <flux:card wire:key="guiche-{{ $g['id'] }}">
                    <flux:heading>{{ __('Guichê') }} {{ str_pad((string) $g['num'], 2, '0', STR_PAD_LEFT) }}</flux:heading>
                    <flux:text class="mt-1">{{ $g['desc'] }}</flux:text>
                    <flux:text class="mt-2 text-sm text-zinc-500">{{ $g['servico'] }}</flux:text>
                    <flux:badge class="mt-3" :color="$g['ativo'] ? 'green' : 'zinc'">{{ $g['ativo'] ? __('Ativo') : __('Inativo') }}</flux:badge>
                </flux:card>
            @endforeach
        </div>
    </x-fila.admin-layout>

    <flux:modal wire:model="showGuicheModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Novo guichê') }}</flux:heading>
        <form wire:submit="salvarGuiche" class="mt-6 space-y-4">
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
                    @foreach ($this->filaState['servicos'] as $svc)
                        <flux:select.option value="{{ $svc['id'] }}">{{ $svc['nome'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showGuicheModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

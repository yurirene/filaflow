<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Relatórios')" class="max-w-4xl">
        <div class="flex flex-wrap items-end gap-4">
            <flux:field>
                <flux:label>{{ __('Período') }}</flux:label>
                <flux:select wire:model="relPeriodo">
                    <flux:select.option value="hoje">{{ __('Hoje') }}</flux:select.option>
                    <flux:select.option value="semana">{{ __('Esta semana') }}</flux:select.option>
                    <flux:select.option value="mes">{{ __('Este mês') }}</flux:select.option>
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Serviço') }}</flux:label>
                <flux:select wire:model="relServico">
                    <flux:select.option value="all">{{ __('Todos') }}</flux:select.option>
                    @foreach ($this->filaState['servicos'] as $svc)
                        <flux:select.option value="{{ $svc['id'] }}">{{ $svc['nome'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:button variant="primary" wire:click="gerarRelatorio">{{ __('Gerar relatório') }}</flux:button>
            <flux:button wire:click="exportarRelatorio">{{ __('Exportar CSV') }}</flux:button>
        </div>
        <flux:card class="mt-6">
            @if ($relatorioResultado)
                <flux:text>{{ $relatorioResultado }}</flux:text>
            @else
                <flux:text class="text-zinc-500">{{ __('Selecione os filtros e clique em "Gerar relatório".') }}</flux:text>
            @endif
        </flux:card>
    </x-fila.admin-layout>
</section>

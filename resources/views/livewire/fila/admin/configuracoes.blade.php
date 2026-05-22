<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Configurações da clínica')" class="max-w-lg">
        <form wire:submit="salvarConfiguracoes" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Nome da clínica') }}</flux:label>
                <flux:input wire:model="clinicName" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('CNPJ') }}</flux:label>
                <flux:input wire:model="cnpj" placeholder="00.000.000/0001-00" />
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Abertura') }}</flux:label>
                    <flux:input type="time" wire:model="horaInicio" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Fechamento') }}</flux:label>
                    <flux:input type="time" wire:model="horaFim" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>{{ __('Mensagem do ticker (Painel TV)') }}</flux:label>
                <flux:input wire:model="ticker" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Reiniciar numeração às') }}</flux:label>
                <flux:input type="time" wire:model="reinicioHora" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Alerta sonoro') }}</flux:label>
                <flux:select wire:model="som">
                    <flux:select.option value="beep">{{ __('Beep padrão') }}</flux:select.option>
                    <flux:select.option value="chime">{{ __('Chime') }}</flux:select.option>
                    <flux:select.option value="bell">{{ __('Sino') }}</flux:select.option>
                    <flux:select.option value="voice">{{ __('Voz sintética') }}</flux:select.option>
                </flux:select>
            </flux:field>
            <flux:button variant="primary" type="submit">{{ __('Salvar configurações') }}</flux:button>
        </form>
    </x-fila.admin-layout>
</section>

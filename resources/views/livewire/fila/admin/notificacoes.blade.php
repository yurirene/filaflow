<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Notificações')" class="max-w-4xl">
        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading>{{ __('WhatsApp') }}</flux:heading>
                    <flux:switch wire:model.live="whatsappAtivo" />
                </div>
                <flux:field>
                    <flux:label>{{ __('Provedor') }}</flux:label>
                    <flux:select wire:model="whatsappProvider">
                        <flux:select.option value="z-api">Z-API</flux:select.option>
                        <flux:select.option value="twilio">Twilio</flux:select.option>
                        <flux:select.option value="evolution">Evolution API</flux:select.option>
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Avisar quando restar X senhas') }}</flux:label>
                    <flux:input type="number" wire:model="whatsappAntecedencia" min="1" max="10" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Mensagem personalizada') }}</flux:label>
                    <flux:textarea wire:model="whatsappMsg" rows="3" />
                </flux:field>
                <div class="flex gap-2">
                    <flux:button variant="primary" wire:click="salvarNotificacao('whatsapp')">{{ __('Salvar') }}</flux:button>
                    <flux:button wire:click="testarNotificacao('whatsapp')">{{ __('Testar') }}</flux:button>
                </div>
            </flux:card>
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading>{{ __('SMS') }}</flux:heading>
                    <flux:switch wire:model.live="smsAtivo" />
                </div>
                <flux:field>
                    <flux:label>{{ __('Provedor') }}</flux:label>
                    <flux:select wire:model="smsProvider">
                        <flux:select.option value="twilio">Twilio</flux:select.option>
                        <flux:select.option value="zenvia">Zenvia</flux:select.option>
                        <flux:select.option value="totalvoice">TotalVoice</flux:select.option>
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Avisar quando restar X senhas') }}</flux:label>
                    <flux:input type="number" wire:model="smsAntecedencia" min="1" max="10" />
                </flux:field>
                <div class="flex gap-2">
                    <flux:button variant="primary" wire:click="salvarNotificacao('sms')">{{ __('Salvar') }}</flux:button>
                    <flux:button wire:click="testarNotificacao('sms')">{{ __('Testar') }}</flux:button>
                </div>
            </flux:card>
        </div>
    </x-fila.admin-layout>
</section>

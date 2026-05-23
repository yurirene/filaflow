<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Acesso do operador')"
        :description="__('Informe seu CPF e senha para acessar o painel de atendimento')"
    />

    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input
            wire:model="cpf"
            :label="__('CPF')"
            type="text"
            inputmode="numeric"
            required
            autofocus
            autocomplete="username"
            placeholder="000.000.000-00"
        />

        <flux:input
            wire:model="password"
            :label="__('Senha')"
            type="password"
            required
            autocomplete="current-password"
            :placeholder="__('Senha')"
            viewable
        />

        <flux:checkbox wire:model="remember" :label="__('Manter conectado')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">{{ __('Entrar') }}</span>
                <span wire:loading wire:target="login">{{ __('Entrando...') }}</span>
            </flux:button>
        </div>
    </form>

    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        <flux:link :href="route('home')" wire:navigate>← {{ __('Voltar ao início') }}</flux:link>
    </div>
</div>

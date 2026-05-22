@props([
    'heading' => '',
    'subheading' => '',
])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Administração') }}">
            <flux:navlist.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('admin.relatorios')" :current="request()->routeIs('admin.relatorios')" wire:navigate>
                {{ __('Relatórios') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('admin.servicos')" :current="request()->routeIs('admin.servicos')" wire:navigate>
                {{ __('Serviços') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('admin.guiches')" :current="request()->routeIs('admin.guiches')" wire:navigate>
                {{ __('Guichês') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('admin.intercalacao')" :current="request()->routeIs('admin.intercalacao')" wire:navigate>
                {{ __('Intercalação') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('admin.notificacoes')" :current="request()->routeIs('admin.notificacoes')" wire:navigate>
                {{ __('Notificações') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('admin.configuracoes')" :current="request()->routeIs('admin.configuracoes')" wire:navigate>
                {{ __('Configurações') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading }}</flux:heading>
        @if ($subheading)
            <flux:subheading>{{ $subheading }}</flux:subheading>
        @endif

        <div class="mt-5 w-full {{ $attributes->get('class', 'max-w-6xl') }}">
            {{ $slot }}
        </div>
    </div>
</div>

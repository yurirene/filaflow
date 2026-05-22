<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Sistema de senhas') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Acesse cada módulo pelo menu lateral ou pelos atalhos abaixo.') }}</flux:text>
            <div class="mt-4 flex flex-wrap gap-3">
                <flux:button :href="route('totem')" variant="primary" target="_blank">{{ __('Totem') }}</flux:button>
                <flux:button :href="route('painel')" target="_blank">{{ __('Painel TV') }}</flux:button>
                <flux:button :href="route('operador')" wire:navigate>{{ __('Operador') }}</flux:button>
                <flux:button :href="route('admin.dashboard')" wire:navigate>{{ __('Administração') }}</flux:button>
            </div>
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>

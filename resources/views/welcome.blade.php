<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
        <div class="flex min-h-screen flex-col items-center justify-center p-6">
            <div class="mb-10 text-center">
                <x-app-logo href="{{ route('home') }}" class="justify-center" />
                <flux:heading size="xl" level="1" class="mt-6">{{ config('app.name', 'Filaflow') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">{{ __('Selecione o módulo de acesso') }}</flux:text>
                <br>
            </div>

            <div class="grid w-full max-w-2xl gap-4 sm:grid-cols-2">
                <a
                    href="{{ route('totem') }}"
                    target="_blank"
                    class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-2xl border border-zinc-200 bg-white p-8 text-center shadow-sm transition hover:border-blue-500 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-blue-400"
                >
                    <flux:icon.ticket class="size-10 text-blue-600 dark:text-blue-400" />
                    <span class="text-lg font-semibold tracking-wide">{{ __('Totem') }}</span>
                </a>

                <a
                    href="{{ route('painel') }}"
                    target="_blank"
                    class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-2xl border border-zinc-200 bg-white p-8 text-center shadow-sm transition hover:border-violet-500 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-400"
                >
                    <flux:icon.tv class="size-10 text-violet-600 dark:text-violet-400" />
                    <span class="text-lg font-semibold tracking-wide">{{ __('Painel TV') }}</span>
                </a>

                <a
                    href="{{ route('operador.login') }}"
                    class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-2xl border border-zinc-200 bg-white p-8 text-center shadow-sm transition hover:border-emerald-500 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-emerald-400"
                >
                    <flux:icon.computer-desktop class="size-10 text-emerald-600 dark:text-emerald-400" />
                    <span class="text-lg font-semibold tracking-wide">{{ __('Operador') }}</span>
                </a>

                <a
                    href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                    class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-2xl border border-zinc-200 bg-white p-8 text-center shadow-sm transition hover:border-amber-500 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-amber-400"
                >
                    <flux:icon.cog-6-tooth class="size-10 text-amber-600 dark:text-amber-400" />
                    <span class="text-lg font-semibold tracking-wide">{{ __('Admin') }}</span>
                </a>
            </div>
        </div>

        @fluxScripts
    </body>
</html>

@php
    $data = $this->dashboardData;
    $kpis = $data['kpis'];
@endphp
<section class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">{{ __('Dashboard') }}</flux:heading>
        <flux:subheading class="mt-1">{{ __('Visão em tempo real da fila e atalhos dos módulos') }}</flux:subheading>
        <flux:separator variant="subtle" class="mt-4" />
    </div>

    <div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <flux:card class="space-y-1">
                <flux:text>{{ __('Atendimentos hoje') }}</flux:text>
                <flux:heading size="xl">{{ $kpis['totalHoje'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Tempo médio') }}</flux:text>
                <flux:heading size="xl">{{ $kpis['tMedio'] }} min</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Em espera agora') }}</flux:text>
                <flux:heading size="xl">{{ $kpis['emEspera'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Ausentes') }}</flux:text>
                <flux:heading size="xl">{{ $kpis['ausentes'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Horário de pico') }}</flux:text>
                <flux:heading size="xl">{{ $kpis['pico'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Guichês ativos') }}</flux:text>
                <flux:heading size="xl">{{ $kpis['guichesAtivos'] }}</flux:heading>
            </flux:card>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <flux:card>
            <flux:heading class="mb-4">{{ __('Atendimentos por hora') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Gráfico disponível em relatórios.') }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:heading class="mb-4">{{ __('Distribuição por serviço') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Gráfico disponível em relatórios.') }}</flux:text>
        </flux:card>
    </div>

    <flux:card>
        <flux:heading class="mb-4">{{ __('Produtividade por operador') }}</flux:heading>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="pb-2 font-medium">{{ __('Operador') }}</th>
                        <th class="pb-2 font-medium">{{ __('Guichê') }}</th>
                        <th class="pb-2 font-medium">{{ __('Serviço') }}</th>
                        <th class="pb-2 font-medium">{{ __('Atendidos') }}</th>
                        <th class="pb-2 font-medium">{{ __('T. médio') }}</th>
                        <th class="pb-2 font-medium">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-3">{{ $data['operadorNome'] }}</td>
                        <td class="py-3">{{ str_pad((string) $data['guicheNumero'], 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="py-3">{{ $data['servicoNome'] }}</td>
                        <td class="py-3">{{ $data['atendidosHoje'] }}</td>
                        <td class="py-3">{{ $this->tMedioOperador }}</td>
                        <td class="py-3"><flux:badge color="green">{{ __('Online') }}</flux:badge></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </flux:card>
</section>

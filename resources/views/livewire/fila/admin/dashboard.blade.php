<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Dashboard em tempo real')">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <flux:card class="space-y-1">
                <flux:text>{{ __('Atendimentos hoje') }}</flux:text>
                <flux:heading size="xl">{{ $this->filaState['kpis']['totalHoje'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Tempo médio') }}</flux:text>
                <flux:heading size="xl">{{ $this->filaState['kpis']['tMedio'] }} min</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Em espera agora') }}</flux:text>
                <flux:heading size="xl">{{ $this->emEsperaAtual }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Ausentes') }}</flux:text>
                <flux:heading size="xl">{{ $this->filaState['kpis']['ausentes'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Horário de pico') }}</flux:text>
                <flux:heading size="xl">{{ $this->filaState['kpis']['pico'] }}</flux:heading>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:text>{{ __('Guichês ativos') }}</flux:text>
                <flux:heading size="xl">{{ $this->filaState['kpis']['guichesAtivos'] }}</flux:heading>
            </flux:card>
        </div>
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <flux:card>
                <flux:heading class="mb-4">{{ __('Atendimentos por hora') }}</flux:heading>
                <flux:text class="text-zinc-500">{{ __('Gráfico disponível após integração com o backend.') }}</flux:text>
            </flux:card>
            <flux:card>
                <flux:heading class="mb-4">{{ __('Distribuição por serviço') }}</flux:heading>
                <flux:text class="text-zinc-500">{{ __('Gráfico disponível após integração com o backend.') }}</flux:text>
            </flux:card>
        </div>
        <flux:card class="mt-6">
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
                            <td class="py-3">{{ $this->filaState['operador']['nome'] }}</td>
                            <td class="py-3">{{ str_pad((string) $this->filaState['operador']['guiche'], 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3">{{ collect($this->filaState['servicos'])->firstWhere('id', $this->filaState['operador']['servico'])['nome'] ?? '' }}</td>
                            <td class="py-3">{{ $this->filaState['stats']['atendidos'] }}</td>
                            <td class="py-3">{{ $this->tMedioOperador }}</td>
                            <td class="py-3"><flux:badge color="green">{{ __('Online') }}</flux:badge></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>
</section>

<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout
        :heading="__('Regras de intercalação')"
        :subheading="__('Proporção entre atendimentos normais e preferenciais (Lei 10.741).')"
        class="max-w-4xl"
    >
        <flux:card class="space-y-4">
            <flux:field>
                <flux:label>{{ __('Serviço') }}</flux:label>
                <flux:select wire:model="intServico">
                    <flux:select.option value="all">{{ __('Todos os serviços') }}</flux:select.option>
                    @foreach ($this->filaState['servicos'] as $svc)
                        <flux:select.option value="{{ $svc['id'] }}">{{ $svc['nome'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Normais por ciclo') }}</flux:label>
                    <flux:input type="number" wire:model="intNormais" min="1" max="10" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Preferenciais por ciclo') }}</flux:label>
                    <flux:input type="number" wire:model="intPreferenciais" min="1" max="5" />
                </flux:field>
            </div>
            <flux:button variant="primary" wire:click="salvarIntercalacao">{{ __('Salvar regra') }}</flux:button>
        </flux:card>
        <flux:card class="mt-6">
            <flux:heading class="mb-4">{{ __('Regras ativas') }}</flux:heading>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="pb-2 font-medium">{{ __('Serviço') }}</th>
                            <th class="pb-2 font-medium">{{ __('Regra') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->filaState['intercalacao'] as $svcId => $ic)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                <td class="py-3">{{ collect($this->filaState['servicos'])->firstWhere('id', $svcId)['nome'] ?? $svcId }}</td>
                                <td class="py-3">{{ $ic['normais'] }} {{ __('normal') }} : {{ $ic['preferenciais'] }} {{ __('preferencial') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>
</section>

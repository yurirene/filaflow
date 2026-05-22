<section class="w-full" wire:poll.1s="tickTimer">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Operador') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Atendimento e gestão da fila') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="grid gap-6 xl:grid-cols-[280px_1fr]">
        <div class="space-y-4">
            <flux:card class="space-y-4">
                <div class="flex items-center gap-3">
                    <flux:avatar :name="$this->filaState['operador']['nome']" />
                    <div>
                        <flux:heading>{{ $this->filaState['operador']['nome'] }}</flux:heading>
                        <flux:text>{{ __('Guichê') }} {{ str_pad((string) $guiche, 2, '0', STR_PAD_LEFT) }}</flux:text>
                    </div>
                </div>
                <flux:separator variant="subtle" />
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div>
                        <div class="text-xl font-bold">{{ $this->filaState['stats']['atendidos'] }}</div>
                        <flux:text class="text-xs">{{ __('Atendidos') }}</flux:text>
                    </div>
                    <div>
                        <div class="text-xl font-bold">{{ $this->tMedio }}</div>
                        <flux:text class="text-xs">{{ __('T. médio') }}</flux:text>
                    </div>
                    <div>
                        <div class="text-xl font-bold">{{ count($this->filaState['filas'][$servico] ?? []) }}</div>
                        <flux:text class="text-xs">{{ __('Na fila') }}</flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Meu guichê') }}</flux:label>
                    <flux:select wire:model.live="guiche">
                        @for ($i = 1; $i <= 5; $i++)
                            <flux:select.option value="{{ $i }}">{{ __('Guichê') }} {{ str_pad((string) $i, 2, '0', STR_PAD_LEFT) }}</flux:select.option>
                        @endfor
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Serviço') }}</flux:label>
                    <flux:select wire:model.live="servico">
                        @foreach ($this->filaState['servicos'] as $svc)
                            <flux:select.option value="{{ $svc['id'] }}">{{ $svc['nome'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:badge color="zinc">{{ $this->intercalacaoBadge }}</flux:badge>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card class="space-y-6">
                <div class="text-center">
                    <flux:text class="tracking-widest uppercase">{{ __('Em atendimento') }}</flux:text>
                    <div class="mt-2 font-mono text-5xl font-bold">
                        {{ $this->filaState['senhaAtual']['codigo'] ?? '---' }}
                    </div>
                    @if ($this->filaState['senhaAtual'])
                        <flux:badge class="mt-2">
                            {{ $this->filaState['senhaAtual']['isPreferencial']
                                ? \App\Support\FilaState::prioridadeLabel($this->filaState['senhaAtual']['prioridade'])
                                : __('Normal') }}
                        </flux:badge>
                    @endif
                    <flux:text class="mt-2 font-mono text-lg">{{ $this->timerFormatado }}</flux:text>
                </div>
                <div class="flex flex-wrap justify-center gap-2">
                    <flux:button variant="primary" wire:click="chamarProxima">{{ __('Chamar próxima') }}</flux:button>
                    <flux:button wire:click="rechamarAtual" :disabled="! $this->temSenhaAtual">{{ __('Rechamar') }}</flux:button>
                    <flux:button wire:click="$set('showTransferModal', true)" :disabled="! $this->temSenhaAtual">{{ __('Transferir') }}</flux:button>
                    <flux:button variant="filled" wire:click="finalizarAtendimento" :disabled="! $this->temSenhaAtual">{{ __('Finalizar') }}</flux:button>
                    <flux:button variant="danger" wire:click="marcarAusente" :disabled="! $this->temSenhaAtual">{{ __('Ausente') }}</flux:button>
                </div>
            </flux:card>

            <flux:card>
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <flux:heading>{{ __('Fila de espera') }}</flux:heading>
                    <flux:button.group>
                        @foreach (['all' => __('Todos'), 'preferencial' => __('Preferencial'), 'normal' => __('Normal'), 'agendado' => __('Agendado')] as $f => $label)
                            <flux:button
                                size="sm"
                                wire:click="filterQueue('{{ $f }}')"
                                variant="{{ $queueFilter === $f ? 'primary' : 'ghost' }}"
                            >{{ $label }}</flux:button>
                        @endforeach
                    </flux:button.group>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->filaFiltrada as $idx => $senha)
                        @php
                            $svc = collect($this->filaState['servicos'])->firstWhere('id', $senha['servicoId']);
                            $minutos = (int) now()->diffInMinutes(\Carbon\Carbon::parse($senha['emitidaEm']));
                        @endphp
                        <div class="flex items-center gap-4 py-3" wire:key="fila-{{ $senha['id'] }}">
                            <flux:badge>{{ $idx + 1 }}</flux:badge>
                            <span class="font-mono text-lg font-bold">{{ $senha['codigo'] }}</span>
                            <div class="min-w-0 flex-1">
                                <flux:text>{{ $svc['nome'] ?? $senha['servicoId'] }}</flux:text>
                                <flux:text class="text-xs">{{ __('Aguardando há') }} {{ $minutos < 1 ? __('menos de 1') : $minutos }} min</flux:text>
                            </div>
                            <flux:badge :color="$senha['isPreferencial'] ? 'amber' : 'zinc'">
                                {{ $senha['isPreferencial'] ? \App\Support\FilaState::prioridadeLabel($senha['prioridade']) : __('Normal') }}
                            </flux:badge>
                        </div>
                    @empty
                        <flux:text class="py-8 text-center">{{ __('Fila vazia') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>

            <div class="grid gap-6 lg:grid-cols-2">
                <flux:card>
                    <flux:heading class="mb-4">{{ __('Agendamentos de hoje') }}</flux:heading>
                    <div class="space-y-2">
                        @foreach ($this->filaState['agendamentos'] as $ag)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700" wire:key="ag-{{ $ag['id'] }}">
                                <div>
                                    <flux:text class="font-medium">{{ $ag['nome'] }}</flux:text>
                                    <flux:text class="text-xs">{{ $ag['hora'] }} · {{ $ag['servico'] }}</flux:text>
                                </div>
                                <flux:badge size="sm">{{ $ag['status'] }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <flux:card>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading>{{ __('Histórico do turno') }}</flux:heading>
                        <flux:button size="sm" variant="ghost" wire:click="clearLog">{{ __('Limpar') }}</flux:button>
                    </div>
                    <div class="max-h-48 space-y-1 overflow-y-auto">
                        @forelse (array_reverse($this->filaState['log']) as $entry)
                            <flux:text class="text-xs" wire:key="log-{{ $entry['hora'] }}-{{ $entry['msg'] }}">
                                <span class="text-zinc-500">{{ $entry['hora'] }}</span> — {{ $entry['msg'] }}
                            </flux:text>
                        @empty
                            <flux:text class="text-xs text-zinc-500">{{ __('Nenhum registro') }}</flux:text>
                        @endforelse
                    </div>
                </flux:card>
            </div>
        </div>
    </div>

    <flux:modal wire:model="showTransferModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Transferir senha') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('Transferindo') }}: <strong>{{ $this->filaState['senhaAtual']['codigo'] ?? '---' }}</strong>
        </flux:text>
        <div class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Serviço de destino') }}</flux:label>
                <flux:select wire:model="transferServico">
                    @foreach ($this->filaState['servicos'] as $svc)
                        <flux:select.option value="{{ $svc['id'] }}">{{ $svc['nome'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Motivo (opcional)') }}</flux:label>
                <flux:input wire:model="transferMotivo" placeholder="{{ __('Ex: Encaminhamento médico') }}" />
            </flux:field>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <flux:button variant="ghost" wire:click="$set('showTransferModal', false)">{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" wire:click="confirmarTransferencia">{{ __('Confirmar') }}</flux:button>
        </div>
    </flux:modal>
</section>

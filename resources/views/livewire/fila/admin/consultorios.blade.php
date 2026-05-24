<section class="w-full">
    @include('partials.fila-admin-heading')

    <x-fila.admin-layout :heading="__('Gerenciar consultórios')" class="max-w-5xl">
        <flux:text class="mb-4 text-sm text-zinc-500">
            {{ __('Salas de atendimento na ala. Cada consultório deve ter um médico vinculado para acesso ao painel de chamadas.') }}
        </flux:text>
        <div class="mb-4">
            <flux:button variant="primary" wire:click="openModal" :disabled="$this->alasConsultorio->isEmpty() || $this->medicosDisponiveis->isEmpty()">
                {{ __('Novo consultório') }}
            </flux:button>
        </div>
        <flux:card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="pb-3 font-medium">{{ __('Número') }}</th>
                            <th class="pb-3 font-medium">{{ __('Médico') }}</th>
                            <th class="pb-3 font-medium">{{ __('Ala / setor') }}</th>
                            <th class="pb-3 font-medium">{{ __('Serviços') }}</th>
                            <th class="pb-3 font-medium">{{ __('Status') }}</th>
                            <th class="pb-3 font-medium text-end">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->consultorios as $c)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="consultorio-{{ $c->id }}">
                                <td class="py-3 font-mono">{{ str_pad((string) $c->numero, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3">{{ $c->medico?->nome ?? '—' }}</td>
                                <td class="py-3">{{ $c->ala?->nome ?? '—' }}</td>
                                <td class="py-3 text-zinc-600 dark:text-zinc-400">
                                    @if ($c->servicos->isNotEmpty())
                                        {{ $c->servicos->pluck('nome')->join(', ') }}
                                    @else
                                        {{ __('Todos da ala') }}
                                    @endif
                                </td>
                                <td class="py-3">
                                    <flux:badge :color="$c->ativo ? 'green' : 'zinc'">
                                        {{ $c->ativo ? __('Ativo') : __('Inativo') }}
                                    </flux:badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="alternarStatus({{ $c->id }})">
                                            {{ $c->ativo ? __('Desativar') : __('Ativar') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $c->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="excluir({{ $c->id }})"
                                            wire:confirm="{{ __('Excluir este consultório?') }}"
                                        >
                                            {{ __('Excluir') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-500">
                                    {{ __('Nenhum consultório cadastrado.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    </x-fila.admin-layout>

    <flux:modal wire:model="showModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingId ? __('Editar consultório') : __('Novo consultório') }}</flux:heading>
        <form wire:submit="salvar" class="mt-6 space-y-4">
            <flux:field>
                <flux:label>{{ __('Ala / setor') }}</flux:label>
                <flux:select wire:model.live="alaId">
                    @foreach ($this->alasConsultorio as $ala)
                        <flux:select.option value="{{ $ala->id }}">{{ $ala->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Número') }}</flux:label>
                <flux:input type="number" wire:model="numero" min="1" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Médico') }}</flux:label>
                <flux:select wire:model="medicoId">
                    <flux:select.option value="">{{ __('Selecione…') }}</flux:select.option>
                    @foreach ($this->medicosDisponiveis as $medico)
                        <flux:select.option value="{{ $medico->id }}">{{ $medico->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('medicoId')
                    <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
                @enderror
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Serviços permitidos (opcional)') }}</flux:label>
                <flux:text class="mb-2 text-xs text-zinc-500">{{ __('Vazio = serviços da mesma ala do consultório') }}</flux:text>
                <div class="max-h-40 space-y-2 overflow-y-auto rounded border border-zinc-200 p-3 dark:border-zinc-700">
                    @foreach ($this->servicosAtivos as $svc)
                        <label class="flex items-center gap-2 text-sm" wire:key="svc-opt-{{ $svc->id }}">
                            <input type="checkbox" wire:model="servicosSelecionados" value="{{ $svc->id }}" />
                            {{ $svc->nome }} ({{ $svc->ala?->nome }})
                        </label>
                    @endforeach
                </div>
            </flux:field>
            <flux:field>
                <flux:checkbox wire:model="ativo" :label="__('Ativo')" />
            </flux:field>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" type="button" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>

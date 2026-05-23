@php
    $empresa = $this->totemData['empresa'];
    $servicos = $this->totemData['servicos'];
    $filasResumo = $this->totemData['filasResumo'];
    $notificacoes = $empresa->notificacoes ?? [];
@endphp
<div class="view active">
    <div class="totem-container">
        @if ($screen === 'home')
            <div class="totem-screen">
                <div class="totem-header">
                    <div class="totem-logo">⚕</div>
                    <h1 class="totem-clinic-name">{{ $empresa->nome }}</h1>
                    <p class="totem-subtitle">{{ __('Bem-vindo! Selecione o tipo de atendimento') }}</p>
                </div>

                <div class="totem-priority-bar">
                    @foreach ([
                        'idoso' => ['👴', __('Idoso (60+)')],
                        'pcd' => ['♿', __('PCD')],
                        'gestante' => ['🤰', __('Gestante')],
                        'normal' => ['👤', __('Atendimento Geral')],
                    ] as $tipo => [$icon, $label])
                        <button
                            type="button"
                            class="priority-btn {{ $prioridadeSelecionada === $tipo ? 'selected' : '' }}"
                            wire:click="setPriority('{{ $tipo }}')"
                        >
                            <span class="priority-icon">{{ $icon }}</span>
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="totem-services">
                    @foreach ($servicos as $svc)
                        @php
                            $resumo = $filasResumo[$svc->id] ?? ['tamanho' => 0, 'esperaMin' => 1];
                        @endphp
                        <button
                            type="button"
                            class="service-btn"
                            style="--svc-color: {{ $svc->cor }}"
                            wire:click="emitirSenha('{{ $svc->id }}')"
                        >
                            <span class="service-btn-icon">{{ $svc->icone }}</span>
                            <span class="service-btn-name">{{ $svc->nome }}</span>
                            <span class="service-btn-wait">⏱ ~{{ $resumo['esperaMin'] }} min</span>
                            <span class="service-btn-queue">{{ $resumo['tamanho'] }} {{ __('na fila') }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="totem-footer">
                    <div class="totem-wait-info">
                        <span class="wait-icon">⏱</span>
                        <span>
                            @switch($prioridadeSelecionada)
                                @case('idoso') {{ __('Atendimento preferencial — Idoso (Lei 10.741)') }} @break
                                @case('pcd') {{ __('Atendimento preferencial — Pessoa com Deficiência') }} @break
                                @case('gestante') {{ __('Atendimento preferencial — Gestante') }} @break
                                @default {{ __('Selecione um serviço para ver o tempo estimado') }}
                            @endswitch
                        </span>
                    </div>
                </div>
            </div>
        @else
            <div class="totem-screen">
                <div class="ticket-card">
                    <div class="ticket-header">
                        <span class="ticket-clinic">{{ $empresa->nome }}</span>
                        <span class="ticket-date">{{ $ticket['data'] ?? '' }}</span>
                    </div>
                    <div class="ticket-body">
                        <div class="ticket-priority-badge {{ $ticket['prioridade'] ?? 'normal' }}">
                            {{ $ticket['badge'] ?? '' }}
                        </div>
                        <div class="ticket-number">{{ $ticket['codigo'] ?? '' }}</div>
                        <div class="ticket-service">{{ $ticket['servico'] ?? '' }}</div>
                        <div class="ticket-wait">
                            <span class="ticket-wait-label">{{ __('Espera estimada') }}</span>
                            <span class="ticket-wait-value">~{{ $ticket['espera'] ?? 0 }} min</span>
                        </div>
                        <div class="ticket-position">
                            <span class="ticket-pos-label">{{ __('Posição na fila') }}</span>
                            <span class="ticket-pos-value">{{ $ticket['posicao'] ?? 1 }}ª</span>
                        </div>
                    </div>
                    <div class="ticket-footer">
                        <p class="ticket-msg">{{ __('Aguarde ser chamado no painel') }}</p>
                        @if (($notificacoes['whatsapp']['ativo'] ?? false) || ($notificacoes['sms']['ativo'] ?? false))
                            <p class="ticket-sms">📱 {{ __('Você receberá uma notificação quando sua vez estiver próxima.') }}</p>
                        @endif
                    </div>
                    <div class="ticket-actions">
                        <button type="button" class="btn-primary" onclick="window.print()">🖨 {{ __('Imprimir Senha') }}</button>
                        <button type="button" class="btn-secondary" wire:click="resetTotem">← {{ __('Voltar ao Início') }}</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

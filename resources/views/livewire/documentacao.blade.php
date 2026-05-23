<section class="mx-auto flex w-full max-w-4xl flex-col gap-8 pb-12">
    <div>
        <flux:heading size="xl" level="1">{{ __('Documentação') }}</flux:heading>
        <flux:subheading class="mt-2">
            {{ __('Guia de configuração, fluxos de atendimento e decisões de cada opção do FilaFlow.') }}
        </flux:subheading>
        <flux:separator variant="subtle" class="mt-4" />
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">{{ __('Neste guia') }}</flux:heading>
        <nav class="flex flex-col gap-1 text-sm">
            <a href="#visao-geral" class="text-blue-600 hover:underline dark:text-blue-400">1. {{ __('Visão geral') }}</a>
            <a href="#fluxo" class="text-blue-600 hover:underline dark:text-blue-400">2. {{ __('Fluxo do atendimento') }}</a>
            <a href="#configuracao" class="text-blue-600 hover:underline dark:text-blue-400">3. {{ __('Configuração passo a passo') }}</a>
            <a href="#modulos" class="text-blue-600 hover:underline dark:text-blue-400">4. {{ __('Módulos do dia a dia') }}</a>
            <a href="#admin" class="text-blue-600 hover:underline dark:text-blue-400">5. {{ __('Administração — opções e porquês') }}</a>
            <a href="#regras" class="text-blue-600 hover:underline dark:text-blue-400">6. {{ __('Regras de negócio') }}</a>
            <a href="#manutencao" class="text-blue-600 hover:underline dark:text-blue-400">7. {{ __('Manutenção e agendamentos') }}</a>
            <a href="#checklist" class="text-blue-600 hover:underline dark:text-blue-400">8. {{ __('Checklist de implantação') }}</a>
        </nav>
    </flux:card>

    <article id="visao-geral" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">1. {{ __('Visão geral') }}</flux:heading>
        <flux:text>
            {{ __('O FilaFlow organiza o atendimento da clínica em filas por serviço (Triagem, Coleta, etc.). O paciente retira senha no Totem; o operador chama a próxima senha respeitando prioridade legal e regras de intercalação; o Painel TV exibe a chamada para o público.') }}
        </flux:text>
        <div class="grid gap-3 sm:grid-cols-2">
            <flux:card class="space-y-1">
                <flux:badge color="blue">{{ __('Totem') }}</flux:badge>
                <flux:text class="text-sm">{{ __('Emissão de senhas — sem login, tela cheia.') }}</flux:text>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:badge color="purple">{{ __('Operador') }}</flux:badge>
                <flux:text class="text-sm">{{ __('Chamada, finalização, transferência e ausência.') }}</flux:text>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:badge color="zinc">{{ __('Painel TV') }}</flux:badge>
                <flux:text class="text-sm">{{ __('Exibe senha chamada, guichê e filas por ala.') }}</flux:text>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:badge color="green">{{ __('Administração') }}</flux:badge>
                <flux:text class="text-sm">{{ __('Cadastros, regras, relatórios e configurações da clínica.') }}</flux:text>
            </flux:card>
        </div>
    </article>

    <article id="fluxo" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">2. {{ __('Fluxo do atendimento') }}</flux:heading>
        <flux:text>{{ __('Entender este fluxo ajuda a configurar na ordem certa e a escolher cada opção com critério.') }}</flux:text>

        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 font-mono text-xs leading-relaxed dark:border-zinc-700 dark:bg-zinc-900">
            <pre class="whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">@verbatim
[Paciente] → Totem: escolhe prioridade + serviço → Senha emitida (ex: T042)
                ↓
         Fila "aguardando" (ordem: agendados → preferenciais → normais)
                ↓
[Operador] → Chamar próxima → Intercalação aplica (ex: 2 normais : 1 preferencial)
                ↓
         Senha "chamado" + registro em chamadas + Painel TV atualiza
                ↓
         Finalizar OU Ausente OU Trocar fila no guichê OU Encaminhar ao consultório
                ↓
         Fila exclusiva do consultório (consultorio_id preenchido)
                ↓
[Operador consultório] → Chamar próxima → Painel exibe consultório e responsável
                ↓
         Finalizar OU Ausente
@endverbatim</pre>
        </div>

        <flux:card class="space-y-2">
            <flux:heading size="sm">{{ __('Por que separar Totem, Operador e Painel?') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('O Totem fica em quiosque público (sem senha de usuário). O Operador exige login e registro de quem atende. O Painel roda em TV na sala de espera, só leitura. Separar reduz risco de alteração indevida e permite hardware dedicado em cada ponto.') }}
            </flux:text>
        </flux:card>
    </article>

    <article id="configuracao" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">3. {{ __('Configuração passo a passo') }}</flux:heading>
        <flux:text>{{ __('Siga esta ordem na primeira implantação. Pular etapas costuma gerar fila vazia, guichê errado ou senhas sem prefixo.') }}</flux:text>

        <ol class="list-decimal space-y-6 ps-5">
            <li class="space-y-2">
                <flux:heading size="sm">{{ __('Ambiente inicial') }}</flux:heading>
                <flux:text class="text-sm">
                    {{ __('Execute as migrations e o seeder (`php artisan migrate --seed`). Isso cria a clínica demo, alas, serviços, guichês e operadores de teste.') }}
                </flux:text>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.configuracoes') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Configurações da clínica') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Defina identidade e comportamento global antes dos cadastros operacionais.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Nome / CNPJ') }}</strong> — {{ __('aparecem no Totem e no Painel.') }}</li>
                    <li><strong>{{ __('Abertura / Fechamento') }}</strong> — {{ __('referência exibida no rodapé do Painel; orienta o público.') }}</li>
                    <li><strong>{{ __('Ticker') }}</strong> — {{ __('mensagem rolante no Painel (documentos, exames, etc.).') }}</li>
                    <li><strong>{{ __('Reiniciar numeração') }}</strong> — {{ __('horário em que o contador diário de senhas (T001, C001…) volta a 1. Use após o expediente ou à meia-noite.') }}</li>
                    <li><strong>{{ __('Alerta sonoro') }}</strong> — {{ __('tipo de som quando uma nova senha aparece no Painel (beep, chime, etc.).') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.servicos') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Serviços') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Cada serviço define o tipo de fila e o prefixo da senha. O paciente escolhe um no Totem; no guichê o operador atende a fila da recepção; após encaminhar, a senha passa à fila do consultório.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Prefixo (2 letras)') }}</strong> — {{ __('forma o código da senha (T = Triagem). Deve ser único na clínica.') }}</li>
                    <li><strong>{{ __('Ala / setor') }}</strong> — {{ __('filtra o Painel TV quando há várias alas no mesmo prédio.') }}</li>
                    <li><strong>{{ __('Tempo médio (min)') }}</strong> — {{ __('multiplicado pela posição na fila para estimar espera no Totem (ex: 3ª posição × 8 min ≈ 24 min).') }}</li>
                    <li><strong>{{ __('Cor') }}</strong> — {{ __('identificação visual no Totem.') }}</li>
                    <li><strong>{{ __('Ativo') }}</strong> — {{ __('serviços inativos somem do Totem mas permanecem no histórico.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.guiches') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Guichês') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Ponto de recepção ou triagem na ala. Senhas com consultorio_id vazio ficam na fila do guichê até serem encaminhadas.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Número') }}</strong> — {{ __('único por clínica (01, 02…). O operador seleciona “Meu guichê” com este número.') }}</li>
                    <li><strong>{{ __('Serviço padrão') }}</strong> — {{ __('referência administrativa; o operador ainda escolhe o serviço da fila que está atendendo.') }}</li>
                    <li><strong>{{ __('Ativo') }}</strong> — {{ __('guichês inativos não aparecem na lista do operador.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.consultorios') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Consultórios') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Salas de atendimento na ala (número + responsável). Após encaminhar do guichê, a senha só aparece na fila do consultório escolhido.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Serviços permitidos') }}</strong> — {{ __('opcional; se vazio, aceita qualquer serviço da mesma ala.') }}</li>
                    <li><strong>{{ __('Operador') }}</strong> — {{ __('use o modo Consultório no topo da tela para chamar senhas encaminhadas.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.intercalacao') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Intercalação') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Obrigatório se houver atendimento preferencial (idoso, PCD, gestante). Detalhes na seção 6.') }}</flux:text>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">{{ __('Dispositivos') }}</flux:heading>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li>{{ __('Totem: tablet/PC na entrada — abra') }} <a href="{{ route('totem') }}" target="_blank" class="text-blue-600 underline">{{ route('totem') }}</a> {{ __('em tela cheia (F11).') }}</li>
                    <li>{{ __('Painel TV: monitor na sala de espera — abra') }} <a href="{{ route('painel') }}" target="_blank" class="text-blue-600 underline">{{ route('painel') }}</a> {{ __('e selecione a ala, se aplicável.') }}</li>
                    <li>{{ __('Operador: estação de cada atendente — login em') }} <a href="{{ route('operador.login') }}" class="text-blue-600 underline">{{ __('Operador') }}</a>.</li>
                </ul>
            </li>
        </ol>
    </article>

    <article id="modulos" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">4. {{ __('Módulos do dia a dia') }}</flux:heading>

        <div class="space-y-4">
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Totem — emissão de senha') }}</flux:heading>
                <flux:text class="text-sm">
                    <strong>1.</strong> {{ __('Paciente toca o tipo de atendimento: Normal, Idoso (60+), PCD ou Gestante.') }}
                    <br><strong>2.</strong> {{ __('Escolhe o serviço (Triagem, Coleta…).') }}
                    <br><strong>3.</strong> {{ __('Recebe o ticket com código, posição na fila e espera estimada.') }}
                </flux:text>
                <flux:text class="text-sm text-zinc-500">
                    {{ __('Por quê prioridade antes do serviço? A lei exige fila preferencial; o sistema marca a senha e posiciona na fila corretamente antes de calcular a espera.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Operador — atendimento') }}</flux:heading>
                <flux:text class="text-sm">
                    <strong>{{ __('Meu guichê / Serviço') }}</strong> — {{ __('define de onde e qual fila você chama. Troque o serviço se atender outro setor no mesmo guichê.') }}
                    <br><strong>{{ __('Chamar próxima') }}</strong> — {{ __('aplica intercalação, remove da fila, mostra no Painel e inicia cronômetro.') }}
                    <br><strong>{{ __('Rechamar') }}</strong> — {{ __('repete a exibição no Painel (paciente não ouviu).') }}
                    <br><strong>{{ __('Finalizar') }}</strong> — {{ __('encerra atendimento com sucesso; conta nas estatísticas.') }}
                    <br><strong>{{ __('Ausente') }}</strong> — {{ __('paciente não compareceu; libera o operador sem contar como atendido.') }}
                    <br><strong>{{ __('Transferir') }}</strong> — {{ __('envia a senha para outra fila (ex: Triagem → Coleta) mantendo prioridade.') }}
                </flux:text>
                <flux:text class="text-sm text-zinc-500">
                    {{ __('Filtros da fila (Todos / Preferencial / Normal / Agendado) só alteram a visualização; a chamada sempre segue a regra de intercalação.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Painel TV') }}</flux:heading>
                <flux:text class="text-sm">
                    {{ __('Atualiza a cada poucos segundos. Mostra a última senha chamada, guichê, histórico recente e resumo das filas. O seletor de') }}
                    <strong>{{ __('Ala') }}</strong> {{ __('limita os serviços exibidos quando a clínica tem vários setores físicos.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Dashboard') }}</flux:heading>
                <flux:text class="text-sm">
                    {{ __('KPIs do dia: total de senhas, tempo médio configurado, quantos aguardam agora, ausentes, guichês ativos. Use para supervisão; gráficos detalhados ficam em Relatórios.') }}
                </flux:text>
            </flux:card>
        </div>
    </article>

    <article id="admin" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">5. {{ __('Administração — opções e porquês') }}</flux:heading>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="pb-2 pe-4 font-medium">{{ __('Tela') }}</th>
                        <th class="pb-2 pe-4 font-medium">{{ __('Quando usar') }}</th>
                        <th class="pb-2 font-medium">{{ __('Decisão principal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Relatórios') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Análise histórica, exportação') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Período e serviço para comparar desempenho') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Serviços') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Novo procedimento ou setor') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Prefixo curto e tempo médio realista') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Guichês') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Nova mesa ou reorganização') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Numeração alinhada à placa física') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Intercalação') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Fila mista normal + preferencial') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Proporção legal (ex: 2:1)') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Notificações') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Reduzir abandono na fila') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Provedor + quantas senhas antes do aviso') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Configurações') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Identidade e horários') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Reinício da numeração no fim do dia') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>

    <article id="regras" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">6. {{ __('Regras de negócio') }}</flux:heading>

        <flux:card class="space-y-2">
            <flux:heading size="sm">{{ __('Intercalação (ex: 2 normais : 1 preferencial)') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('A cada chamada, o sistema olha a posição no ciclo. Nos slots “normais”, chama o primeiro normal da fila; nos slots “preferenciais”, o primeiro preferencial. Se um tipo acabar, chama o outro — a fila nunca trava.') }}
            </flux:text>
            <flux:text class="text-sm text-zinc-500">
                {{ __('Por quê não chamar sempre o preferencial primeiro? Para equilibrar fluxo e cumprir a proporção definida pela clínica conforme a Lei do Idoso e boas práticas de acessibilidade.') }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <flux:heading size="sm">{{ __('Ordem na fila de espera') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Agendados entram após os preferenciais já na fila. Preferenciais entram antes dos normais. Dentro de cada grupo, vale a ordem de chegada.') }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <flux:heading size="sm">{{ __('Estimativa de espera') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('posição × tempo médio do serviço. É uma aproximação — se os atendimentos atrasarem, a espera real pode ser maior. Ajuste o tempo médio nos serviços conforme a operação.') }}
            </flux:text>
        </flux:card>
    </article>

    <article id="manutencao" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">7. {{ __('Manutenção e agendamentos') }}</flux:heading>
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Agendamentos na fila') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Consultas marcadas podem entrar automaticamente na fila cerca de 15 minutos antes do horário. O comando roda a cada minuto:') }}
            </flux:text>
            <pre class="overflow-x-auto rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">php artisan fila:integrar-agendamentos</pre>
            <flux:text class="text-sm text-zinc-500">
                {{ __('Em produção, configure o scheduler do Laravel (`* * * * * php artisan schedule:run`). Sem isso, apenas senhas emitidas no Totem entram na fila.') }}
            </flux:text>
        </flux:card>
        <flux:card class="space-y-2">
            <flux:heading size="sm">{{ __('Contador de senhas') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Reinicia no horário definido em Configurações. Cada serviço tem sua sequência (T001, C001…) por dia.') }}
            </flux:text>
        </flux:card>
    </article>

    <article id="checklist" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">8. {{ __('Checklist de implantação') }}</flux:heading>
        <flux:card>
            <ul class="space-y-2 text-sm">
                <li class="flex gap-2"><span>☐</span> {{ __('Migrations e seed executados') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Configurações da clínica salvas (nome, horários, ticker)') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Todos os serviços cadastrados com prefixo e tempo médio') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Guichês numerados conforme o layout físico') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Intercalação definida (ex: 2:1) em cada serviço com preferencial') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Totem e Painel abertos nos dispositivos corretos') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Operadores treinados (chamar, ausente, transferir)') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Scheduler ativo se usar agendamentos') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Teste completo: emitir senha → chamar → ver no Painel → finalizar') }}</li>
            </ul>
        </flux:card>
        <div class="flex flex-wrap gap-3">
            <flux:button :href="route('admin.configuracoes')" variant="primary" wire:navigate>{{ __('Ir para configurações') }}</flux:button>
            <flux:button :href="route('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:button>
        </div>
    </article>
</section>

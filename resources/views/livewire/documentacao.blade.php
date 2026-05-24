<section class="mx-auto flex w-full max-w-4xl flex-col gap-8 pb-12">
    <div>
        <flux:heading size="xl" level="1">{{ __('Documentação') }}</flux:heading>
        <flux:subheading class="mt-2">
            {{ __('Guia de uso, fluxos de atendimento e orientações para configurar o FilaFlow na sua clínica.') }}
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
            <a href="#manutencao" class="text-blue-600 hover:underline dark:text-blue-400">7. {{ __('Agendamentos e rotina') }}</a>
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
                <flux:text class="text-sm">{{ __('Chamada, finalização, transferência, encaminhamento e ausência.') }}</flux:text>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:badge color="zinc">{{ __('Painel TV') }}</flux:badge>
                <flux:text class="text-sm">{{ __('Exibe senha chamada, guichê ou consultório, com aviso por voz.') }}</flux:text>
            </flux:card>
            <flux:card class="space-y-1">
                <flux:badge color="teal">{{ __('Médico') }}</flux:badge>
                <flux:text class="text-sm">{{ __('Chamada de senhas encaminhadas ao consultório vinculado.') }}</flux:text>
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

        <flux:card class="space-y-3 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
            <p><strong>1.</strong> {{ __('Paciente retira senha no Totem (prioridade + serviço).') }}</p>
            <p><strong>2.</strong> {{ __('Senha entra na fila de espera (agendados, preferenciais e normais).') }}</p>
            <p><strong>3.</strong> {{ __('Operador na recepção chama a próxima senha do guichê.') }}</p>
            <p><strong>4.</strong> {{ __('Painel TV atualiza e anuncia a chamada.') }}</p>
            <p><strong>5.</strong> {{ __('Operador finaliza, marca ausente, transfere de serviço ou encaminha ao consultório com nome do paciente.') }}</p>
            <p><strong>6.</strong> {{ __('No consultório, médico ou operador chama a fila da sala.') }}</p>
            <p><strong>7.</strong> {{ __('Atendimento finalizado ou paciente ausente.') }}</p>
        </flux:card>

        <flux:card class="space-y-2">
            <flux:heading size="sm">{{ __('Por que separar Totem, Operador e Painel?') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('O Totem fica em quiosque público (sem senha de usuário). O Operador exige login e registro de quem atende. O Painel roda em TV na sala de espera, só leitura. Separar reduz risco de alteração indevida e permite um equipamento dedicado em cada ponto.') }}
            </flux:text>
        </flux:card>
    </article>

    <article id="configuracao" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">3. {{ __('Configuração passo a passo') }}</flux:heading>
        <flux:text>{{ __('Siga esta ordem na primeira implantação. Pular etapas costuma gerar fila vazia, guichê errado ou senhas sem prefixo.') }}</flux:text>

        <ol class="list-decimal space-y-6 ps-5">
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.alas') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Alas / setores') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Primeiro cadastro estrutural. Define onde guichês, consultórios e serviços ficam no prédio.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Ala de consultório') }}</strong> — {{ __('marque quando a ala tiver salas de atendimento médico.') }}</li>
                    <li><strong>{{ __('Ativo') }}</strong> — {{ __('alas inativas não aparecem nos seletores.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.configuracoes') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Configurações da clínica') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Defina identidade e comportamento global antes dos cadastros operacionais.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Nome / CNPJ') }}</strong> — {{ __('aparecem no Totem e no Painel.') }}</li>
                    <li><strong>{{ __('Abertura / Fechamento') }}</strong> — {{ __('referência exibida no rodapé do Painel.') }}</li>
                    <li><strong>{{ __('Ticker') }}</strong> — {{ __('mensagem rolante no Painel (documentos, exames, etc.).') }}</li>
                    <li><strong>{{ __('Reiniciar numeração') }}</strong> — {{ __('horário em que os contadores diários de senha voltam a 1.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.servicos') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Serviços') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Cada serviço define o tipo de fila e o prefixo da senha.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Prefixo (2 letras)') }}</strong> — {{ __('forma o código da senha (T = Triagem). Deve ser único na clínica.') }}</li>
                    <li><strong>{{ __('Ala / setor') }}</strong> — {{ __('filtra o Painel TV quando há várias alas.') }}</li>
                    <li><strong>{{ __('Tempo médio (min)') }}</strong> — {{ __('usado para estimar a espera no Totem.') }}</li>
                    <li><strong>{{ __('Cor') }}</strong> — {{ __('identificação visual no Totem.') }}</li>
                    <li><strong>{{ __('Ativo') }}</strong> — {{ __('serviços inativos somem do Totem.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.guiches') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Guichês') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Ponto de recepção ou triagem na ala.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Número') }}</strong> — {{ __('único por clínica (01, 02…). O operador seleciona “Meu guichê” com este número.') }}</li>
                    <li><strong>{{ __('Serviço padrão') }}</strong> — {{ __('referência administrativa.') }}</li>
                    <li><strong>{{ __('Ativo') }}</strong> — {{ __('guichês inativos não aparecem na lista do operador.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.consultorios') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Consultórios') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Salas de atendimento. Após encaminhar do guichê, a senha só aparece na fila do consultório escolhido.') }}</flux:text>
                <ul class="list-disc space-y-1 ps-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>{{ __('Serviços permitidos') }}</strong> — {{ __('opcional; se vazio, aceita qualquer serviço da mesma ala.') }}</li>
                    <li><strong>{{ __('Médico vinculado') }}</strong> — {{ __('cadastre em Médicos e associe ao consultório.') }}</li>
                </ul>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.medicos') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Médicos') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Cada médico precisa de consultório ativo para acessar o sistema. Atende apenas a fila encaminhada ao seu consultório.') }}</flux:text>
            </li>
            <li class="space-y-2">
                <flux:heading size="sm">
                    <a href="{{ route('admin.operadores') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>{{ __('Operadores') }}</a>
                </flux:heading>
                <flux:text class="text-sm">{{ __('Equipe da recepção e triagem. Acesso com CPF e senha cadastrados.') }}</flux:text>
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
                    <li>{{ __('Totem: tablet ou PC na entrada — abra o Totem em tela cheia (F11).') }}</li>
                    <li>{{ __('Painel TV: um monitor por ala, se necessário — selecione a ala no painel e clique em Iniciar painel para habilitar o aviso por voz.') }}</li>
                    <li>{{ __('Operador e Médico: estações com login individual.') }}</li>
                </ul>
                <div class="flex flex-wrap gap-3 pt-2">
                    <flux:button :href="route('totem')" target="_blank" size="sm">{{ __('Abrir Totem') }}</flux:button>
                    <flux:button :href="route('painel')" target="_blank" size="sm">{{ __('Abrir Painel TV') }}</flux:button>
                    <flux:button :href="route('operador.login')" size="sm" wire:navigate>{{ __('Operador') }}</flux:button>
                    <flux:button :href="route('medico.login')" size="sm" wire:navigate>{{ __('Médico') }}</flux:button>
                </div>
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
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Operador — atendimento') }}</flux:heading>
                <flux:text class="text-sm">
                    <strong>{{ __('Meu guichê / Serviço') }}</strong> — {{ __('define de onde e qual fila você chama.') }}
                    <br><strong>{{ __('Chamar próxima') }}</strong> — {{ __('aplica intercalação e exibe no Painel.') }}
                    <br><strong>{{ __('Rechamar') }}</strong> — {{ __('repete a exibição no Painel.') }}
                    <br><strong>{{ __('Finalizar') }}</strong> — {{ __('encerra atendimento com sucesso.') }}
                    <br><strong>{{ __('Ausente') }}</strong> — {{ __('paciente não compareceu.') }}
                    <br><strong>{{ __('Transferir') }}</strong> — {{ __('envia a senha para outra fila mantendo prioridade.') }}
                    <br><strong>{{ __('Encaminhar') }}</strong> — {{ __('envia ao consultório com nome do paciente.') }}
                    <br><strong>{{ __('Modo Guichê / Consultório') }}</strong> — {{ __('alterna entre recepção e consultório.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Painel TV') }}</flux:heading>
                <flux:text class="text-sm">
                    {{ __('Atualiza automaticamente ao chamar senhas. Mostra a última chamada, guichê ou consultório, histórico e filas. Selecione a') }}
                    <strong>{{ __('Ala') }}</strong> {{ __('para que cada TV exiba apenas o setor desejado. Com nome do paciente informado, o painel anuncia por voz consultório e paciente; caso contrário, anuncia senha e destino.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Médico — consultório') }}</flux:heading>
                <flux:text class="text-sm">
                    {{ __('Acesso com CPF. Vê apenas a fila do consultório vinculado. Chama, rechama, finaliza e marca ausente.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Dashboard') }}</flux:heading>
                <flux:text class="text-sm">
                    {{ __('Indicadores do dia: total de senhas, filas aguardando, ausentes e guichês ativos. Relatórios detalhados ficam em Administração → Relatórios.') }}
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
                        <td class="py-3 pe-4 font-medium">{{ __('Alas') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Novo setor físico ou reorganização') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Marcar ala de consultório quando houver salas médicas') }}</td>
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
                        <td class="py-3 pe-4 font-medium">{{ __('Consultórios') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Nova sala de atendimento') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Vincular médico e serviços permitidos') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Médicos') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Profissional que atende no consultório') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('CPF único e consultório ativo') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Operadores') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Equipe da recepção/triagem') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('CPF e senha de acesso') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Intercalação') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Fila mista normal + preferencial') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Proporção legal (ex: 2:1)') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pe-4 font-medium">{{ __('Relatórios') }}</td>
                        <td class="py-3 pe-4 text-zinc-600 dark:text-zinc-400">{{ __('Análise histórica') }}</td>
                        <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ __('Período e serviço para comparar desempenho') }}</td>
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
                {{ __('A cada chamada, o sistema alterna entre senhas normais e preferenciais conforme a proporção configurada. Se um tipo acabar, chama o outro — a fila nunca trava.') }}
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
                {{ __('Calculada pela posição na fila e pelo tempo médio do serviço. É uma aproximação — ajuste o tempo médio conforme a operação real.') }}
            </flux:text>
        </flux:card>
    </article>

    <article id="manutencao" class="scroll-mt-8 space-y-4">
        <flux:heading size="lg">7. {{ __('Agendamentos e rotina') }}</flux:heading>
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Agendamentos na fila') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Consultas marcadas podem entrar automaticamente na fila cerca de 15 minutos antes do horário, quando essa integração estiver ativa na clínica. Sem agendamentos integrados, apenas senhas emitidas no Totem entram na fila.') }}
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
                <li class="flex gap-2"><span>☐</span> {{ __('Alas cadastradas (consultório marcado onde aplicável)') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Configurações da clínica salvas (nome, horários, ticker)') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Serviços, guichês, consultórios e médicos cadastrados') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Operadores cadastrados e treinados') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Intercalação definida em cada serviço com preferencial') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Totem e Painel abertos nos dispositivos corretos') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Painel com ala correta e voz habilitada (Iniciar painel)') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Teste: emitir senha → chamar → ver no Painel → finalizar') }}</li>
                <li class="flex gap-2"><span>☐</span> {{ __('Teste consultório: encaminhar com paciente → chamar → painel anuncia') }}</li>
            </ul>
        </flux:card>
        <div class="flex flex-wrap gap-3">
            <flux:button :href="route('admin.configuracoes')" variant="primary" wire:navigate>{{ __('Ir para configurações') }}</flux:button>
            <flux:button :href="route('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:button>
        </div>
    </article>
</section>

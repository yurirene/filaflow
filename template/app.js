/* ============================================================
   ClinicaFila — Sistema de Senhas
   app.js — VanillaJS SPA
   ============================================================ */

'use strict';

/* ============================================================
   ESTADO GLOBAL (simula dados vindos do backend)
   ============================================================ */
const STATE = {
  empresa_id: 'emp_001',
  clinicName: 'Clínica São Lucas',

  // Serviços cadastrados
  servicos: [
    { id: 'triagem', nome: 'Triagem',   prefixo: 'T', ala: 'Ala A', tMedio: 8,  cor: '#2563eb', ativo: true, icon: '🩺' },
    { id: 'coleta',  nome: 'Coleta',    prefixo: 'C', ala: 'Ala B', tMedio: 12, cor: '#0ea5e9', ativo: true, icon: '🧪' },
    { id: 'raio-x',  nome: 'Raio-X',   prefixo: 'R', ala: 'Ala C', tMedio: 20, cor: '#7c3aed', ativo: true, icon: '🔬' },
    { id: 'caixa',   nome: 'Caixa',    prefixo: 'X', ala: 'Ala D', tMedio: 6,  cor: '#16a34a', ativo: true, icon: '💳' },
  ],

  // Guichês
  guiches: [
    { id: 1, num: 1, desc: 'Guichê de Triagem',  servico: 'triagem', ativo: true },
    { id: 2, num: 2, desc: 'Guichê de Triagem',  servico: 'triagem', ativo: true },
    { id: 3, num: 3, desc: 'Guichê de Coleta',   servico: 'coleta',  ativo: true },
    { id: 4, num: 4, desc: 'Guichê de Raio-X',   servico: 'raio-x',  ativo: false },
    { id: 5, num: 5, desc: 'Guichê de Caixa',    servico: 'caixa',   ativo: true },
  ],

  // Filas por serviço: { [servicoId]: SenhaItem[] }
  filas: {
    triagem: [],
    coleta:  [],
    'raio-x': [],
    caixa:   [],
  },

  // Contadores de senha por serviço
  contadores: { triagem: 0, coleta: 0, 'raio-x': 0, caixa: 0 },

  // Histórico de chamadas (painel TV)
  historico: [],

  // Senha atualmente em atendimento no operador
  senhaAtual: null,
  timerAtual: null,
  timerSegundos: 0,

  // Prioridade selecionada no totem
  prioridadeSelecionada: 'normal',

  // Intercalação: { [servicoId]: { normais, preferenciais, cicloAtual } }
  intercalacao: {
    triagem: { normais: 2, preferenciais: 1, cicloAtual: 0 },
    coleta:  { normais: 2, preferenciais: 1, cicloAtual: 0 },
    'raio-x': { normais: 2, preferenciais: 1, cicloAtual: 0 },
    caixa:   { normais: 2, preferenciais: 1, cicloAtual: 0 },
  },

  // Operador atual
  operador: { nome: 'Ana Tereza', guiche: 3, servico: 'triagem' },

  // Estatísticas do turno
  stats: { atendidos: 0, ausentes: 0, tempos: [] },

  // Agendamentos do dia
  agendamentos: [
    { id: 'ag1', hora: '08:30', nome: 'Maria Silva',    servico: 'triagem', status: 'aguardando' },
    { id: 'ag2', hora: '09:00', nome: 'João Pereira',   servico: 'coleta',  status: 'na-fila'    },
    { id: 'ag3', hora: '09:30', nome: 'Carla Mendes',   servico: 'triagem', status: 'aguardando' },
    { id: 'ag4', hora: '10:00', nome: 'Roberto Lima',   servico: 'raio-x',  status: 'aguardando' },
    { id: 'ag5', hora: '10:30', nome: 'Fernanda Costa', servico: 'caixa',   status: 'atendido'   },
  ],

  // Relatórios / KPIs simulados
  kpis: {
    totalHoje: 47,
    tMedio: 9,
    emEspera: 12,
    ausentes: 3,
    pico: '10:00',
    guichesAtivos: 4,
  },

  // Configurações de notificação
  notificacoes: {
    whatsapp: { ativo: false, provider: 'z-api', antecedencia: 3 },
    sms:      { ativo: false, provider: 'twilio', antecedencia: 5 },
  },

  // Filtro de fila do operador
  queueFilter: 'all',

  // View atual
  currentView: 'totem',
};

/* ============================================================
   INICIALIZAÇÃO
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  initClock();
  initTotem();
  initPainel();
  initOperador();
  initAdmin();
  seedDemoData();
  startSimulation();
});

/* ============================================================
   RELÓGIO
   ============================================================ */
function initClock() {
  function tick() {
    const now = new Date();
    const hms = now.toLocaleTimeString('pt-BR');
    const hm  = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    const date = now.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });

    const navClock = document.getElementById('navClock');
    const painelClock = document.getElementById('painelClock');
    const painelDate  = document.getElementById('painelDate');

    if (navClock) navClock.textContent = hm;
    if (painelClock) painelClock.textContent = hms;
    if (painelDate) painelDate.textContent = date;
  }
  tick();
  setInterval(tick, 1000);
}

/* ============================================================
   NAVEGAÇÃO DE VIEWS
   ============================================================ */
function showView(viewId) {
  document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));

  const view = document.getElementById(`view-${viewId}`);
  const btn  = document.querySelector(`[data-view="${viewId}"]`);

  if (view) view.classList.remove('hidden');
  if (btn)  btn.classList.add('active');

  STATE.currentView = viewId;

  if (viewId === 'painel') refreshPainel();
  if (viewId === 'operador') refreshOperador();
  if (viewId === 'admin') refreshAdmin();
}

/* ============================================================
   TOTEM
   ============================================================ */
function initTotem() {
  document.getElementById('totemClinicName').textContent = STATE.clinicName;
  renderTotemServices();
}

function renderTotemServices() {
  const container = document.getElementById('totemServices');
  container.innerHTML = '';

  STATE.servicos.filter(s => s.ativo).forEach(svc => {
    const fila = STATE.filas[svc.id] || [];
    const espera = calcEspera(svc.id);
    const btn = document.createElement('button');
    btn.className = 'service-btn';
    btn.style.setProperty('--svc-color', svc.cor);
    btn.onclick = () => emitirSenha(svc.id);
    btn.innerHTML = `
      <span class="service-btn-icon">${svc.icon}</span>
      <span class="service-btn-name">${svc.nome}</span>
      <span class="service-btn-wait">⏱ ~${espera} min</span>
      <span class="service-btn-queue">${fila.length} na fila</span>
    `;
    container.appendChild(btn);
  });
}

function setPriority(tipo) {
  STATE.prioridadeSelecionada = tipo;
  document.querySelectorAll('.priority-btn').forEach(b => b.classList.remove('selected'));
  event.currentTarget.classList.add('selected');

  const msgs = {
    idoso:    '⚠ Atendimento preferencial — Idoso (Lei 10.741)',
    pcd:      '⚠ Atendimento preferencial — Pessoa com Deficiência',
    gestante: '⚠ Atendimento preferencial — Gestante',
    normal:   'Selecione um serviço para ver o tempo estimado',
  };
  document.getElementById('totemWaitText').textContent = msgs[tipo];
  renderTotemServices();
}

function emitirSenha(servicoId) {
  const svc = STATE.servicos.find(s => s.id === servicoId);
  if (!svc) return;

  const prioridade = STATE.prioridadeSelecionada;
  const isPreferencial = ['idoso', 'pcd', 'gestante'].includes(prioridade);

  STATE.contadores[servicoId] = (STATE.contadores[servicoId] || 0) + 1;
  const num = String(STATE.contadores[servicoId]).padStart(3, '0');
  const codigo = `${svc.prefixo}${num}`;

  const senha = {
    id:          `${servicoId}_${Date.now()}`,
    empresa_id:  STATE.empresa_id,
    codigo,
    servicoId,
    prioridade,
    isPreferencial,
    status:      'aguardando',
    emitidaEm:   new Date(),
    posicao:     calcPosicao(servicoId, isPreferencial),
  };

  // Insere na posição correta (preferenciais antes dos normais)
  if (isPreferencial) {
    const idx = STATE.filas[servicoId].findIndex(s => !s.isPreferencial);
    if (idx === -1) STATE.filas[servicoId].push(senha);
    else STATE.filas[servicoId].splice(idx, 0, senha);
  } else {
    STATE.filas[servicoId].push(senha);
  }

  // Exibe ticket
  showTicket(senha, svc);
  refreshPainelQueues();
  refreshOperadorQueue();
  updateKpiEmEspera();
}

function calcPosicao(servicoId, isPreferencial) {
  const fila = STATE.filas[servicoId];
  if (isPreferencial) {
    return fila.filter(s => s.isPreferencial).length + 1;
  }
  return fila.length + 1;
}

function calcEspera(servicoId) {
  const svc = STATE.servicos.find(s => s.id === servicoId);
  const fila = STATE.filas[servicoId] || [];
  return Math.max(1, fila.length * (svc ? svc.tMedio : 10));
}

function showTicket(senha, svc) {
  document.getElementById('totemHome').classList.add('hidden');
  document.getElementById('totemConfirm').classList.remove('hidden');

  const now = new Date();
  document.getElementById('ticketClinic').textContent = STATE.clinicName;
  document.getElementById('ticketDate').textContent = now.toLocaleDateString('pt-BR') + ' ' + now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  document.getElementById('ticketNumber').textContent = senha.codigo;
  document.getElementById('ticketService').textContent = svc.nome;
  document.getElementById('ticketWait').textContent = `~${calcEspera(svc.id)} min`;
  document.getElementById('ticketPosition').textContent = `${senha.posicao}ª`;

  const badge = document.getElementById('ticketPriorityBadge');
  const labels = { normal: 'Atendimento Normal', idoso: '👴 Idoso — Preferencial', pcd: '♿ PCD — Preferencial', gestante: '🤰 Gestante — Preferencial' };
  badge.textContent = labels[senha.prioridade] || 'Normal';
  badge.className = `ticket-priority-badge ${senha.prioridade}`;

  const smsMsg = document.getElementById('ticketSmsMsg');
  if (STATE.notificacoes.whatsapp.ativo || STATE.notificacoes.sms.ativo) {
    smsMsg.textContent = '📱 Você receberá uma notificação quando sua vez estiver próxima.';
  } else {
    smsMsg.textContent = '';
  }
}

function printTicket() {
  window.print();
}

function resetTotem() {
  STATE.prioridadeSelecionada = 'normal';
  document.querySelectorAll('.priority-btn').forEach(b => b.classList.remove('selected'));
  document.getElementById('totemWaitText').textContent = 'Selecione um serviço para ver o tempo estimado';
  document.getElementById('totemHome').classList.remove('hidden');
  document.getElementById('totemConfirm').classList.add('hidden');
  renderTotemServices();
}

/* ============================================================
   PAINEL TV
   ============================================================ */
function initPainel() {
  refreshPainel();
}

function refreshPainel() {
  document.getElementById('painelClinicName').textContent = STATE.clinicName;
  refreshPainelQueues();
  renderPainelHistory();
}

function refreshPainelQueues() {
  const servicos = ['triagem', 'coleta', 'raio-x', 'caixa'];
  const ids = { 'triagem': 'Triagem', 'coleta': 'Coleta', 'raio-x': 'RaioX', 'caixa': 'Caixa' };

  servicos.forEach(svcId => {
    const fila = STATE.filas[svcId] || [];
    const key = ids[svcId];
    const countEl = document.getElementById(`queueCount${key}`);
    const waitEl  = document.getElementById(`queueWait${key}`);
    if (countEl) countEl.textContent = fila.length;
    if (waitEl)  waitEl.textContent  = `~${calcEspera(svcId)} min`;
  });
}

function renderPainelHistory() {
  const list = document.getElementById('painelHistoryList');
  if (!list) return;
  list.innerHTML = '';

  STATE.historico.slice(0, 8).forEach((item, idx) => {
    const div = document.createElement('div');
    div.className = `history-item${idx === 0 ? ' first' : ''}`;
    div.innerHTML = `
      <span class="history-num">${item.codigo}</span>
      <div class="history-info">
        <div class="history-service">${item.servico}</div>
        <div class="history-guiche">Guichê ${item.guiche}</div>
      </div>
      <span class="history-time">${item.hora}</span>
    `;
    list.appendChild(div);
  });
}

function chamarNoPainel(codigo, servico, guiche) {
  document.getElementById('painelCurrentNumber').textContent = codigo;
  document.getElementById('painelCurrentService').textContent = servico;
  document.getElementById('painelCurrentGuiche').textContent = String(guiche).padStart(2, '0');

  // Animação de alerta
  const ring = document.getElementById('painelAlertRing');
  ring.classList.remove('animate');
  void ring.offsetWidth;
  ring.classList.add('animate');

  // Alerta sonoro
  playAlert();

  // Adiciona ao histórico
  const hora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  STATE.historico.unshift({ codigo, servico, guiche, hora });
  if (STATE.historico.length > 20) STATE.historico.pop();

  renderPainelHistory();
}

function changePainelAla(ala) {
  // Filtra cards de fila por ala
  const cards = document.querySelectorAll('.painel-queue-card');
  cards.forEach(card => {
    if (ala === 'all') {
      card.style.display = '';
    } else {
      const svcId = card.id.replace('queueCard', '').toLowerCase();
      const svc = STATE.servicos.find(s => s.id === svcId || s.id === svcId.replace('raio-x', 'raio-x'));
      card.style.display = (svc && svc.ala.toLowerCase().includes(ala)) ? '' : 'none';
    }
  });
}

/* ============================================================
   OPERADOR
   ============================================================ */
function initOperador() {
  refreshOperador();
}

function refreshOperador() {
  document.getElementById('opName').textContent = STATE.operador.nome;
  document.getElementById('opGuicheNum').textContent = String(STATE.operador.guiche).padStart(2, '0');
  const svc = STATE.servicos.find(s => s.id === STATE.operador.servico);
  document.getElementById('opServiceName').textContent = svc ? svc.nome : '';

  refreshOperadorStats();
  refreshOperadorQueue();
  renderAgendamentos();
  updateIntercalacaoBadge();
}

function refreshOperadorStats() {
  document.getElementById('opStatAtendidos').textContent = STATE.stats.atendidos;
  const tMedio = STATE.stats.tempos.length
    ? Math.round(STATE.stats.tempos.reduce((a, b) => a + b, 0) / STATE.stats.tempos.length)
    : '--';
  document.getElementById('opStatTMedio').textContent = tMedio === '--' ? '--' : `${tMedio}s`;
  const fila = STATE.filas[STATE.operador.servico] || [];
  document.getElementById('opStatFila').textContent = fila.length;
}

function refreshOperadorQueue() {
  const list = document.getElementById('opQueueList');
  if (!list) return;
  list.innerHTML = '';

  const fila = STATE.filas[STATE.operador.servico] || [];
  const filtered = STATE.queueFilter === 'all'
    ? fila
    : fila.filter(s => {
        if (STATE.queueFilter === 'preferencial') return s.isPreferencial;
        if (STATE.queueFilter === 'normal')       return !s.isPreferencial;
        if (STATE.queueFilter === 'agendado')     return s.agendado;
        return true;
      });

  if (filtered.length === 0) {
    list.innerHTML = '<div style="text-align:center;padding:24px;color:var(--text-muted);font-size:14px;">Fila vazia</div>';
    return;
  }

  filtered.forEach((senha, idx) => {
    const svc = STATE.servicos.find(s => s.id === senha.servicoId);
    const espera = new Date() - senha.emitidaEm;
    const minutos = Math.floor(espera / 60000);
    const badgeClass = senha.agendado ? 'badge-agendado' : (senha.isPreferencial ? 'badge-preferencial' : 'badge-normal');
    const badgeText  = senha.agendado ? 'Agendado' : (senha.isPreferencial ? getPrioridadeLabel(senha.prioridade) : 'Normal');

    const item = document.createElement('div');
    item.className = 'queue-item';
    item.innerHTML = `
      <div class="queue-item-pos">${idx + 1}</div>
      <div class="queue-item-num">${senha.codigo}</div>
      <div class="queue-item-info">
        <div class="queue-item-service">${svc ? svc.nome : senha.servicoId}</div>
        <div class="queue-item-time">Aguardando há ${minutos < 1 ? 'menos de 1' : minutos} min</div>
      </div>
      <span class="queue-item-badge ${badgeClass}">${badgeText}</span>
    `;
    list.appendChild(item);
  });

  document.getElementById('opStatFila').textContent = fila.length;
}

function getPrioridadeLabel(p) {
  const map = { idoso: '👴 Idoso', pcd: '♿ PCD', gestante: '🤰 Gestante' };
  return map[p] || 'Preferencial';
}

function renderAgendamentos() {
  const list = document.getElementById('opScheduleList');
  if (!list) return;
  list.innerHTML = '';

  STATE.agendamentos.forEach(ag => {
    const item = document.createElement('div');
    item.className = 'schedule-item';
    const statusClass = `status-${ag.status}`;
    const statusLabel = { aguardando: 'Aguardando', 'na-fila': 'Na Fila', atendido: 'Atendido' }[ag.status];
    item.innerHTML = `
      <span class="schedule-time">${ag.hora}</span>
      <div class="schedule-info">
        <div class="schedule-name">${ag.nome}</div>
        <div class="schedule-service">${ag.servico}</div>
      </div>
      <span class="schedule-status ${statusClass}">${statusLabel}</span>
    `;
    list.appendChild(item);
  });
}

function updateIntercalacaoBadge() {
  const ic = STATE.intercalacao[STATE.operador.servico];
  if (ic) {
    document.getElementById('intercalacaoBadge').textContent = `${ic.normais} Normal : ${ic.preferenciais} Preferencial`;
  }
}

// Determina próxima senha respeitando intercalação
function proximaSenhaIntercalada(servicoId) {
  const fila = STATE.filas[servicoId] || [];
  if (fila.length === 0) return null;

  const ic = STATE.intercalacao[servicoId];
  if (!ic) return fila[0];

  const normais       = fila.filter(s => !s.isPreferencial);
  const preferenciais = fila.filter(s => s.isPreferencial);

  if (preferenciais.length === 0) return normais[0] || null;
  if (normais.length === 0)       return preferenciais[0] || null;

  // Ciclo: a cada (normais) chamadas normais, chama (preferenciais) preferenciais
  const cicloTotal = ic.normais + ic.preferenciais;
  const posNoCiclo = ic.cicloAtual % cicloTotal;

  if (posNoCiclo < ic.normais) {
    return normais[0];
  } else {
    return preferenciais[0];
  }
}

function chamarProxima() {
  const servicoId = STATE.operador.servico;
  const senha = proximaSenhaIntercalada(servicoId);
  if (!senha) {
    showToast('Fila vazia para este serviço.', 'warning');
    return;
  }

  // Remove da fila
  STATE.filas[servicoId] = STATE.filas[servicoId].filter(s => s.id !== senha.id);

  // Avança ciclo de intercalação
  const ic = STATE.intercalacao[servicoId];
  if (ic) ic.cicloAtual++;

  // Define como atual
  STATE.senhaAtual = { ...senha, chamadaEm: new Date() };

  // Inicia timer
  clearInterval(STATE.timerAtual);
  STATE.timerSegundos = 0;
  STATE.timerAtual = setInterval(() => {
    STATE.timerSegundos++;
    const m = String(Math.floor(STATE.timerSegundos / 60)).padStart(2, '0');
    const s = String(STATE.timerSegundos % 60).padStart(2, '0');
    document.getElementById('opTimer').textContent = `${m}:${s}`;
  }, 1000);

  // Atualiza UI operador
  const svc = STATE.servicos.find(s => s.id === senha.servicoId);
  document.getElementById('opCurrentNumber').textContent = senha.codigo;
  document.getElementById('opCurrentService').textContent = svc ? svc.nome : '';
  const prioEl = document.getElementById('opCurrentPriority');
  prioEl.textContent = senha.isPreferencial ? getPrioridadeLabel(senha.prioridade) : 'Normal';
  prioEl.className = `op-current-priority ${senha.isPreferencial ? 'preferencial' : 'normal'}`;

  // Habilita botões
  setOperadorButtons(true);

  // Chama no painel TV
  chamarNoPainel(senha.codigo, svc ? svc.nome : '', STATE.operador.guiche);

  // Log
  addLog('call', `Chamou ${senha.codigo} — ${svc ? svc.nome : ''}`);

  // Atualiza filas
  refreshOperadorQueue();
  refreshPainelQueues();
  renderTotemServices();
}

function rechamarAtual() {
  if (!STATE.senhaAtual) return;
  const svc = STATE.servicos.find(s => s.id === STATE.senhaAtual.servicoId);
  chamarNoPainel(STATE.senhaAtual.codigo, svc ? svc.nome : '', STATE.operador.guiche);
  addLog('call', `Rechamou ${STATE.senhaAtual.codigo}`);
  showToast(`Senha ${STATE.senhaAtual.codigo} rechamada.`, 'info');
}

function finalizarAtendimento() {
  if (!STATE.senhaAtual) return;
  clearInterval(STATE.timerAtual);
  STATE.stats.atendidos++;
  STATE.stats.tempos.push(STATE.timerSegundos);
  STATE.kpis.totalHoje++;

  addLog('finish', `Finalizou ${STATE.senhaAtual.codigo} em ${STATE.timerSegundos}s`);
  showToast(`Atendimento de ${STATE.senhaAtual.codigo} finalizado.`, 'success');

  STATE.senhaAtual = null;
  STATE.timerSegundos = 0;
  document.getElementById('opCurrentNumber').textContent = '---';
  document.getElementById('opCurrentService').textContent = '—';
  document.getElementById('opCurrentPriority').textContent = '';
  document.getElementById('opTimer').textContent = '00:00';
  setOperadorButtons(false);
  refreshOperadorStats();
  updateKpiEmEspera();
}

function marcarAusente() {
  if (!STATE.senhaAtual) return;
  clearInterval(STATE.timerAtual);
  STATE.stats.ausentes++;
  STATE.kpis.ausentes++;

  addLog('absent', `Ausente: ${STATE.senhaAtual.codigo}`);
  showToast(`Senha ${STATE.senhaAtual.codigo} marcada como ausente.`, 'warning');

  STATE.senhaAtual = null;
  STATE.timerSegundos = 0;
  document.getElementById('opCurrentNumber').textContent = '---';
  document.getElementById('opCurrentService').textContent = '—';
  document.getElementById('opCurrentPriority').textContent = '';
  document.getElementById('opTimer').textContent = '00:00';
  setOperadorButtons(false);
  refreshOperadorStats();
}

function openTransferModal() {
  if (!STATE.senhaAtual) return;
  document.getElementById('transferSenhaNum').textContent = STATE.senhaAtual.codigo;
  document.getElementById('transferModal').classList.remove('hidden');
}

function confirmarTransferencia() {
  if (!STATE.senhaAtual) return;
  const destino = document.getElementById('transferServico').value;
  const motivo  = document.getElementById('transferMotivo').value;

  // Move para a fila de destino
  const senha = { ...STATE.senhaAtual, servicoId: destino, transferida: true };
  STATE.filas[destino].push(senha);

  clearInterval(STATE.timerAtual);
  addLog('transfer', `Transferiu ${STATE.senhaAtual.codigo} → ${destino}${motivo ? ` (${motivo})` : ''}`);
  showToast(`Senha ${STATE.senhaAtual.codigo} transferida para ${destino}.`, 'success');

  STATE.senhaAtual = null;
  STATE.timerSegundos = 0;
  document.getElementById('opCurrentNumber').textContent = '---';
  document.getElementById('opCurrentService').textContent = '—';
  document.getElementById('opCurrentPriority').textContent = '';
  document.getElementById('opTimer').textContent = '00:00';
  setOperadorButtons(false);

  closeModal('transferModal');
  refreshOperadorQueue();
  refreshPainelQueues();
}

function setOperadorButtons(enabled) {
  ['btnRechamar', 'btnTransferir', 'btnFinalizar', 'btnAusente'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.disabled = !enabled;
  });
}

function filterQueue(tipo, btn) {
  STATE.queueFilter = tipo;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  refreshOperadorQueue();
}

function changeOpGuiche(val) {
  STATE.operador.guiche = parseInt(val);
  document.getElementById('opGuicheNum').textContent = String(STATE.operador.guiche).padStart(2, '0');
}

function changeOpService(val) {
  STATE.operador.servico = val;
  const svc = STATE.servicos.find(s => s.id === val);
  document.getElementById('opServiceName').textContent = svc ? svc.nome : '';
  refreshOperadorQueue();
  updateIntercalacaoBadge();
}

function addAgendamentoFila() {
  const ag = STATE.agendamentos.find(a => a.status === 'aguardando');
  if (!ag) { showToast('Nenhum agendamento pendente.', 'info'); return; }

  ag.status = 'na-fila';
  const svc = STATE.servicos.find(s => s.id === ag.servico);
  STATE.contadores[ag.servico] = (STATE.contadores[ag.servico] || 0) + 1;
  const num = String(STATE.contadores[ag.servico]).padStart(3, '0');
  const codigo = `${svc ? svc.prefixo : 'A'}${num}`;

  const senha = {
    id: `ag_${Date.now()}`,
    empresa_id: STATE.empresa_id,
    codigo,
    servicoId: ag.servico,
    prioridade: 'normal',
    isPreferencial: false,
    agendado: true,
    agendamentoId: ag.id,
    nomePaciente: ag.nome,
    status: 'aguardando',
    emitidaEm: new Date(),
    posicao: 1,
  };

  // Agendados entram na frente dos normais mas após preferenciais
  const fila = STATE.filas[ag.servico];
  const idxNormal = fila.findIndex(s => !s.isPreferencial && !s.agendado);
  if (idxNormal === -1) fila.push(senha);
  else fila.splice(idxNormal, 0, senha);

  renderAgendamentos();
  refreshOperadorQueue();
  refreshPainelQueues();
  showToast(`${ag.nome} adicionado à fila com senha ${codigo}.`, 'success');
}

function addLog(tipo, texto) {
  const list = document.getElementById('opLogList');
  if (!list) return;

  const hora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  const item = document.createElement('div');
  item.className = `log-item log-${tipo}`;
  item.innerHTML = `
    <div class="log-time">${hora}</div>
    <div class="log-text">${texto}</div>
  `;
  list.insertBefore(item, list.firstChild);

  // Limita a 50 itens
  while (list.children.length > 50) list.removeChild(list.lastChild);
}

function clearLog() {
  const list = document.getElementById('opLogList');
  if (list) list.innerHTML = '';
}

/* ============================================================
   ADMIN
   ============================================================ */
function initAdmin() {
  renderServicosTable();
  renderGuichesGrid();
  renderIntercalacaoTable();
  renderOperatorsTable();
  updateKpis();
  drawCharts();
}

function refreshAdmin() {
  updateKpis();
  renderOperatorsTable();
}

function showAdminTab(tab) {
  document.querySelectorAll('.admin-tab').forEach(t => t.classList.add('hidden'));
  document.querySelectorAll('.admin-menu-btn').forEach(b => b.classList.remove('active'));

  const tabEl = document.getElementById(`adminTab-${tab}`);
  if (tabEl) tabEl.classList.remove('hidden');

  const btns = document.querySelectorAll('.admin-menu-btn');
  btns.forEach(b => { if (b.textContent.toLowerCase().includes(tab.substring(0, 4))) b.classList.add('active'); });
}

function updateKpis() {
  const k = STATE.kpis;
  document.getElementById('kpiTotalHoje').textContent = k.totalHoje;
  document.getElementById('kpiTMedio').textContent = `${k.tMedio} min`;
  document.getElementById('kpiEmEspera').textContent = k.emEspera;
  document.getElementById('kpiAusentes').textContent = k.ausentes;
  document.getElementById('kpiPico').textContent = k.pico;
  document.getElementById('kpiGuichesAtivos').textContent = STATE.guiches.filter(g => g.ativo).length;
}

function updateKpiEmEspera() {
  const total = Object.values(STATE.filas).reduce((acc, f) => acc + f.length, 0);
  STATE.kpis.emEspera = total;
  document.getElementById('kpiEmEspera').textContent = total;
}

function renderServicosTable() {
  const tbody = document.getElementById('servicosTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  STATE.servicos.forEach(svc => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><span style="display:inline-flex;align-items:center;gap:8px;">
        <span style="width:10px;height:10px;border-radius:50%;background:${svc.cor};display:inline-block;"></span>
        <strong>${svc.nome}</strong>
      </span></td>
      <td><code>${svc.prefixo}</code></td>
      <td>${svc.ala}</td>
      <td>${svc.tMedio} min</td>
      <td><span class="badge ${svc.ativo ? 'badge-active' : 'badge-inactive'}">${svc.ativo ? 'Ativo' : 'Inativo'}</span></td>
      <td>
        <button class="btn-icon" onclick="editServico('${svc.id}')">✏</button>
        <button class="btn-icon danger" onclick="toggleServico('${svc.id}')">⊘</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function renderGuichesGrid() {
  const grid = document.getElementById('guichesGrid');
  if (!grid) return;
  grid.innerHTML = '';

  STATE.guiches.forEach(g => {
    const card = document.createElement('div');
    card.className = 'guiche-card';
    const svc = STATE.servicos.find(s => s.id === g.servico);
    card.innerHTML = `
      <div class="guiche-card-num">${String(g.num).padStart(2, '0')}</div>
      <div class="guiche-card-desc">${g.desc}</div>
      <div class="guiche-card-service">${svc ? svc.nome : g.servico}</div>
      <span class="guiche-card-status ${g.ativo ? 'guiche-status-ativo' : 'guiche-status-inativo'}">${g.ativo ? 'Ativo' : 'Inativo'}</span>
      <div class="guiche-card-actions">
        <button class="btn-icon" onclick="toggleGuiche(${g.id})">⊘</button>
        <button class="btn-icon danger" onclick="removeGuiche(${g.id})">✕</button>
      </div>
    `;
    grid.appendChild(card);
  });
}

function renderIntercalacaoTable() {
  const tbody = document.getElementById('intercalacaoTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  Object.entries(STATE.intercalacao).forEach(([svcId, ic]) => {
    const svc = STATE.servicos.find(s => s.id === svcId);
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${svc ? svc.nome : svcId}</td>
      <td>${ic.normais} Normal : ${ic.preferenciais} Preferencial</td>
      <td><span class="badge badge-active">Ativo</span></td>
      <td><button class="btn-icon" onclick="editIntercalacao('${svcId}')">✏</button></td>
    `;
    tbody.appendChild(tr);
  });

  renderIntercalacaoPreview();
}

function renderIntercalacaoPreview() {
  const preview = document.getElementById('intercalacaoPreview');
  if (!preview) return;

  const n = parseInt(document.getElementById('intNormais')?.value || 2);
  const p = parseInt(document.getElementById('intPreferenciais')?.value || 1);

  let html = '<div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px;">Sequência de chamada (próximos 10):</div>';
  html += '<div class="preview-sequence">';

  let ciclo = 0;
  for (let i = 0; i < 10; i++) {
    const pos = ciclo % (n + p);
    const isNormal = pos < n;
    html += `<div class="preview-token ${isNormal ? 'normal' : 'pref'}">${isNormal ? 'N' : 'P'}</div>`;
    ciclo++;
  }

  html += '</div>';
  preview.innerHTML = html;
}

function renderOperatorsTable() {
  const tbody = document.getElementById('operatorTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const operadores = [
    { nome: 'Ana Tereza',    guiche: 3, servico: 'Triagem', atendidos: STATE.stats.atendidos, tMedio: STATE.stats.tempos.length ? Math.round(STATE.stats.tempos.reduce((a,b)=>a+b,0)/STATE.stats.tempos.length/60) : 0, ausentes: STATE.stats.ausentes, status: 'ativo' },
    { nome: 'Carlos Souza',  guiche: 1, servico: 'Triagem', atendidos: 12, tMedio: 8, ausentes: 1, status: 'ativo'   },
    { nome: 'Beatriz Lima',  guiche: 2, servico: 'Coleta',  atendidos: 9,  tMedio: 11, ausentes: 0, status: 'ativo'  },
    { nome: 'Diego Martins', guiche: 4, servico: 'Raio-X',  atendidos: 5,  tMedio: 18, ausentes: 2, status: 'inativo'},
  ];

  operadores.forEach(op => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><strong>${op.nome}</strong></td>
      <td>${String(op.guiche).padStart(2,'0')}</td>
      <td>${op.servico}</td>
      <td>${op.atendidos}</td>
      <td>${op.tMedio} min</td>
      <td>${op.ausentes}</td>
      <td><span class="badge ${op.status === 'ativo' ? 'badge-active' : 'badge-inactive'}">${op.status === 'ativo' ? 'Ativo' : 'Inativo'}</span></td>
    `;
    tbody.appendChild(tr);
  });
}

/* ---- Ações de serviços ---- */
function openServiceModal(svcId) {
  document.getElementById('serviceModalTitle').textContent = svcId ? 'Editar Serviço' : 'Novo Serviço';
  if (svcId) {
    const svc = STATE.servicos.find(s => s.id === svcId);
    if (svc) {
      document.getElementById('svcNome').value = svc.nome;
      document.getElementById('svcPrefixo').value = svc.prefixo;
      document.getElementById('svcAla').value = svc.ala;
      document.getElementById('svcTMedio').value = svc.tMedio;
      document.getElementById('svcCor').value = svc.cor;
      document.getElementById('svcAtivo').checked = svc.ativo;
    }
  } else {
    document.getElementById('svcNome').value = '';
    document.getElementById('svcPrefixo').value = '';
    document.getElementById('svcAla').value = '';
    document.getElementById('svcTMedio').value = 10;
    document.getElementById('svcCor').value = '#2563eb';
    document.getElementById('svcAtivo').checked = true;
  }
  document.getElementById('serviceModal').classList.remove('hidden');
}

function salvarServico() {
  const nome     = document.getElementById('svcNome').value.trim();
  const prefixo  = document.getElementById('svcPrefixo').value.trim().toUpperCase();
  const ala      = document.getElementById('svcAla').value.trim();
  const tMedio   = parseInt(document.getElementById('svcTMedio').value);
  const cor      = document.getElementById('svcCor').value;
  const ativo    = document.getElementById('svcAtivo').checked;

  if (!nome || !prefixo) { showToast('Preencha nome e prefixo.', 'warning'); return; }

  const id = nome.toLowerCase().replace(/\s+/g, '-');
  const exists = STATE.servicos.find(s => s.id === id);

  if (exists) {
    Object.assign(exists, { nome, prefixo, ala, tMedio, cor, ativo });
  } else {
    STATE.servicos.push({ id, nome, prefixo, ala, tMedio, cor, ativo, icon: '🏥' });
    STATE.filas[id] = [];
    STATE.contadores[id] = 0;
    STATE.intercalacao[id] = { normais: 2, preferenciais: 1, cicloAtual: 0 };
  }

  renderServicosTable();
  renderTotemServices();
  closeModal('serviceModal');
  showToast(`Serviço "${nome}" salvo com sucesso.`, 'success');
}

function editServico(id) { openServiceModal(id); }

function toggleServico(id) {
  const svc = STATE.servicos.find(s => s.id === id);
  if (svc) {
    svc.ativo = !svc.ativo;
    renderServicosTable();
    renderTotemServices();
    showToast(`Serviço "${svc.nome}" ${svc.ativo ? 'ativado' : 'desativado'}.`, 'info');
  }
}

/* ---- Ações de guichês ---- */
function openGuicheModal() {
  document.getElementById('guicheModal').classList.remove('hidden');
}

function salvarGuiche() {
  const num   = parseInt(document.getElementById('guicheNum').value);
  const desc  = document.getElementById('guicheDesc').value.trim();
  const svc   = document.getElementById('guicheServico').value;

  if (!num) { showToast('Informe o número do guichê.', 'warning'); return; }

  STATE.guiches.push({ id: Date.now(), num, desc: desc || `Guichê ${num}`, servico: svc, ativo: true });
  renderGuichesGrid();
  closeModal('guicheModal');
  showToast(`Guichê ${num} criado.`, 'success');
}

function toggleGuiche(id) {
  const g = STATE.guiches.find(g => g.id === id);
  if (g) {
    g.ativo = !g.ativo;
    renderGuichesGrid();
    showToast(`Guichê ${g.num} ${g.ativo ? 'ativado' : 'desativado'}.`, 'info');
  }
}

function removeGuiche(id) {
  STATE.guiches = STATE.guiches.filter(g => g.id !== id);
  renderGuichesGrid();
  showToast('Guichê removido.', 'info');
}

/* ---- Intercalação ---- */
function salvarIntercalacao() {
  const svcId = document.getElementById('intServico').value;
  const n     = parseInt(document.getElementById('intNormais').value);
  const p     = parseInt(document.getElementById('intPreferenciais').value);

  const rule = { normais: n, preferenciais: p, cicloAtual: 0 };

  if (svcId === 'all') {
    Object.keys(STATE.intercalacao).forEach(k => { STATE.intercalacao[k] = { ...rule }; });
  } else {
    STATE.intercalacao[svcId] = rule;
  }

  renderIntercalacaoTable();
  updateIntercalacaoBadge();
  showToast('Regra de intercalação salva.', 'success');
}

function editIntercalacao(svcId) {
  document.getElementById('intServico').value = svcId;
  const ic = STATE.intercalacao[svcId];
  if (ic) {
    document.getElementById('intNormais').value = ic.normais;
    document.getElementById('intPreferenciais').value = ic.preferenciais;
  }
  renderIntercalacaoPreview();
}

/* ---- Notificações ---- */
function toggleNotif(tipo, ativo) {
  STATE.notificacoes[tipo].ativo = ativo;
  showToast(`Notificação por ${tipo === 'whatsapp' ? 'WhatsApp' : 'SMS'} ${ativo ? 'ativada' : 'desativada'}.`, 'info');
}

function salvarNotifWhatsapp() {
  const token = document.getElementById('whatsappToken').value;
  const ant   = parseInt(document.getElementById('whatsappAntecedencia').value);
  STATE.notificacoes.whatsapp.antecedencia = ant;
  showToast('Configurações de WhatsApp salvas.', 'success');
}

function salvarNotifSms() {
  const ant = parseInt(document.getElementById('smsAntecedencia').value);
  STATE.notificacoes.sms.antecedencia = ant;
  showToast('Configurações de SMS salvas.', 'success');
}

function testarNotif(tipo) {
  showToast(`Mensagem de teste enviada via ${tipo === 'whatsapp' ? 'WhatsApp' : 'SMS'}.`, 'success');
}

/* ---- Relatórios ---- */
function gerarRelatorio() {
  const periodo  = document.getElementById('relPeriodo').value;
  const servico  = document.getElementById('relServico').value;
  const operador = document.getElementById('relOperador').value;

  const result = document.getElementById('relatorioResult');
  result.innerHTML = `
    <h3 style="margin-bottom:16px;font-size:16px;font-weight:700;">Relatório — ${periodo} / ${servico === 'all' ? 'Todos os Serviços' : servico}</h3>
    <table class="admin-table">
      <thead>
        <tr><th>Serviço</th><th>Atendidos</th><th>T. Médio</th><th>Ausentes</th><th>Pico</th></tr>
      </thead>
      <tbody>
        <tr><td>Triagem</td><td>23</td><td>8 min</td><td>2</td><td>10:00</td></tr>
        <tr><td>Coleta</td><td>15</td><td>11 min</td><td>1</td><td>09:30</td></tr>
        <tr><td>Raio-X</td><td>6</td><td>18 min</td><td>0</td><td>11:00</td></tr>
        <tr><td>Caixa</td><td>18</td><td>5 min</td><td>1</td><td>08:30</td></tr>
      </tbody>
    </table>
  `;
}

function exportarRelatorio() {
  const csv = 'Serviço,Atendidos,T.Médio,Ausentes,Pico\nTriagem,23,8,2,10:00\nColeta,15,11,1,09:30\nRaio-X,6,18,0,11:00\nCaixa,18,5,1,08:30';
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'relatorio.csv'; a.click();
  URL.revokeObjectURL(url);
  showToast('Relatório exportado como CSV.', 'success');
}

/* ---- Configurações ---- */
function salvarConfiguracoes() {
  const nome   = document.getElementById('configClinicName').value.trim();
  const ticker = document.getElementById('configTicker').value.trim();

  if (nome) {
    STATE.clinicName = nome;
    document.getElementById('tenantName').textContent = nome;
    document.getElementById('totemClinicName').textContent = nome;
    document.getElementById('painelClinicName').textContent = nome;
    document.getElementById('ticketClinic').textContent = nome;
  }

  if (ticker) {
    document.getElementById('tickerContent').textContent = ticker + ' • ';
  }

  showToast('Configurações salvas com sucesso.', 'success');
}

/* ============================================================
   GRÁFICOS (Canvas puro)
   ============================================================ */
function drawCharts() {
  drawBarChart();
  drawPieChart();
}

function drawBarChart() {
  const canvas = document.getElementById('chartHora');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;

  const data = [3, 8, 12, 15, 18, 14, 10, 7, 5, 3, 2, 1];
  const labels = ['07h','08h','09h','10h','11h','12h','13h','14h','15h','16h','17h','18h'];
  const max = Math.max(...data);
  const pad = { top: 20, right: 20, bottom: 30, left: 30 };
  const chartW = W - pad.left - pad.right;
  const chartH = H - pad.top - pad.bottom;
  const barW = chartW / data.length - 6;

  ctx.clearRect(0, 0, W, H);

  // Grid lines
  ctx.strokeStyle = '#e2e8f0';
  ctx.lineWidth = 1;
  for (let i = 0; i <= 4; i++) {
    const y = pad.top + (chartH / 4) * i;
    ctx.beginPath();
    ctx.moveTo(pad.left, y);
    ctx.lineTo(W - pad.right, y);
    ctx.stroke();
  }

  // Bars
  data.forEach((val, i) => {
    const x = pad.left + i * (chartW / data.length) + 3;
    const barH = (val / max) * chartH;
    const y = pad.top + chartH - barH;

    const grad = ctx.createLinearGradient(0, y, 0, y + barH);
    grad.addColorStop(0, '#2563eb');
    grad.addColorStop(1, '#93c5fd');
    ctx.fillStyle = grad;
    ctx.beginPath();
    ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]);
    ctx.fill();

    // Label
    ctx.fillStyle = '#94a3b8';
    ctx.font = '10px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(labels[i], x + barW / 2, H - 8);
  });
}

function drawPieChart() {
  const canvas = document.getElementById('chartServico');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;

  const data = [
    { label: 'Triagem', value: 23, color: '#2563eb' },
    { label: 'Coleta',  value: 15, color: '#0ea5e9' },
    { label: 'Raio-X',  value: 6,  color: '#7c3aed' },
    { label: 'Caixa',   value: 18, color: '#16a34a' },
  ];

  const total = data.reduce((a, d) => a + d.value, 0);
  const cx = W / 2 - 30, cy = H / 2, r = Math.min(cx, cy) - 20;

  ctx.clearRect(0, 0, W, H);

  let startAngle = -Math.PI / 2;
  data.forEach(d => {
    const slice = (d.value / total) * 2 * Math.PI;
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, startAngle, startAngle + slice);
    ctx.closePath();
    ctx.fillStyle = d.color;
    ctx.fill();
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.stroke();
    startAngle += slice;
  });

  // Legenda
  data.forEach((d, i) => {
    const lx = W - 80, ly = 20 + i * 28;
    ctx.fillStyle = d.color;
    ctx.fillRect(lx, ly, 12, 12);
    ctx.fillStyle = '#475569';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(`${d.label} (${d.value})`, lx + 16, ly + 10);
  });
}

/* ============================================================
   ÁUDIO
   ============================================================ */
function playAlert() {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    const ctx = new AudioCtx();

    const notes = [523.25, 659.25, 783.99]; // C5, E5, G5
    notes.forEach((freq, i) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = freq;
      osc.type = 'sine';
      gain.gain.setValueAtTime(0, ctx.currentTime + i * 0.15);
      gain.gain.linearRampToValueAtTime(0.3, ctx.currentTime + i * 0.15 + 0.05);
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + i * 0.15 + 0.25);
      osc.start(ctx.currentTime + i * 0.15);
      osc.stop(ctx.currentTime + i * 0.15 + 0.3);
    });
  } catch (e) {
    // Áudio não disponível
  }
}

/* ============================================================
   MODAIS
   ============================================================ */
function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
}

// Fecha modal ao clicar fora
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.add('hidden');
  }
});

/* ============================================================
   TOAST
   ============================================================ */
function showToast(msg, tipo = 'info') {
  const icons = { success: '✓', warning: '⚠', error: '✕', info: 'ℹ' };
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast ${tipo}`;
  toast.innerHTML = `
    <span class="toast-icon">${icons[tipo] || 'ℹ'}</span>
    <span class="toast-text">${msg}</span>
    <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
  `;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

/* ============================================================
   DADOS DE DEMONSTRAÇÃO
   ============================================================ */
function seedDemoData() {
  // Popula filas com senhas de demonstração
  const prioridades = ['normal', 'normal', 'normal', 'idoso', 'normal', 'pcd', 'normal', 'gestante'];

  STATE.servicos.forEach(svc => {
    const qtd = Math.floor(Math.random() * 6) + 2;
    for (let i = 0; i < qtd; i++) {
      STATE.contadores[svc.id]++;
      const num = String(STATE.contadores[svc.id]).padStart(3, '0');
      const prio = prioridades[Math.floor(Math.random() * prioridades.length)];
      const isPref = ['idoso', 'pcd', 'gestante'].includes(prio);
      const senha = {
        id: `${svc.id}_demo_${i}`,
        empresa_id: STATE.empresa_id,
        codigo: `${svc.prefixo}${num}`,
        servicoId: svc.id,
        prioridade: prio,
        isPreferencial: isPref,
        status: 'aguardando',
        emitidaEm: new Date(Date.now() - Math.random() * 1800000),
        posicao: i + 1,
      };
      STATE.filas[svc.id].push(senha);
    }
  });

  // Histórico de demonstração
  STATE.historico = [
    { codigo: 'T012', servico: 'Triagem', guiche: 1, hora: '10:45' },
    { codigo: 'C008', servico: 'Coleta',  guiche: 3, hora: '10:43' },
    { codigo: 'T011', servico: 'Triagem', guiche: 2, hora: '10:40' },
    { codigo: 'X005', servico: 'Caixa',   guiche: 5, hora: '10:38' },
    { codigo: 'R003', servico: 'Raio-X',  guiche: 4, hora: '10:35' },
  ];

  refreshPainelQueues();
  refreshOperadorQueue();
  updateKpiEmEspera();
  renderPainelHistory();
}

/* ============================================================
   SIMULAÇÃO EM TEMPO REAL
   ============================================================ */
function startSimulation() {
  // Simula chegada de novos pacientes a cada 15-30s
  setInterval(() => {
    const svc = STATE.servicos[Math.floor(Math.random() * STATE.servicos.length)];
    if (!svc.ativo) return;

    STATE.contadores[svc.id]++;
    const num = String(STATE.contadores[svc.id]).padStart(3, '0');
    const prios = ['normal', 'normal', 'normal', 'idoso', 'pcd'];
    const prio = prios[Math.floor(Math.random() * prios.length)];
    const isPref = ['idoso', 'pcd', 'gestante'].includes(prio);

    STATE.filas[svc.id].push({
      id: `${svc.id}_sim_${Date.now()}`,
      empresa_id: STATE.empresa_id,
      codigo: `${svc.prefixo}${num}`,
      servicoId: svc.id,
      prioridade: prio,
      isPreferencial: isPref,
      status: 'aguardando',
      emitidaEm: new Date(),
      posicao: STATE.filas[svc.id].length + 1,
    });

    refreshPainelQueues();
    refreshOperadorQueue();
    updateKpiEmEspera();
    renderTotemServices();
  }, 20000 + Math.random() * 15000);

  // Atualiza gráficos a cada 30s
  setInterval(drawCharts, 30000);
}

/* ============================================================
   INTERCALAÇÃO — preview ao digitar
   ============================================================ */
document.addEventListener('input', e => {
  if (e.target.id === 'intNormais' || e.target.id === 'intPreferenciais') {
    renderIntercalacaoPreview();
  }
});

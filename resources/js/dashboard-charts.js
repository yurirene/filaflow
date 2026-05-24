function parseChartPayload(raw) {
    if (! raw) {
        return [];
    }

    try {
        return JSON.parse(raw);
    } catch {
        return [];
    }
}

function setupCanvas(canvas) {
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = Math.max(rect.width, 280);
    const height = Math.max(rect.height, 180);

    canvas.width = Math.floor(width * dpr);
    canvas.height = Math.floor(height * dpr);

    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    return { ctx, width, height };
}

function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function drawEmptyState(ctx, width, height, message) {
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = isDarkMode() ? '#71717a' : '#94a3b8';
    ctx.font = '13px Inter, system-ui, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(message, width / 2, height / 2);
}

function drawBarChart(canvas, data) {
    const { ctx, width, height } = setupCanvas(canvas);

    if (! data.length || data.every((item) => item.value === 0)) {
        drawEmptyState(ctx, width, height, 'Sem atendimentos hoje');

        return;
    }

    const values = data.map((item) => item.value);
    const labels = data.map((item) => item.label);
    const max = Math.max(...values, 1);
    const pad = { top: 16, right: 12, bottom: 28, left: 28 };
    const chartW = width - pad.left - pad.right;
    const chartH = height - pad.top - pad.bottom;
    const barW = Math.max(8, chartW / data.length - 6);

    ctx.clearRect(0, 0, width, height);

    ctx.strokeStyle = isDarkMode() ? '#3f3f46' : '#e2e8f0';
    ctx.lineWidth = 1;

    for (let i = 0; i <= 4; i++) {
        const y = pad.top + (chartH / 4) * i;
        ctx.beginPath();
        ctx.moveTo(pad.left, y);
        ctx.lineTo(width - pad.right, y);
        ctx.stroke();
    }

    data.forEach((item, i) => {
        const x = pad.left + i * (chartW / data.length) + 3;
        const barH = (item.value / max) * chartH;
        const y = pad.top + chartH - barH;

        const grad = ctx.createLinearGradient(0, y, 0, y + barH);
        grad.addColorStop(0, '#2563eb');
        grad.addColorStop(1, '#93c5fd');
        ctx.fillStyle = grad;

        if (typeof ctx.roundRect === 'function') {
            ctx.beginPath();
            ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]);
            ctx.fill();
        } else {
            ctx.fillRect(x, y, barW, barH);
        }

        if (item.value > 0) {
            ctx.fillStyle = isDarkMode() ? '#e4e4e7' : '#475569';
            ctx.font = '10px Inter, system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(String(item.value), x + barW / 2, y - 4);
        }

        ctx.fillStyle = isDarkMode() ? '#a1a1aa' : '#94a3b8';
        ctx.font = '10px Inter, system-ui, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(labels[i], x + barW / 2, height - 8);
    });
}

function drawPieChart(canvas, data) {
    const { ctx, width, height } = setupCanvas(canvas);
    const total = data.reduce((sum, item) => sum + item.value, 0);

    if (! total) {
        drawEmptyState(ctx, width, height, 'Sem atendimentos hoje');

        return;
    }

    ctx.clearRect(0, 0, width, height);

    const legendWidth = Math.min(120, width * 0.35);
    const cx = (width - legendWidth) / 2;
    const cy = height / 2;
    const r = Math.min(cx, cy) - 16;

    let startAngle = -Math.PI / 2;

    data.forEach((item) => {
        const slice = (item.value / total) * 2 * Math.PI;

        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, startAngle, startAngle + slice);
        ctx.closePath();
        ctx.fillStyle = item.color;
        ctx.fill();
        ctx.strokeStyle = isDarkMode() ? '#27272a' : '#ffffff';
        ctx.lineWidth = 2;
        ctx.stroke();

        startAngle += slice;
    });

    const lx = width - legendWidth + 8;

    data.forEach((item, i) => {
        const ly = 18 + i * 26;

        ctx.fillStyle = item.color;
        ctx.fillRect(lx, ly, 12, 12);

        ctx.fillStyle = isDarkMode() ? '#d4d4d8' : '#475569';
        ctx.font = '11px Inter, system-ui, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`${item.label} (${item.value})`, lx + 18, ly + 10);
    });
}

function renderDashboardCharts(root) {
    if (! root) {
        return;
    }

    const porHora = parseChartPayload(root.dataset.porHora);
    const porServico = parseChartPayload(root.dataset.porServico);
    const barCanvas = root.querySelector('#chartHora');
    const pieCanvas = root.querySelector('#chartServico');

    if (barCanvas) {
        drawBarChart(barCanvas, porHora);
    }

    if (pieCanvas) {
        drawPieChart(pieCanvas, porServico);
    }
}

function bindDashboardCharts() {
    const root = document.getElementById('dashboard-charts');

    if (! root) {
        return;
    }

    renderDashboardCharts(root);

    if (window.ResizeObserver && root.dataset.chartsObserved !== '1') {
        root.dataset.chartsObserved = '1';

        const observer = new ResizeObserver(() => renderDashboardCharts(root));
        root.querySelectorAll('canvas').forEach((canvas) => observer.observe(canvas));
    }
}

document.addEventListener('DOMContentLoaded', bindDashboardCharts);
document.addEventListener('livewire:navigated', bindDashboardCharts);

export { renderDashboardCharts, bindDashboardCharts };

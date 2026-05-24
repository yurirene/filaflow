function eventoAlaMatchesPainel(component, marker, eventAlaId) {
    if (! marker.dataset.filterAla) {
        return true;
    }

    const painelAla = component.$wire.get('ala');

    if (painelAla === 'all') {
        return true;
    }

    if (eventAlaId == null) {
        return true;
    }

    return String(eventAlaId) === String(painelAla);
}

function bindFilaEcho(component) {
    if (! window.Echo) {
        return;
    }

    const marker = component.el.querySelector('[data-fila-echo]');

    if (! marker || marker.dataset.filaEchoBound === '1') {
        return;
    }

    marker.dataset.filaEchoBound = '1';

    const onFila = marker.dataset.onFila || 'onFilaAtualizada';
    const onSenha = marker.dataset.onSenha;

    const channel = window.Echo.channel('fila');

    channel.listen('.fila.atualizada', () => component.$wire.call(onFila));

    if (onSenha) {
        channel.listen('.senha.chamada', (event) => {
            if (! eventoAlaMatchesPainel(component, marker, event.alaId)) {
                return;
            }

            component.$wire.call(onSenha, event.alaId ?? null);
        });
    }
}

function tryBindFilaEcho(component) {
    if (window.Echo) {
        bindFilaEcho(component);

        return;
    }

    window.addEventListener('filaflow:echo-ready', () => bindFilaEcho(component), { once: true });
}

document.addEventListener('livewire:init', () => {
    Livewire.hook('component.init', ({ component }) => {
        if (! component.el.querySelector('[data-fila-echo]')) {
            return;
        }

        tryBindFilaEcho(component);
    });
});

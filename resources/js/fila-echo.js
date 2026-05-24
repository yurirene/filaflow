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
        channel.listen('.senha.chamada', () => component.$wire.call(onSenha));
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

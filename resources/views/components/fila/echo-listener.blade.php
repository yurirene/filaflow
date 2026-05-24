@props([
    'onFila' => 'onFilaAtualizada',
    'onSenha' => null,
])

<div
    aria-hidden="true"
    class="hidden"
    wire:ignore
    data-fila-echo
    data-on-fila="{{ $onFila }}"
    @if ($onSenha) data-on-senha="{{ $onSenha }}" @endif
></div>

@props(['chamadaEm' => null])

@php
    $timestamp = $chamadaEm?->getTimestamp();
@endphp

<span {{ $attributes->merge(['class' => 'timer-value']) }}>
    @if ($timestamp)
        <span
            x-data="{
                secs: 0,
                format() {
                    const m = String(Math.floor(this.secs / 60)).padStart(2, '0');
                    const s = String(this.secs % 60).padStart(2, '0');
                    return `${m}:${s}`;
                },
                tick() {
                    this.secs = Math.max(0, Math.floor(Date.now() / 1000) - {{ $timestamp }});
                },
                init() {
                    this.tick();
                    setInterval(() => this.tick(), 1000);
                },
            }"
            x-text="format()"
        ></span>
    @else
        00:00
    @endif
</span>

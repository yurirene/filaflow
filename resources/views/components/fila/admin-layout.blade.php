@props([
    'heading' => '',
    'subheading' => '',
])

<div class="w-full {{ $attributes->get('class', 'max-w-6xl') }}">
    <flux:heading>{{ $heading }}</flux:heading>
    @if ($subheading)
        <flux:subheading>{{ $subheading }}</flux:subheading>
    @endif

    <div class="mt-5 w-full">
        {{ $slot }}
    </div>
</div>

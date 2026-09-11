@props(['title' => null, 'kicker' => null, 'variant' => 'default'])

@php
    $variants = [
        'default' => '',
        'cyan' => 'sn-card-cyan',
        'yellow' => 'sn-card-yellow',
        'paper' => 'sn-card-paper',
        'black' => 'sn-card-black',
    ];
    $variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<article {{ $attributes->merge(['class' => 'sn-card ' . $variantClass]) }}>
    @if ($kicker)
        <p class="sn-kicker mb-2">{{ $kicker }}</p>
    @endif
    @if ($title)
        <h3 class="sn-card-title">{{ $title }}</h3>
    @endif
    <div class="sn-card-body">
        {{ $slot }}
    </div>
</article>

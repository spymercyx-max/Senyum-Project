@props(['href' => null, 'type' => 'button', 'variant' => 'primary'])

@php
    $variants = [
        'primary' => 'sn-btn-primary',
        'yellow' => 'sn-btn-yellow',
        'cyan' => 'sn-btn-cyan',
        'danger' => 'sn-btn-danger',
        'ghost' => 'sn-btn-ghost',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'sn-btn ' . $variantClass]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'sn-btn ' . $variantClass]) }}>{{ $slot }}</button>
@endif

@props(['size' => 'md'])

@php
    $dims = ['sm' => 32, 'md' => 48, 'lg' => 72][$size] ?? 48;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center']) }} role="img" aria-label="Logo SENYUM — wajah tersenyum">
    <svg width="{{ $dims }}" height="{{ $dims }}" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="24" cy="24" r="21" fill="#FFD21F" stroke="#141414" stroke-width="3"/>
        <circle cx="17" cy="19" r="3" fill="#141414"/>
        <circle cx="31" cy="19" r="3" fill="#141414"/>
        <path d="M14 29c2.5 4.5 6 6.5 10 6.5s7.5-2 10-6.5" stroke="#141414" stroke-width="3" stroke-linecap="round"/>
    </svg>
</span>

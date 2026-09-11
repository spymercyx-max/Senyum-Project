@props(['status' => 'draft'])

@php
    $key = strtolower((string) $status);
    $map = [
        'active' => 'sn-badge--active',
        'approved' => 'sn-badge--approved',
        'success' => 'sn-badge--success',
        'pending' => 'sn-badge--pending',
        'warning' => 'sn-badge--warning',
        'rejected' => 'sn-badge--rejected',
        'danger' => 'sn-badge--danger',
        'suspended' => 'sn-badge--suspended',
        'draft' => 'sn-badge--draft',
        'featured' => 'sn-badge--featured',
    ];
    $mod = $map[$key] ?? 'sn-badge--draft';
    $label = $slot->isNotEmpty() ? trim($slot) : ucfirst($key);
@endphp

<span {{ $attributes->merge(['class' => 'sn-badge ' . $mod]) }}>{{ $label }}</span>

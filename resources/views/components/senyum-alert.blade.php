@props(['type' => 'info', 'title' => null, 'message' => null])

@php
    $allowed = ['info', 'action', 'warning', 'success', 'danger'];
    $kind = in_array($type, $allowed, true) ? $type : 'info';
    $text = $slot->isNotEmpty() ? trim($slot) : $message;
@endphp

<div class="sn-alert sn-alert--{{ $kind }}" role="alert" {{ $attributes }}>
    <div>
        @if ($title)
            <p class="sn-alert-title">{{ $title }}</p>
        @endif
        @if ($text)
            <p>{{ $text }}</p>
        @endif
    </div>
    <button type="button" class="sn-alert-close" data-alert-close aria-label="Tutup notifikasi">&times;</button>
</div>

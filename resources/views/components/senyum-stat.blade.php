@props(['label' => '', 'value' => '', 'sub' => null])

<div class="sn-stat" {{ $attributes }}>
    @if ($label)
        <p class="sn-stat-label">{{ $label }}</p>
    @endif
    <p class="sn-stat-value">{{ $value }}</p>
    @if ($sub)
        <p class="sn-stat-sub">{{ $sub }}</p>
    @elseif ($slot->isNotEmpty())
        <p class="sn-stat-sub">{{ $slot }}</p>
    @endif
</div>

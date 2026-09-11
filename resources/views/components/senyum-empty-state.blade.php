@props(['title' => 'Belum ada data', 'message' => null, 'action' => null, 'href' => null])

<div class="sn-empty" role="status" {{ $attributes }}>
    <div class="sn-empty-face" aria-hidden="true">:(</div>
    <h3 class="sn-empty-title">{{ $title }}</h3>
    @if ($message)
        <p class="sn-empty-message">{{ $message }}</p>
    @endif
    @if ($action && $href)
        <a href="{{ $href }}" class="sn-btn sn-btn-yellow sn-btn-sm">{{ $action }}</a>
    @elseif ($action)
        <span class="sn-btn sn-btn-yellow sn-btn-sm">{{ $action }}</span>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>

@props(['number' => '01', 'title' => '', 'desc' => null])

<section {{ $attributes->merge(['class' => 'my-10']) }} aria-label="{{ $title }}">
    <div class="sn-section-head">
        <span class="sn-section-number" aria-hidden="true">{{ $number }}</span>
        <div>
            <h2 class="sn-section-title">{{ $title }}</h2>
            @if ($desc)
                <p class="sn-section-desc">{{ $desc }}</p>
            @endif
        </div>
    </div>
    <div>
        {{ $slot }}
    </div>
</section>

@props([
    'title',
    'subtitle' => null,
    'date' => null,
])

<div class="vp-page-banner mb-4">
    <div class="vp-page-banner__pattern"></div>
    <div class="vp-page-banner__content">
        @if($date)
            <p class="vp-page-banner__date mb-1">{{ $date }}</p>
        @endif
        <h2 class="vp-page-banner__title mb-2">{{ $title }}</h2>
        @if($subtitle)
            <p class="vp-page-banner__subtitle mb-0">{{ $subtitle }}</p>
        @endif
    </div>
</div>

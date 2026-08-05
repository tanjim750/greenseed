@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $images = collect($content['images'] ?? [])->filter(fn ($image) => is_array($image))->values();
@endphp

<section
    class="landing-component seed-mobile-block seed-mobile-gallery {{ $scope }}"
    style="
        --seed-mobile-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --seed-mobile-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-mobile-inner">
        <div class="seed-mobile-card">
            @if(!empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif
            <div class="seed-mobile-gallery-grid">
                @foreach($images as $image)
                    <img src="{{ $image['url'] ?: asset('images/no_found.png') }}" alt="{{ $image['alt'] ?? '' }}">
                @endforeach
            </div>
        </div>
    </div>
</section>

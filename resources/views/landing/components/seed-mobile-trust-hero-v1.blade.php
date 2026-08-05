@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
@endphp

<section
    class="landing-component seed-mobile-block seed-mobile-trust-hero {{ $scope }}"
    style="
        --seed-mobile-bg: {{ $resolvedStyle['background_color'] ?? '#faf9f5' }};
        --seed-mobile-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        --seed-mobile-trust-bg: {{ $resolvedStyle['trust_background'] ?? '#d1e7dd' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-mobile-inner">
        @if(!empty($content['trust_message']))
            <div class="seed-mobile-trust-banner">{{ $content['trust_message'] }}</div>
        @endif
        <div class="seed-mobile-hero-copy">
            @if(!empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif
            @if(!empty($content['subheading']))
                <p>{{ $content['subheading'] }}</p>
            @endif
        </div>
    </div>
</section>

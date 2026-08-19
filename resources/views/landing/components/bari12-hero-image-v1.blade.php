@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-hero-image {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --bari12-card: {{ $resolvedStyle['card_background'] ?? '#f9ebea' }};
        --bari12-heading: {{ $resolvedStyle['heading_color'] ?? '#b91c1c' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        <div class="bari12-card">
            @if(!empty($content['heading']))
                <h2>{!! nl2br(e($content['heading'])) !!}</h2>
            @endif
        </div>
        @if(!empty($content['image_url']))
            <figure>
                <img src="{{ $content['image_url'] }}" alt="{{ $content['image_alt'] ?? 'Bari-12 offer' }}">
            </figure>
        @endif
    </div>
</section>

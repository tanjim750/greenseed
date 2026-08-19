@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-trust-banner {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#22c55e' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">{{ $content['text'] ?? '' }}</div>
</section>

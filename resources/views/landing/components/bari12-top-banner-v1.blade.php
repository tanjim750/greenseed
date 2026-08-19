@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-stitch-top-banner {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#f2d7d5' }};
        --bari12-heading: {{ $resolvedStyle['heading_color'] ?? '#b91c1c' }};
        --bari12-muted: {{ $resolvedStyle['text_color'] ?? '#dc2626' }};
        --bari12-border-strong: {{ $resolvedStyle['border_color'] ?? '#ef4444' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <h1>{{ $content['heading'] }}</h1>
        @endif
        @if(!empty($content['subheading']))
            <p>{{ $content['subheading'] }}</p>
        @endif
    </div>
</section>

@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-whatsapp {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#f9ebea' }};
        --bari12-heading: {{ $resolvedStyle['heading_color'] ?? '#7f1d1d' }};
        --bari12-button: {{ $resolvedStyle['button_color'] ?? '#6c3453' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <h2>{{ $content['heading'] }}</h2>
        @endif
        <a class="bari12-btn" href="{{ $render->href($content['url'] ?? '#') }}">
            <span class="bari12-icon">{{ $content['icon'] ?? 'chat' }}</span>
            <span>{{ $content['label'] ?? '' }}</span>
        </a>
    </div>
</section>

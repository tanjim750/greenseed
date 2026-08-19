@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component {{ $scope }}"
    style="
        --bari12-button: {{ $resolvedStyle['button_color'] ?? '#dc2626' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <a class="bari12-floating-whatsapp" href="{{ $render->href($content['url'] ?? '#') }}">
        <span class="bari12-icon">{{ $content['icon'] ?? 'chat' }}</span>
        <span>{{ $content['label'] ?? '' }}</span>
    </a>
</section>

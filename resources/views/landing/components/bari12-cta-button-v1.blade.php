@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-cta {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --bari12-button: {{ $resolvedStyle['button_color'] ?? '#1d8348' }};
        --bari12-button-top: {{ $resolvedStyle['button_top_color'] ?? '#28b463' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        <a class="bari12-btn" href="{{ $render->href($content['url'] ?? '#order-form') }}">
            <span class="bari12-icon">{{ $content['icon'] ?? 'shopping_cart' }}</span>
            <span>{{ $content['label'] ?? '' }}</span>
        </a>
    </div>
</section>

@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-why-us {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#e8f8f5' }};
        --bari12-heading: {{ $resolvedStyle['heading_color'] ?? '#7f1d1d' }};
        --bari12-check: {{ $resolvedStyle['check_color'] ?? '#7f1d1d' }};
        --bari12-button: {{ $resolvedStyle['button_color'] ?? '#1d8348' }};
        --bari12-button-top: {{ $resolvedStyle['button_top_color'] ?? '#28b463' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <h2 class="bari12-section-title">{{ $content['heading'] }}</h2>
        @endif
        <div class="bari12-rule" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
        <ul class="bari12-check-list">
            @foreach(($content['items'] ?? []) as $item)
                <li>
                    <span class="bari12-icon">check_circle</span>
                    <span>{{ $item['text'] ?? '' }}</span>
                </li>
            @endforeach
        </ul>
        @if(!empty($content['button_text']))
            <div class="bari12-cta-wrap">
                <a class="bari12-btn" href="{{ $render->href($content['button_url'] ?? '#order-form') }}">
                    <span class="bari12-icon">shopping_cart</span>
                    <span>{{ $content['button_text'] }}</span>
                </a>
            </div>
        @endif
    </div>
</section>

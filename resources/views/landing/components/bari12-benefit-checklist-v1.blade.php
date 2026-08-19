@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-benefits {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#f0fdf4' }};
        --bari12-title-bg: {{ $resolvedStyle['title_background'] ?? '#fcf3cf' }};
        --bari12-heading: {{ $resolvedStyle['heading_color'] ?? '#145a32' }};
        --bari12-check: {{ $resolvedStyle['check_color'] ?? '#dc2626' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <h2 class="bari12-section-title">{{ $content['heading'] }}</h2>
        @endif
        <ul class="bari12-check-list">
            @foreach(($content['items'] ?? []) as $item)
                <li>
                    <span class="bari12-icon">check_circle</span>
                    <span>{{ $item['text'] ?? '' }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>

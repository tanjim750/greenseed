@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-gallery {{ $scope }}"
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#f7fef9' }};
        --bari12-image-border: {{ $resolvedStyle['image_border_color'] ?? '#3b82f6' }};
        --bari12-columns: {{ (int) ($settings['columns'] ?? 2) }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <div class="bari12-gallery-head"><h2 class="bari12-section-title">{{ $content['heading'] }}</h2></div>
        @endif
        <div class="bari12-gallery-grid">
            @foreach(($content['images'] ?? []) as $image)
                <figure>
                    <img src="{{ $image['url'] ?? asset('images/no_found.png') }}" alt="{{ $image['alt'] ?? 'Bari-12 gallery image' }}">
                </figure>
            @endforeach
        </div>
    </div>
</section>

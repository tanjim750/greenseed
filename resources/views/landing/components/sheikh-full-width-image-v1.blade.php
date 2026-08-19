@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-image-banner {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <img src="{{ $content['image_url'] ?? '' }}" alt="{{ $content['image_alt'] ?? 'Field image' }}">
    </div>
</section>

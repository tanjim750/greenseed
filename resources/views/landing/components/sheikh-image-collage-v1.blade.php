@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $images = collect($content['images'] ?? [])->filter(fn ($image) => is_array($image))->take(4);
@endphp
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-collage {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#dcfce7' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <div class="sheikh-collage-grid">
            @foreach($images as $image)
                <figure>
                    <img src="{{ $image['url'] ?? '' }}" alt="{{ $image['alt'] ?? 'Seeds image' }}">
                </figure>
            @endforeach
            @if(!empty($content['center_image_url']))
                <div class="sheikh-collage-center">
                    <img src="{{ $content['center_image_url'] }}" alt="{{ $content['center_image_alt'] ?? 'Product image' }}">
                </div>
            @endif
        </div>
    </div>
</section>

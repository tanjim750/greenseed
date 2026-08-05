@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $images = collect($content['images'] ?? [])->filter(fn ($image) => is_array($image));
    $columns = max(2, min((int) ($settings['columns'] ?? 4), 6));
@endphp

<section
    class="landing-component seed-gallery {{ $scope }}"
    style="
        --seed-gallery-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --seed-gallery-columns: {{ $columns }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        @if(!empty($content['heading']))
            <h2>{{ $content['heading'] }}</h2>
        @endif

        <div class="seed-gallery-grid">
            @foreach($images as $image)
                <figure>
                    <img src="{{ $render->href($image['url'] ?? '') }}" alt="{{ $image['alt'] ?? '' }}">
                </figure>
            @endforeach
        </div>
    </div>
</section>

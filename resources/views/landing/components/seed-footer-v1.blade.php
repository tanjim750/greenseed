@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $links = collect($content['links'] ?? [])->filter(fn ($link) => is_array($link));
@endphp

<footer
    class="landing-component seed-footer {{ $scope }}"
    style="
        --seed-footer-bg: {{ $resolvedStyle['background_color'] ?? '#e2e3df' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        <div>
            <strong>{{ $content['brand'] ?? '' }}</strong>
            @if(!empty($content['description']))
                <p>{{ $content['description'] }}</p>
            @endif
        </div>

        <nav>
            @foreach($links as $link)
                <a href="{{ $render->href($link['url'] ?? '#') }}">{{ $link['label'] ?? '' }}</a>
            @endforeach
        </nav>
    </div>
</footer>

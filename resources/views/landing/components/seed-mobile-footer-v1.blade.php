@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $links = collect($content['links'] ?? [])->filter(fn ($link) => is_array($link))->values();
@endphp

<footer
    class="landing-component seed-mobile-footer {{ $scope }}"
    style="
        --seed-mobile-bg: {{ $resolvedStyle['background_color'] ?? '#f4f4f0' }};
        --seed-mobile-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    @if(!empty($content['brand']))
        <strong>{{ $content['brand'] }}</strong>
    @endif
    @if(!empty($content['copyright']))
        <p>{{ $content['copyright'] }}</p>
    @endif
    <nav>
        @foreach($links as $link)
            <a href="{{ $render->href($link['url'] ?? '#') }}">{{ $link['label'] ?? '' }}</a>
        @endforeach
    </nav>
</footer>

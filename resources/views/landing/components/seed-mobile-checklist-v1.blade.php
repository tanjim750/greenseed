@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $items = collect($content['items'] ?? [])->filter(fn ($item) => is_array($item))->values();
@endphp

<section
    class="landing-component seed-mobile-block seed-mobile-checklist {{ $scope }}"
    style="
        --seed-mobile-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --seed-mobile-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-mobile-inner">
        <div class="seed-mobile-card">
            @if(!empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif
            <ul>
                @foreach($items as $item)
                    <li>
                        <span class="material-symbols-outlined">check_circle</span>
                        <p>{{ $item['text'] ?? '' }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $featurePoints = collect($content['feature_points'] ?? [])->filter();
    $cards = collect($content['cards'] ?? [])->filter(fn ($card) => is_array($card));
    $trustCards = collect($content['trust_cards'] ?? [])->filter(fn ($card) => is_array($card));
@endphp

<section
    class="landing-component seed-benefits {{ $scope }}"
    style="
        --seed-benefits-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --seed-benefits-accent: {{ $resolvedStyle['accent_color'] ?? '#ffb300' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        @if(!empty($content['heading']))
            <div class="seed-section-heading">
                <h2>{{ $content['heading'] }}</h2>
                <span></span>
            </div>
        @endif

        <div class="seed-benefits-grid">
            <article class="seed-feature-card">
                <span class="material-symbols-outlined">workspace_premium</span>
                <h3>{{ $content['feature_title'] ?? '' }}</h3>
                <p>{{ $content['feature_description'] ?? '' }}</p>
                <ul>
                    @foreach($featurePoints as $point)
                        <li>
                            <span class="material-symbols-outlined">check_circle</span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </article>

            @foreach($cards as $card)
                <article class="seed-small-card">
                    <span class="material-symbols-outlined">eco</span>
                    <h4>{{ $card['title'] ?? '' }}</h4>
                    <p>{{ $card['description'] ?? '' }}</p>
                </article>
            @endforeach

            <div class="seed-trust-stack">
                @foreach($trustCards as $card)
                    <article class="seed-trust-card">
                        <span class="material-symbols-outlined">verified</span>
                        <div>
                            <h4>{{ $card['title'] ?? '' }}</h4>
                            <p>{{ $card['description'] ?? '' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $flags = collect($content['flags'] ?? [])->filter(fn ($flag) => is_array($flag));
@endphp
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-hero {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#14532d' }};
        --sheikh-accent: {{ $resolvedStyle['accent_color'] ?? '#facc15' }};
        --sheikh-border: {{ $resolvedStyle['border_color'] ?? '#22c55e' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <div class="sheikh-hero-card">
            <div class="sheikh-hero-content">
                @if(!empty($content['kicker']))
                    <p class="sheikh-hero-kicker">{{ $content['kicker'] }}</p>
                @endif
                <h1>{{ $content['heading'] ?? '' }}</h1>
                <div class="sheikh-price-row">
                    @if(!empty($content['old_price']))
                        <span class="sheikh-old-price">{{ $content['old_price'] }}</span>
                    @endif
                    @if(!empty($content['price']))
                        <span class="sheikh-new-price">{{ $content['price'] }}</span>
                    @endif
                </div>
                @if(!empty($content['delivery_text']))
                    <div class="sheikh-delivery-badge">{{ $content['delivery_text'] }}</div>
                @endif
                <div class="sheikh-hero-flags">
                    @foreach($flags as $flag)
                        @php
                            $flagColor = (string) ($flag['color'] ?? '#22c55e');
                            $flagColor = preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $flagColor) ? $flagColor : '#22c55e';
                        @endphp
                        <span class="sheikh-hero-flag">
                            <span class="sheikh-dot" style="--sheikh-dot: {{ $flagColor }}"></span>
                            {{ $flag['text'] ?? '' }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

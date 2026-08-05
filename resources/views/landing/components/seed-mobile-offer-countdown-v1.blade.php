@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $totalSeconds = max(0, ((int) ($settings['hours'] ?? 0) * 3600) + ((int) ($settings['minutes'] ?? 0) * 60) + (int) ($settings['seconds'] ?? 0));
@endphp

<section
    class="landing-component seed-mobile-block seed-mobile-offer {{ $scope }}"
    data-seed-mobile-countdown="{{ $totalSeconds }}"
    style="
        --seed-mobile-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        --seed-mobile-accent: {{ $resolvedStyle['accent_color'] ?? '#ffb300' }};
        --seed-mobile-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-mobile-inner">
        <div class="seed-mobile-card seed-mobile-offer-card">
            @if(!empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif
            @if(!empty($content['subheading']))
                <p>{{ $content['subheading'] }}</p>
            @endif
            <div class="seed-mobile-countdown">
                <span><b data-count-days>00</b><small>Days</small></span>
                <span><b data-count-hours>00</b><small>Hours</small></span>
                <span><b data-count-mins>00</b><small>Mins</small></span>
                <span><b data-count-secs>00</b><small>Secs</small></span>
            </div>
            <div class="seed-mobile-price-row">
                @if(!empty($content['regular_price']))
                    <s>{{ $content['regular_price'] }}</s>
                @endif
                @if(!empty($content['offer_price']))
                    <strong>{{ $content['offer_price'] }}</strong>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
    (() => {
        const root = document.currentScript.previousElementSibling;
        let seconds = Number(root?.dataset.seedMobileCountdown || 0);
        const fill = () => {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            root.querySelector('[data-count-days]').textContent = String(days).padStart(2, '0');
            root.querySelector('[data-count-hours]').textContent = String(hours).padStart(2, '0');
            root.querySelector('[data-count-mins]').textContent = String(mins).padStart(2, '0');
            root.querySelector('[data-count-secs]').textContent = String(secs).padStart(2, '0');
        };
        fill();
        window.setInterval(() => {
            seconds = Math.max(0, seconds - 1);
            fill();
        }, 1000);
    })();
</script>

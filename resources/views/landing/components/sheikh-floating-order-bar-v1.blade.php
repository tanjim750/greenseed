@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-floating-order {{ $scope }}"
    data-landing-component
    style="
        --sheikh-float-bg: {{ $resolvedStyle['background_color'] ?? '#fffefb' }};
        --sheikh-float-badge-bg: {{ $resolvedStyle['badge_background'] ?? '#dcfce7' }};
        --sheikh-float-badge-text: {{ $resolvedStyle['badge_color'] ?? '#168a45' }};
        --sheikh-float-text: {{ $resolvedStyle['text_color'] ?? '#2f3a3f' }};
        --sheikh-float-old-price: {{ $resolvedStyle['old_price_color'] ?? '#b8c2c6' }};
        --sheikh-float-price: {{ $resolvedStyle['price_color'] ?? '#168a45' }};
        --sheikh-float-button: {{ $resolvedStyle['button_color'] ?? '#168a45' }};
        --sheikh-float-button-text: {{ $resolvedStyle['button_text_color'] ?? '#ffffff' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-floating-order-card">
        <div class="sheikh-floating-copy">
            @if(!empty($content['badge']))
                <span class="sheikh-floating-badge">{{ $content['badge'] }}</span>
            @endif
            @if(!empty($content['title']))
                <strong class="sheikh-floating-title">{{ $content['title'] }}</strong>
            @endif
            <span class="sheikh-floating-price">
                @if(!empty($content['old_price']))
                    <s>{{ $content['old_price'] }}</s>
                @endif
                @if(!empty($content['price']))
                    <strong>{{ $content['price'] }}</strong>
                @endif
            </span>
        </div>
        <a class="sheikh-floating-btn" href="{{ $render->href($content['button_url'] ?? '#greenseed-checkout-form') }}">
            <span>{{ $content['button_text'] ?? 'অর্ডার করুন' }}</span>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
        </a>
    </div>
</section>
<div class="sheikh-floating-order-spacer" aria-hidden="true"></div>

<script>
    (() => {
        const spacer = document.currentScript.previousElementSibling;
        const root = spacer?.previousElementSibling;
        const checkout = document.getElementById('greenseed-checkout-form');

        if (!root || !root.classList.contains('sheikh-floating-order') || !checkout) {
            return;
        }

        const setHidden = (hidden) => {
            root.classList.toggle('is-hidden', hidden);
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                setHidden(entries.some((entry) => entry.isIntersecting));
            }, {
                threshold: 0.08,
            });

            observer.observe(checkout);

            return;
        }

        const sync = () => {
            const rect = checkout.getBoundingClientRect();
            setHidden(rect.bottom > 0 && rect.top < window.innerHeight);
        };

        window.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        sync();
    })();
</script>

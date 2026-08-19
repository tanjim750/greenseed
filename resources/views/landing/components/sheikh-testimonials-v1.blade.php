@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $items = collect($content['items'] ?? [])->filter(fn ($item) => is_array($item));
@endphp
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-testimonials {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#f8fafc' }};
        --sheikh-heading: {{ $resolvedStyle['heading_color'] ?? '#1e3a8a' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <h2 class="sheikh-section-title">{{ $content['heading'] ?? '' }}</h2>
        <div class="sheikh-testimonial-grid">
            @foreach($items as $item)
                <article class="sheikh-testimonial">
                    <span class="sheikh-testimonial-mark">"</span>
                    <div class="sheikh-person">
                        <span class="sheikh-avatar">{{ $item['avatar'] ?? '👤' }}</span>
                        <span>
                            <strong>{{ $item['name'] ?? '' }}</strong>
                            <span class="sheikh-stars">{{ $item['stars'] ?? '★★★★★' }}</span>
                        </span>
                    </div>
                    <p>{{ $item['quote'] ?? '' }}</p>
                    <span class="sheikh-verified">✓ {{ $item['badge'] ?? 'Verified customer' }}</span>
                </article>
            @endforeach
        </div>
    </div>
</section>

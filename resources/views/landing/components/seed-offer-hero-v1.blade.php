@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $durationHours = max(1, (int) data_get($settings, 'countdown.duration_hours', 4));
    $startsAt = data_get($settings, 'countdown.starts_at');
    $showCountdown = (bool) ($settings['show_countdown'] ?? true);
    $behaviourPayload = collect($definition->behaviours())
        ->map(fn ($key) => [
            'key' => $key,
            'config' => [
                'durationHours' => $durationHours,
                'startsAt' => $startsAt,
            ],
        ])
        ->values()
        ->all();
@endphp

<section
    class="landing-component seed-offer-hero {{ $scope }}"
    data-landing-component
    data-component-id="{{ $component->source_component_id ?? $component->id }}"
    data-published-version-id="{{ $component->published_version_id ?? '' }}"
    data-component-key="{{ $definition->key() }}"
    data-component-scope="{{ $scope }}"
    data-behaviours='@json($behaviourPayload)'
    style="
        --seed-hero-bg: {{ $resolvedStyle['background_color'] ?? '#0d631b' }};
        --seed-hero-accent: {{ $resolvedStyle['accent_color'] ?? '#ffb300' }};
        --seed-hero-text: {{ $resolvedStyle['text_color'] ?? '#ffffff' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-hero-card">
        <div class="seed-hero-text">
            @if(!empty($content['badge_text']))
                <div class="seed-offer-badge">
                    <span class="material-symbols-outlined">bolt</span>
                    {{ $content['badge_text'] }}
                </div>
            @endif

            <h1>{{ $content['title'] ?? '' }}</h1>

            @if(!empty($content['description']))
                <p>{{ $content['description'] }}</p>
            @endif

            <div class="seed-hero-meta">
                <div>
                    @if(!empty($content['offer_label']))
                        <div class="seed-meta-label">{{ $content['offer_label'] }}</div>
                    @endif
                    <div class="seed-price-row">
                        <span>{{ $content['price'] ?? '' }}</span>
                        @if(!empty($content['old_price']))
                            <s>{{ $content['old_price'] }}</s>
                        @endif
                    </div>
                </div>

                @if($showCountdown)
                    <div class="seed-countdown-card">
                        <div class="seed-meta-label">{{ $content['timer_label'] ?? '' }}</div>
                        <div class="landing-countdown" data-countdown-output>00d 00h 00m 00s</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="seed-hero-visual">
            <div class="seed-hero-image-ring">
                <img src="{{ $render->href($content['image_url'] ?? '') }}" alt="{{ $content['image_alt'] ?? '' }}">
            </div>
            @if(!empty($content['trust_badge']))
                <div class="seed-guarantee-badge">
                    <span class="material-symbols-outlined">check_circle</span>
                    {{ $content['trust_badge'] }}
                </div>
            @endif
        </div>
    </div>
</section>

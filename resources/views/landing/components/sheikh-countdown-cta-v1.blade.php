@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $durationHours = max(1, (int) data_get($settings, 'countdown.duration_hours', 4));
    $startsAt = data_get($settings, 'countdown.starts_at');
    $behaviourPayload = collect($definition->behaviours())
        ->map(fn ($key) => ['key' => $key, 'config' => ['durationHours' => $durationHours, 'startsAt' => $startsAt]])
        ->values()
        ->all();
@endphp
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-countdown-cta {{ $scope }}"
    data-landing-component
    data-behaviours='@json($behaviourPayload)'
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --sheikh-countdown-bg: {{ $resolvedStyle['countdown_background'] ?? '#fff7ed' }};
        --sheikh-countdown-border: {{ $resolvedStyle['countdown_border_color'] ?? '#ca8a04' }};
        --sheikh-countdown-color: {{ $resolvedStyle['countdown_color'] ?? '#166534' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <div class="sheikh-countdown-box">
            <p class="sheikh-countdown-label">{{ $content['countdown_label'] ?? '' }}</p>
            <div class="sheikh-countdown" data-countdown-output aria-label="Offer countdown">
                <span class="sheikh-countdown-unit"><b data-countdown-part="days">00</b><small>দিন</small></span>
                <span class="sheikh-countdown-unit"><b data-countdown-part="minutes">00</b><small>মিনিট</small></span>
                <span class="sheikh-countdown-unit"><b data-countdown-part="seconds">00</b><small>সেকেন্ড</small></span>
            </div>
        </div>
    </div>
</section>

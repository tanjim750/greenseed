@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $behaviourPayload = collect($definition->behaviours())
        ->map(fn ($key) => [
            'key' => $key,
            'config' => [
                'durationHours' => $settings['countdown']['duration_hours'] ?? null,
                'startsAt' => $settings['countdown']['starts_at'] ?? null,
            ],
        ])
        ->values()
        ->all();
@endphp

<section
    class="landing-component landing-hero {{ $scope }}"
    data-landing-component
    data-component-id="{{ $component->source_component_id ?? $component->id }}"
    data-published-version-id="{{ $component->published_version_id ?? '' }}"
    data-component-key="{{ $definition->key() }}"
    data-component-scope="{{ $scope }}"
    data-behaviours='@json($behaviourPayload)'
    data-runtime-config='@json([])'
    style="
        --hero-background: {{ $resolvedStyle['background_color'] ?? '#0f172a' }};
        --hero-title: {{ $resolvedStyle['title_color'] ?? '#ffffff' }};
        --button-color: {{ $resolvedStyle['button_color'] ?? '#2563eb' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        <h1>{{ $content['title'] ?? '' }}</h1>

        @if(!empty($content['description']))
            <p>{{ $content['description'] }}</p>
        @endif

        <div class="landing-countdown" data-countdown-output aria-live="polite"></div>

        @if(!empty($content['button_text']))
            <a class="landing-button" href="{{ $render->href($content['button_url'] ?? '#') }}">
                {{ $content['button_text'] }}
            </a>
        @endif
    </div>
</section>

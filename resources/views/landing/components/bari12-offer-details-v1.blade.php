@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $durationHours = max(1, (int) data_get($settings, 'countdown.duration_hours', 4));
    $startsAt = data_get($settings, 'countdown.starts_at');
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
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-offer {{ $scope }}"
    data-landing-component
    data-component-id="{{ $component->source_component_id ?? $component->id }}"
    data-published-version-id="{{ $component->published_version_id ?? '' }}"
    data-component-key="{{ $definition->key() }}"
    data-component-scope="{{ $scope }}"
    data-behaviours='@json($behaviourPayload)'
    style="
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#e6b0aa' }};
        --bari12-accent: {{ $resolvedStyle['accent_color'] ?? '#15803d' }};
        --bari12-countdown: {{ $resolvedStyle['countdown_color'] ?? '#e74c3c' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <h3>{!! str_replace('{gift}', '<span class="bari12-gift">' . e($content['gift_text'] ?? '') . '</span>', e($content['heading'])) !!}</h3>
        @endif
        @if(!empty($content['limited_text']))
            <p class="bari12-limit">{{ $content['limited_text'] }}</p>
        @endif
        @if(!empty($content['red_heading']))
            <h4 class="bari12-red">{{ $content['red_heading'] }}</h4>
        @endif
        @if(!empty($content['blue_heading']))
            <h4 class="bari12-blue">{{ $content['blue_heading'] }}</h4>
        @endif
        @if(!empty($content['regular_price']))
            <p>রেগুলার মূল্য <s>{{ $content['regular_price'] }}</s> টাকা</p>
        @endif
        <div class="bari12-price-row">
            <span>{{ $content['offer_label'] ?? 'আজকের অফার মূল্য মাত্র' }}</span>
            <span class="bari12-price-badge">{{ $content['offer_price'] ?? '' }}</span>
            <span>টাকা</span>
        </div>
        <div class="bari12-countdown" data-countdown-output aria-label="Offer countdown">
            @foreach(($content['countdown'] ?? []) as $unit)
                @php
                    $label = strtolower((string) ($unit['label'] ?? ''));
                    $part = str_starts_with($label, 'day') ? 'days'
                        : (str_starts_with($label, 'hour') ? 'hours'
                            : (str_starts_with($label, 'minute') ? 'minutes'
                                : (str_starts_with($label, 'second') ? 'seconds' : '')));
                @endphp
                <span>
                    <b @if($part) data-countdown-part="{{ $part }}" @endif>{{ $unit['value'] ?? '00' }}</b>
                    <small>{{ $unit['label'] ?? '' }}</small>
                </span>
            @endforeach
        </div>
    </div>
</section>

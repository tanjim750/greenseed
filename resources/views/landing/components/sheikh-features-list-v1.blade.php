@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $items = collect($content['items'] ?? [])->filter(fn ($item) => is_array($item));
@endphp
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-list {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --sheikh-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --sheikh-heading-bg: {{ $resolvedStyle['heading_background'] ?? '#5b21b6' }};
        --sheikh-heading-text: {{ $resolvedStyle['heading_color'] ?? '#ffffff' }};
        --sheikh-icon-bg: {{ $resolvedStyle['icon_background'] ?? '#f3e8ff' }};
        --sheikh-check: {{ $resolvedStyle['check_color'] ?? '#7c3aed' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <div class="sheikh-panel">
            <div class="sheikh-panel-head"><h2>{{ $content['heading'] ?? '' }}</h2></div>
            <ul class="sheikh-items">
                @foreach($items as $item)
                    <li>
                        <span class="sheikh-item-icon">✓</span>
                        <span>{{ $item['text'] ?? '' }}</span>
                        <span class="sheikh-check">✓</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

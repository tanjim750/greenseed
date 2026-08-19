@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $badges = collect($content['badges'] ?? [])->filter(fn ($badge) => is_array($badge));
    $phone = preg_replace('/[^0-9+]/', '', (string) ($content['phone'] ?? ''));
@endphp

<section
    class="landing-component seed-support {{ $scope }}"
    style="
        --seed-support-button: {{ $resolvedStyle['button_color'] ?? '#006e1c' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        @if(!empty($content['heading']))
            <p>{{ $content['heading'] }}</p>
        @endif

        @if($phone !== '')
            <a href="tel:{{ $phone }}">
                <span class="material-symbols-outlined">call</span>
                {{ $content['phone'] }}
            </a>
        @endif

        <div class="seed-support-badges">
            @foreach($badges as $badge)
                <span>
                    <span class="material-symbols-outlined">verified</span>
                    {{ $badge['text'] ?? '' }}
                </span>
            @endforeach
        </div>
    </div>
</section>

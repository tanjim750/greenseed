@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-cta {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --sheikh-button: {{ $resolvedStyle['button_color'] ?? '#22734e' }};
        --sheikh-button-text: {{ $resolvedStyle['button_text_color'] ?? '#ffffff' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <a class="sheikh-btn" href="{{ $render->href($content['url'] ?? '#greenseed-checkout-form') }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            <span>{{ $content['label'] ?? '' }}</span>
        </a>
    </div>
</section>

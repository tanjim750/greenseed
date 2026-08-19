@php($render = app(\App\Services\Landing\LandingRenderSupport::class))
@include('landing.components.partials.sheikh-seeds-styles')

<footer
    class="landing-component sheikh-component sheikh-footer {{ $scope }}"
    data-landing-component
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#f3f4f6' }};
        --sheikh-button: {{ $resolvedStyle['button_color'] ?? '#dc2626' }};
        --sheikh-button-text: {{ $resolvedStyle['button_text_color'] ?? '#ffffff' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        @if(!empty($content['button_text']))
            <a class="sheikh-btn" href="{{ $render->href($content['button_url'] ?? '#greenseed-checkout-form') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <span>{{ $content['button_text'] }}</span>
            </a>
        @endif
        <p>{{ $content['text'] ?? '' }}</p>
    </div>
</footer>

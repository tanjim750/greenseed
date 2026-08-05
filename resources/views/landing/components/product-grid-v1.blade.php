@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $products = collect($resolvedData['products'] ?? []);
    $columns = max(1, min((int) ($settings['columns'] ?? 4), 6));
    $behaviourPayload = collect($definition->behaviours())
        ->map(fn ($key) => [
            'key' => $key,
            'config' => [],
        ])
        ->values()
        ->all();
    $runtimeConfig = [
        'actions' => [
            'orderSubmission' => [
                'url' => route('dynamic_landing.actions.store', ['actionKey' => 'order-submission']),
            ],
        ],
        'csrfToken' => csrf_token(),
    ];
@endphp

<section
    class="landing-component landing-products {{ $scope }}"
    data-landing-component
    data-component-id="{{ $component->source_component_id ?? $component->id }}"
    data-published-version-id="{{ $component->published_version_id ?? '' }}"
    data-component-key="{{ $definition->key() }}"
    data-component-scope="{{ $scope }}"
    data-behaviours='@json($behaviourPayload)'
    data-runtime-config='@json($runtimeConfig)'
    style="
        --products-background: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --product-columns: {{ $columns }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        @if(!empty($content['section_title']))
            <h2>{{ $content['section_title'] }}</h2>
        @endif

        <div class="landing-product-grid">
            @forelse($products as $product)
                <article class="landing-product-card">
                    <a href="{{ $render->href($product['url'] ?? '#') }}">
                        <img src="{{ $product['image_url'] ?? asset('images/no_found.png') }}" alt="{{ $product['name'] ?? 'Product' }}">
                    </a>

                    <div class="landing-product-card-body">
                        <h3>
                            <a href="{{ $render->href($product['url'] ?? '#') }}">
                                {{ $product['name'] ?? 'Product' }}
                            </a>
                        </h3>

                        @if(!empty($settings['show_price']))
                            <div class="landing-price">{{ $product['formatted_price'] ?? priceFormate(0) }}</div>
                        @endif

                        @if(!empty($settings['show_stock']))
                            <div class="landing-stock">{{ $product['availability_text'] ?? '' }}</div>
                        @endif

                        <form class="landing-order-form" data-landing-order-form>
                            <input type="hidden" name="product_id" value="{{ $product['id'] ?? '' }}">

                            @if(!empty($product['variations']))
                                <select name="variation_id" aria-label="Product option" required>
                                    <option value="">Select option</option>
                                    @foreach($product['variations'] as $variation)
                                        <option value="{{ $variation['id'] }}">
                                            {{ $variation['title'] }} @if(($variation['stock'] ?? 0) <= 0)(Out of stock)@endif
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <input type="number" name="quantity" value="1" min="1" max="100" aria-label="Quantity">
                            <input type="text" name="first_name" placeholder="Name" autocomplete="name" required>
                            <input type="tel" name="mobile" placeholder="Mobile" autocomplete="tel" required>
                            <textarea name="shipping_address" placeholder="Address" rows="2" autocomplete="street-address" required></textarea>
                            <button type="submit">Order Now</button>
                            <div class="landing-order-message" data-order-submission-message aria-live="polite"></div>
                        </form>
                    </div>
                </article>
            @empty
                <p>No products selected.</p>
            @endforelse
        </div>
    </div>
</section>

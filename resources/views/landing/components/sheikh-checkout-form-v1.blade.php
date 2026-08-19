@php
    $render = app(\App\Services\Landing\LandingRenderSupport::class);
    $storedContent = is_array($component->config['content'] ?? null) ? $component->config['content'] : [];
    $packageSource = is_array($storedContent['packages'] ?? null) ? $storedContent['packages'] : ($content['packages'] ?? []);
    $packages = collect($packageSource)->filter(fn ($package) => is_array($package))->values();
    $defaultQuantity = (int) ($settings['default_quantity'] ?? 1);
    $productIds = collect($dataSource['product_ids'] ?? [])->filter(fn ($id) => is_numeric($id))->values();
    $selectedProduct = $resolvedData['product'] ?? null;
    $productId = $productIds->first() ?: ($selectedProduct['id'] ?? null);
    $availableStock = is_numeric($selectedProduct['stock'] ?? null) ? (int) $selectedProduct['stock'] : null;
    $displayPackages = $packages->map(function ($package) use ($selectedProduct, $definition, $render) {
        $quantity = max(1, (int) ($package['quantity'] ?? 1));

        if (!$selectedProduct) {
            return $package;
        }

        $unitPrice = (float) ($selectedProduct['order_base_price'] ?? $selectedProduct['price'] ?? 0);
        $priceDisplay = $render->checkoutPackagePrice($package, $definition, $unitPrice, $quantity);

        return array_merge($package, [
            'title' => $package['title'] ?: ($quantity > 1
                ? $quantity . ' x ' . ($selectedProduct['name'] ?? '')
                : ($selectedProduct['name'] ?? '')),
            'subtitle' => $package['subtitle'] ?? ($selectedProduct['sku'] ? 'SKU: ' . $selectedProduct['sku'] : ($selectedProduct['availability_text'] ?? '')),
            'price' => $priceDisplay['price'],
            'original_price' => $priceDisplay['original_price'],
            'has_custom_price' => $priceDisplay['has_custom_price'],
        ]);
    })->filter(function ($package) use ($selectedProduct, $availableStock) {
        if (!$selectedProduct) {
            return true;
        }

        if ($availableStock === null) {
            return max(1, (int) ($package['quantity'] ?? 1)) === 1;
        }

        return max(1, (int) ($package['quantity'] ?? 1)) <= $availableStock;
    })->values();
    $selectedPackage = $selectedProduct && $displayPackages->isNotEmpty()
        ? ($displayPackages->firstWhere('quantity', $defaultQuantity) ?? $displayPackages->first())
        : null;
    $selectedPrice = $selectedPackage['price'] ?? '';
    $behaviourPayload = collect($definition->behaviours())->map(fn ($key) => ['key' => $key, 'config' => []])->values()->all();
    $runtimeConfig = [
        'actions' => [
            'orderSubmission' => [
                'url' => route('dynamic_landing.actions.store', ['actionKey' => 'order-submission']),
            ],
        ],
        'csrfToken' => csrf_token(),
    ];
@endphp
@include('landing.components.partials.sheikh-seeds-styles')

<section
    class="landing-component sheikh-component sheikh-checkout {{ $scope }}"
    id="{{ $render->checkoutAnchorId($component) }}"
    data-landing-component
    data-landing-component-role="checkout-form"
    data-component-id="{{ $component->source_component_id ?? $component->id }}"
    data-published-version-id="{{ $component->published_version_id ?? '' }}"
    data-component-key="{{ $definition->key() }}"
    data-component-scope="{{ $scope }}"
    data-behaviours='@json($behaviourPayload)'
    data-runtime-config='@json($runtimeConfig)'
    style="
        --sheikh-bg: {{ $resolvedStyle['background_color'] ?? '#ffffff' }};
        --sheikh-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --sheikh-primary: {{ $resolvedStyle['primary_color'] ?? '#22734e' }};
        --sheikh-button: {{ $resolvedStyle['button_color'] ?? '#f97316' }};
        --sheikh-button-text: {{ $resolvedStyle['button_text_color'] ?? '#ffffff' }};
        --sheikh-border: {{ $resolvedStyle['border_color'] ?? '#16a34a' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="sheikh-inner">
        <div class="sheikh-checkout-card">
            @if(!empty($content['heading']))
                <h2 class="sheikh-section-title">{{ $content['heading'] }}</h2>
            @endif

            <form class="sheikh-checkout-form" data-landing-order-form>
                <input type="hidden" name="product_id" value="{{ $productId ?? '' }}">

                <div>
                    <h3>{{ $content['products_heading'] ?? '' }}</h3>
                    @if(!$selectedProduct || $displayPackages->isEmpty())
                        <div class="sheikh-shipping-note">
                            {{ $selectedProduct ? 'No available package' : 'No product selected' }}
                        </div>
                    @else
                        <div class="sheikh-products">
                            @foreach($displayPackages as $package)
                                @php
                                    $quantity = (int) ($package['quantity'] ?? 1);
                                    $isSelected = (int) ($selectedPackage['quantity'] ?? $defaultQuantity) === $quantity;
                                @endphp
                                <label class="sheikh-package @if($isSelected) is-selected @endif" data-sheikh-package data-price="{{ $package['price'] ?? '' }}">
                                    <input type="radio" name="quantity" value="{{ $quantity }}" @checked($isSelected)>
                                    <img src="{{ $selectedProduct['image_url'] ?? asset('images/no_found.png') }}" alt="{{ $selectedProduct['name'] ?? 'Selected product' }}">
                                    <span>
                                        <span class="sheikh-package-title">{{ $package['title'] ?? '' }}</span>
                                        @if(!empty($package['subtitle']))
                                            <small class="sheikh-package-subtitle">{{ $package['subtitle'] }}</small>
                                        @endif
                                    </span>
                                    <strong class="sheikh-package-price">
                                        @if(!empty($package['has_custom_price']) && !empty($package['original_price']) && $package['original_price'] !== ($package['price'] ?? null))
                                            <s>{{ $package['original_price'] }}</s>
                                        @endif
                                        <span>{{ $package['price'] ?? '' }}</span>
                                    </strong>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="sheikh-checkout-grid">
                    <div>
                        <h3>{{ $content['billing_heading'] ?? '' }}</h3>
                        <div class="sheikh-field">
                            <label for="{{ $scope }}_first_name">আপনার পুরা নাম লিখুন *</label>
                            <input id="{{ $scope }}_first_name" name="first_name" type="text" required>
                        </div>
                        <div class="sheikh-field">
                            <label for="{{ $scope }}_address">আপনার সম্পূর্ণ ঠিকানা *</label>
                            <input id="{{ $scope }}_address" name="shipping_address" type="text" required>
                        </div>
                        <div class="sheikh-field">
                            <label for="{{ $scope }}_mobile">আপনার ফোন নাম্বার *</label>
                            <input id="{{ $scope }}_mobile" name="mobile" type="tel" required>
                        </div>
                        <h3>{{ $content['shipping_heading'] ?? '' }}</h3>
                        <div class="sheikh-shipping-note">{{ $content['shipping_label'] ?? '' }}</div>
                    </div>

                    <div>
                        <h3>{{ $content['order_heading'] ?? '' }}</h3>
                        <div class="sheikh-summary-box">
                            <div class="sheikh-summary-line">
                                <span>Product</span>
                                <strong data-sheikh-summary-product>{{ $selectedPackage['title'] ?? '' }}</strong>
                            </div>
                            <div class="sheikh-summary-line">
                                <span>Subtotal</span>
                                <strong data-sheikh-subtotal>{{ $selectedPrice }}</strong>
                            </div>
                            <div class="sheikh-summary-line total">
                                <span>Total</span>
                                <strong data-sheikh-total>{{ $selectedPrice }}</strong>
                            </div>
                        </div>
                        <div class="sheikh-shipping-note sheikh-payment-note">
                            <strong>{{ $content['payment_title'] ?? '' }}</strong><br>
                            {{ $content['payment_description'] ?? '' }}
                        </div>
                        <button class="sheikh-submit" type="submit" @disabled(!$selectedProduct || $displayPackages->isEmpty())>
                            {{ $content['button_text'] ?? 'Order Now' }}
                        </button>
                        <div class="landing-order-message" data-order-submission-message aria-live="polite"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    (() => {
        const root = document.currentScript.previousElementSibling;

        if (!root || !root.classList.contains('sheikh-checkout')) {
            return;
        }

        root.querySelectorAll('[data-sheikh-package] input[type="radio"]').forEach((input) => {
            input.addEventListener('change', () => {
                const card = input.closest('[data-sheikh-package]');
                const price = card?.dataset.price || '';
                const title = card?.querySelector('.sheikh-package-title')?.textContent?.trim() || '';

                root.querySelectorAll('[data-sheikh-package]').forEach((item) => {
                    item.classList.toggle('is-selected', item === card);
                });
                root.querySelectorAll('[data-sheikh-subtotal], [data-sheikh-total]').forEach((item) => {
                    item.textContent = price;
                });
                root.querySelectorAll('[data-sheikh-summary-product]').forEach((item) => {
                    item.textContent = title;
                });
            });
        });
    })();
</script>

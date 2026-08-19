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
    $behaviourPayload = collect($definition->behaviours())
        ->map(fn ($key) => ['key' => $key, 'config' => []])
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
@include('landing.components.partials.bari12-stitch-styles')

<section
    class="landing-component bari12-component bari12-checkout {{ $scope }}"
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
        --bari12-bg: {{ $resolvedStyle['background_color'] ?? '#e8f8f5' }};
        --bari12-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --bari12-primary: {{ $resolvedStyle['primary_color'] ?? '#1d8348' }};
        --bari12-button: {{ $resolvedStyle['button_color'] ?? '#6c3453' }};
        --bari12-border: {{ $resolvedStyle['border_color'] ?? '#d1d5db' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="bari12-inner">
        @if(!empty($content['heading']))
            <h2 class="bari12-section-title">{{ $content['heading'] }}</h2>
        @endif

        <form class="bari12-checkout-form" data-landing-order-form>
            <input type="hidden" name="product_id" value="{{ $productId ?? '' }}">

            <div class="bari12-fieldset bari12-billing-block">
                <h3>{{ $content['billing_heading'] ?? '' }}</h3>
                <div class="bari12-field">
                    <label>
                        <span>Full Name <strong>*</strong></span>
                        <input type="text" name="first_name" placeholder="আপনার সম্পূর্ণ নাম*" required>
                    </label>
                    <div class="bari12-form-note">Full Name is required</div>
                </div>
                <div class="bari12-field">
                    <label>
                        <span>Mobile Number <strong>*</strong></span>
                        <input type="tel" name="mobile" placeholder="আপনার মোবাইল নাম্বার* (১১ ডিজিট)" required>
                    </label>
                </div>
                <div class="bari12-field">
                    <label>
                        <span>Full Address <strong>*</strong></span>
                        <textarea name="shipping_address" placeholder="থানা, জেলা এবং ডেলিভারী ম্যান কোথায় আসবে?*" required></textarea>
                    </label>
                </div>
            </div>

            <div class="bari12-shipping-block">
                <h3>{{ $content['shipping_heading'] ?? '' }}</h3>
                <div class="bari12-shipping-row">
                    <span>{{ $content['shipping_label'] ?? '' }}</span>
                    <strong>0.00৳</strong>
                </div>
            </div>

            <div class="bari12-products-block">
                <h3>{{ $content['products_heading'] ?? '' }}</h3>
                @if(!$selectedProduct || $displayPackages->isEmpty())
                    <div class="bari12-shipping-row">
                        <span>{{ $selectedProduct ? 'No available package' : 'No product selected' }}</span>
                        <strong>0.00৳</strong>
                    </div>
                @else
                    <div class="bari12-packages">
                        @foreach($displayPackages as $package)
                            @php
                                $quantity = (int) ($package['quantity'] ?? 1);
                                $isSelected = (int) ($selectedPackage['quantity'] ?? $defaultQuantity) === $quantity;
                            @endphp
                            <label class="bari12-package @if($isSelected) is-selected @endif" data-bari12-package data-price="{{ $package['price'] ?? '' }}">
                                <input type="radio" name="quantity" value="{{ $quantity }}" @checked($isSelected)>
                                <span class="bari12-package-main">
                                    <img class="bari12-product-thumb" src="{{ $selectedProduct['image_url'] ?? asset('images/no_found.png') }}" alt="{{ $selectedProduct['name'] ?? 'Selected product' }}">
                                    <span>
                                        <span class="bari12-package-title">{{ $package['title'] ?? '' }}</span>
                                        @if(!empty($package['subtitle']))
                                            <small class="bari12-package-subtitle">{{ $package['subtitle'] }}</small>
                                        @endif
                                    </span>
                                </span>
                                <strong class="bari12-package-price">
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

            <div class="bari12-order-summary">
                <h3>{{ $content['order_heading'] ?? '' }}</h3>
                <div class="bari12-summary-table">
                    <div class="bari12-summary-row"><span>Subtotal</span><strong data-bari12-subtotal>{{ $selectedPrice }}</strong></div>
                    <div class="bari12-summary-row total"><span>Total</span><strong data-bari12-total>{{ $selectedPrice }}</strong></div>
                </div>
                <div class="bari12-payment">
                    <strong>{{ $content['payment_title'] ?? '' }}</strong>
                    <span>{{ $content['payment_description'] ?? '' }}</span>
                </div>
            </div>

            <button class="bari12-submit" type="submit" @disabled(!$selectedProduct || $displayPackages->isEmpty())>
                {{ $content['button_text'] ?? 'Order Now' }}
            </button>
            <div class="landing-order-message" data-order-submission-message aria-live="polite"></div>
        </form>
    </div>
</section>

<script>
    (() => {
        const root = document.currentScript.previousElementSibling;

        if (!root || !root.classList.contains('bari12-checkout')) {
            return;
        }

        root.querySelectorAll('[data-bari12-package] input[type="radio"]').forEach((input) => {
            input.addEventListener('change', () => {
                const card = input.closest('[data-bari12-package]');
                const price = card?.dataset.price || '';

                root.querySelectorAll('[data-bari12-package]').forEach((item) => {
                    item.classList.toggle('is-selected', item === card);
                });
                root.querySelectorAll('[data-bari12-subtotal], [data-bari12-total]').forEach((item) => {
                    item.textContent = price;
                });
            });
        });
    })();
</script>

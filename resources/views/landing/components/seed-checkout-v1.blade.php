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
    $displayPackages = $packages->map(function ($package) use ($selectedProduct) {
        $quantity = max(1, (int) ($package['quantity'] ?? 1));

        if (!$selectedProduct) {
            return $package;
        }

        $unitPrice = (float) ($selectedProduct['price'] ?? 0);
        $totalPrice = $unitPrice * $quantity;

        return array_merge($package, [
            'title' => $quantity > 1
                ? $quantity . ' x ' . ($selectedProduct['name'] ?? '')
                : ($selectedProduct['name'] ?? ''),
            'subtitle' => $package['subtitle']
                ?? ($selectedProduct['sku']
                    ? 'SKU: ' . $selectedProduct['sku']
                    : ($selectedProduct['availability_text'] ?? '')),
            'price' => function_exists('priceFormate') ? priceFormate($totalPrice) : number_format($totalPrice, 2),
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

<section
    class="landing-component seed-checkout {{ $scope }}"
    id="order-form"
    data-landing-component
    data-component-id="{{ $component->source_component_id ?? $component->id }}"
    data-published-version-id="{{ $component->published_version_id ?? '' }}"
    data-component-key="{{ $definition->key() }}"
    data-component-scope="{{ $scope }}"
    data-behaviours='@json($behaviourPayload)'
    data-runtime-config='@json($runtimeConfig)'
    style="
        --seed-checkout-bg: {{ $resolvedStyle['background_color'] ?? '#eeeeea' }};
        --seed-checkout-button: {{ $resolvedStyle['button_color'] ?? '#0d631b' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="landing-section-inner">
        @if(!empty($content['heading']))
            <h2>{{ $content['heading'] }}</h2>
        @endif

        <form class="seed-order-grid" data-landing-order-form>
            <input type="hidden" name="product_id" value="{{ $productId ?? '' }}">

            <div class="seed-order-column">
                <div class="seed-step-heading">
                    <span>১</span>
                    <h3>{{ $content['customer_heading'] ?? '' }}</h3>
                </div>

                <label>
                    <span>আপনার নাম *</span>
                    <input type="text" name="first_name" placeholder="সম্পূর্ণ নাম লিখুন" required>
                </label>

                <label>
                    <span>মোবাইল নাম্বার *</span>
                    <input type="tel" name="mobile" placeholder="১১ ডিজিটের নাম্বার" required>
                </label>

                <label>
                    <span>পূর্ণ ঠিকানা *</span>
                    <textarea name="shipping_address" placeholder="থানা, জেলা এবং গ্রাম সহ বিস্তারিত" rows="3" required></textarea>
                </label>

                <div class="seed-delivery-note">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <div>
                        <strong>{{ $content['delivery_title'] ?? '' }}</strong>
                        <p>{{ $content['delivery_description'] ?? '' }}</p>
                    </div>
                </div>
            </div>

            <div class="seed-order-column">
                <div class="seed-step-heading">
                    <span>২</span>
                    <h3>{{ $content['product_heading'] ?? '' }}</h3>
                </div>

                @if(!$selectedProduct || $displayPackages->isEmpty())
                    <div class="seed-selected-product seed-selected-product-empty">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <div>
                            <strong>{{ $selectedProduct ? 'No available package' : 'No product selected' }}</strong>
                            <small>{{ $selectedProduct ? 'Update package quantities or product stock before publishing.' : 'Select a product in the builder before publishing.' }}</small>
                        </div>
                    </div>
                @else
                    <div class="seed-package-list">
                        @foreach($displayPackages as $package)
                            @php
                                $quantity = (int) ($package['quantity'] ?? 1);
                                $isSelected = (int) ($selectedPackage['quantity'] ?? $defaultQuantity) === $quantity;
                            @endphp
                            <label class="seed-package-card @if($isSelected) is-selected @endif" data-package-card data-price="{{ $package['price'] ?? '' }}">
                                <input type="radio" name="quantity" value="{{ $quantity }}" @checked($isSelected)>
                                <img src="{{ $selectedProduct['image_url'] ?? asset('images/no_found.png') }}" alt="{{ $selectedProduct['name'] ?? 'Selected product' }}">
                                <span class="seed-package-info">
                                    <strong>{{ $package['title'] ?? '' }}</strong>
                                    @if(!empty($package['subtitle']))
                                        <small>{{ $package['subtitle'] }}</small>
                                    @endif
                                </span>
                                <b>{{ $package['price'] ?? '' }}</b>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="seed-summary-card">
                    <h4>{{ $content['summary_title'] ?? '' }}</h4>
                    <div><span>পণ্য মূল্য</span><span data-seed-checkout-subtotal>{{ $selectedPackage['price'] ?? '' }}</span></div>
                    <div><span>ডেলিভারি চার্জ</span><span>ফ্রি</span></div>
                    <strong><span>মোট</span><span data-seed-checkout-total>{{ $selectedPackage['price'] ?? '' }}</span></strong>
                </div>

                <p class="seed-payment-note">
                    <span class="material-symbols-outlined">payment</span>
                    {{ $content['payment_note'] ?? '' }}
                </p>

                <button type="submit" @disabled(!$selectedProduct || $displayPackages->isEmpty())>
                    {{ $content['button_text'] ?? 'Order Now' }}
                    <span class="material-symbols-outlined">shopping_cart_checkout</span>
                </button>

                <div class="landing-order-message" data-order-submission-message aria-live="polite"></div>
            </div>
        </form>
    </div>
</section>

<script>
    (() => {
        const root = document.currentScript.previousElementSibling;

        if (!root || !root.classList.contains('seed-checkout')) {
            return;
        }

        root.querySelectorAll('[data-package-card] input[type="radio"]').forEach((input) => {
            input.addEventListener('change', () => {
                const card = input.closest('[data-package-card]');
                const price = card?.dataset.price || '';

                root.querySelectorAll('[data-package-card]').forEach((item) => {
                    item.classList.toggle('is-selected', item === card);
                });

                root.querySelectorAll('[data-seed-checkout-subtotal], [data-seed-checkout-total]').forEach((item) => {
                    item.textContent = price;
                });
            });
        });
    })();
</script>

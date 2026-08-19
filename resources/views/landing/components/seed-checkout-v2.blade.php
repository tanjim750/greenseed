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

<section
    class="landing-component seed-checkout-v2 {{ $scope }}"
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
        --seed-v2-bg: {{ $resolvedStyle['background_color'] ?? '#faf9f5' }};
        --seed-v2-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --seed-v2-soft: {{ $resolvedStyle['soft_background'] ?? '#f4f4f0' }};
        --seed-v2-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        --seed-v2-accent: {{ $resolvedStyle['accent_color'] ?? '#2e7d32' }};
        --seed-v2-border: {{ $resolvedStyle['border_color'] ?? '#bfcaba' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-v2-inner">
        <div class="seed-v2-heading">
            @if(!empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif
            @if(!empty($content['subheading']))
                <p>{{ $content['subheading'] }}</p>
            @endif
        </div>

        <form class="seed-v2-grid" data-landing-order-form>
            <input type="hidden" name="product_id" value="{{ $productId ?? '' }}">

            <div class="seed-v2-left">
                <section class="seed-v2-card seed-v2-customer-card">
                    <div class="seed-v2-card-title">
                        <span class="material-symbols-outlined">person</span>
                        <h3>{{ $content['customer_heading'] ?? '' }}</h3>
                    </div>

                    <label>
                        <span>আপনার সম্পূর্ন নাম*</span>
                        <input type="text" name="first_name" placeholder="যেমন: আব্দুর রহমান" required>
                    </label>

                    <label>
                        <span>আপনার মোবাইল নাম্বার* (১১ ডিজিট)</span>
                        <input type="tel" name="mobile" placeholder="০১৮XXXXXXXX" required>
                    </label>

                    <label>
                        <span>আপনার সম্পূর্ন ঠিকানা*</span>
                        <textarea name="shipping_address" rows="3" placeholder="থানা, জেলা এবং ডেলিভারী ম্যান কোথায় আসবে?" required></textarea>
                    </label>
                </section>

                <div class="seed-v2-trust">
                    <span class="material-symbols-outlined">verified</span>
                    <div>
                        <strong>{{ $content['trust_title'] ?? '' }}</strong>
                        <p>{{ $content['trust_description'] ?? '' }}</p>
                    </div>
                </div>
            </div>

            <div class="seed-v2-right">
                <section class="seed-v2-card seed-v2-product-card">
                    <h3>{{ $content['product_heading'] ?? '' }}</h3>

                    @if(!$selectedProduct || $displayPackages->isEmpty())
                        <div class="seed-v2-empty-product">
                            <span class="material-symbols-outlined">inventory_2</span>
                            <div>
                                <strong>{{ $selectedProduct ? 'No available package' : 'No product selected' }}</strong>
                                <small>{{ $selectedProduct ? 'Update package quantities or product stock before publishing.' : 'Select a product in the builder before publishing.' }}</small>
                            </div>
                        </div>
                    @else
                        <div class="seed-v2-packages">
                            @foreach($displayPackages as $package)
                                @php
                                    $quantity = (int) ($package['quantity'] ?? 1);
                                    $isSelected = (int) ($selectedPackage['quantity'] ?? $defaultQuantity) === $quantity;
                                @endphp
                                <label class="seed-v2-package @if($isSelected) is-selected @endif" data-package-card data-price="{{ $package['price'] ?? '' }}">
                                    <input type="radio" name="quantity" value="{{ $quantity }}" @checked($isSelected)>
                                    <img src="{{ $selectedProduct['image_url'] ?? asset('images/no_found.png') }}" alt="{{ $selectedProduct['name'] ?? 'Selected product' }}">
                                    <span class="seed-v2-package-info">
                                        <strong>{{ $package['title'] ?? '' }}</strong>
                                        @if(!empty($package['subtitle']))
                                            <small>{{ $package['subtitle'] }}</small>
                                        @endif
                                        <span class="seed-v2-package-price">
                                            @if(!empty($package['has_custom_price']) && !empty($package['original_price']) && $package['original_price'] !== ($package['price'] ?? null))
                                                <s>{{ $package['original_price'] }}</s>
                                            @endif
                                            <b>{{ $package['price'] ?? '' }}</b>
                                        </span>
                                    </span>
                                    <span class="seed-v2-radio" aria-hidden="true"><i></i></span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="seed-v2-card seed-v2-summary-card">
                    <h3>{{ $content['summary_heading'] ?? '' }}</h3>
                    <div class="seed-v2-summary">
                        <div><span>উপ-মোট (Subtotal)</span><strong data-seed-v2-subtotal>{{ $selectedPrice }}</strong></div>
                        <div><span>শিপিং (ফ্রি ডেলিভারী)</span><strong>০৳</strong></div>
                        <div class="seed-v2-total"><span>সর্বমোট (Total)</span><strong data-seed-v2-total>{{ $selectedPrice }}</strong></div>
                    </div>

                    <div class="seed-v2-payment">
                        <strong><span class="material-symbols-outlined">payments</span>{{ $content['payment_title'] ?? '' }}</strong>
                        <p>{{ $content['payment_description'] ?? '' }}</p>
                    </div>

                    <button type="submit" @disabled(!$selectedProduct || $displayPackages->isEmpty())>
                        {{ $content['button_text'] ?? 'Order Now' }}
                        <span data-seed-v2-cta-price>{{ $selectedPrice }}</span>
                    </button>
                    @if(!empty($content['secure_text']))
                        <p class="seed-v2-secure">{{ $content['secure_text'] }}</p>
                    @endif
                    <div class="landing-order-message" data-order-submission-message aria-live="polite"></div>
                </section>

                @if(!empty($content['whatsapp_text']))
                    <a class="seed-v2-whatsapp" href="{{ $render->href($content['whatsapp_url'] ?? '#') }}">
                        <span class="material-symbols-outlined">chat</span>
                        {{ $content['whatsapp_text'] }}
                    </a>
                @endif
            </div>
        </form>
    </div>
</section>

<script>
    (() => {
        const root = document.currentScript.previousElementSibling;

        if (!root || !root.classList.contains('seed-checkout-v2')) {
            return;
        }

        root.querySelectorAll('[data-package-card] input[type="radio"]').forEach((input) => {
            input.addEventListener('change', () => {
                const card = input.closest('[data-package-card]');
                const price = card?.dataset.price || '';

                root.querySelectorAll('[data-package-card]').forEach((item) => {
                    item.classList.toggle('is-selected', item === card);
                });
                root.querySelectorAll('[data-seed-v2-subtotal], [data-seed-v2-total], [data-seed-v2-cta-price]').forEach((item) => {
                    item.textContent = price;
                });
            });
        });
    })();
</script>

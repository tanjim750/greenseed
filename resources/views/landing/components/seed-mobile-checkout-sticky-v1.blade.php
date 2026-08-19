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
            'title' => $package['title'] ?: ($quantity > 1 ? $quantity . ' x ' . ($selectedProduct['name'] ?? '') : ($selectedProduct['name'] ?? '')),
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
        'actions' => ['orderSubmission' => ['url' => route('dynamic_landing.actions.store', ['actionKey' => 'order-submission'])]],
        'csrfToken' => csrf_token(),
    ];
@endphp

<section
    class="landing-component seed-mobile-block seed-mobile-checkout {{ $scope }}"
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
        --seed-mobile-bg: {{ $resolvedStyle['background_color'] ?? '#faf9f5' }};
        --seed-mobile-card: {{ $resolvedStyle['card_background'] ?? '#ffffff' }};
        --seed-mobile-primary: {{ $resolvedStyle['primary_color'] ?? '#0d631b' }};
        --seed-mobile-soft: {{ $resolvedStyle['soft_background'] ?? '#f4f4f0' }};
        --seed-mobile-border: {{ $resolvedStyle['border_color'] ?? '#bfcaba' }};
        {!! $render->layoutStyleVariables($resolvedStyle) !!}
    "
>
    <div class="seed-mobile-inner">
        <form data-landing-order-form>
            <input type="hidden" name="product_id" value="{{ $productId ?? '' }}">

            <div class="seed-mobile-card seed-mobile-product-card">
                <h2><span class="material-symbols-outlined">inventory_2</span>{{ $content['product_heading'] ?? '' }}</h2>
                @if(!$selectedProduct || $displayPackages->isEmpty())
                    <div class="seed-mobile-empty-product">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <div>
                            <strong>{{ $selectedProduct ? 'No available package' : 'No product selected' }}</strong>
                            <small>{{ $selectedProduct ? 'Update package quantities or product stock before publishing.' : 'Select a product in the builder before publishing.' }}</small>
                        </div>
                    </div>
                @else
                    <div class="seed-mobile-package-list">
                        @foreach($displayPackages as $package)
                            @php
                                $quantity = (int) ($package['quantity'] ?? 1);
                                $isSelected = (int) ($selectedPackage['quantity'] ?? $defaultQuantity) === $quantity;
                            @endphp
                            <label class="seed-mobile-package @if($isSelected) is-selected @endif" data-mobile-package-card data-price="{{ $package['price'] ?? '' }}">
                                <input type="radio" name="quantity" value="{{ $quantity }}" @checked($isSelected)>
                                <img src="{{ $selectedProduct['image_url'] ?? asset('images/no_found.png') }}" alt="{{ $selectedProduct['name'] ?? 'Selected product' }}">
                                <span>
                                    <strong>{{ $package['title'] ?? '' }}</strong>
                                    @if(!empty($package['subtitle']))
                                        <small>{{ $package['subtitle'] }}</small>
                                    @endif
                                    <span class="seed-mobile-package-price">
                                        @if(!empty($package['has_custom_price']) && !empty($package['original_price']) && $package['original_price'] !== ($package['price'] ?? null))
                                            <s>{{ $package['original_price'] }}</s>
                                        @endif
                                        <b>{{ $package['price'] ?? '' }}</b>
                                    </span>
                                </span>
                                @if(!empty($package['badge']))
                                    <em>{{ $package['badge'] }}</em>
                                @endif
                                <i class="material-symbols-outlined">check</i>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="seed-mobile-card seed-mobile-form-card">
                <h2><span class="material-symbols-outlined">person</span>{{ $content['customer_heading'] ?? '' }}</h2>
                <label><span>আপনার সম্পূর্ণ নাম *</span><input type="text" name="first_name" placeholder="আপনার নাম লিখুন" required></label>
                <label><span>মোবাইল নাম্বার *</span><input type="tel" name="mobile" placeholder="০১৭XXXXXXXX" required></label>
                <label><span>পূর্ণ ঠিকানা (জেলা ও থানা সহ) *</span><textarea name="shipping_address" rows="3" placeholder="বিস্তারিত ঠিকানা লিখুন" required></textarea></label>
            </div>

            <div class="seed-mobile-card seed-mobile-summary">
                <h2><span class="material-symbols-outlined">receipt_long</span>{{ $content['summary_heading'] ?? '' }}</h2>
                <div><span>সাব-টোটাল:</span><strong data-mobile-subtotal>{{ $selectedPrice }}</strong></div>
                <div><span>ডেলিভারি চার্জ:</span><strong>ফ্রি (০৳)</strong></div>
                <div class="total"><span>সর্বমোট:</span><strong data-mobile-total>{{ $selectedPrice }}</strong></div>
                @if(!empty($content['guarantee_note']))
                    <p>{{ $content['guarantee_note'] }}</p>
                @endif
                <div class="landing-order-message" data-order-submission-message aria-live="polite"></div>
            </div>

            <div class="seed-mobile-sticky-cta">
                <button type="submit" @disabled(!$selectedProduct || $displayPackages->isEmpty())>
                    {{ $content['button_text'] ?? 'Order Now' }}
                    <span class="material-symbols-outlined">shopping_bag</span>
                </button>
                @if(!empty($content['support_phone']))
                    <a href="tel:{{ $content['support_phone'] }}">{{ $content['support_text'] ?? 'Call' }}: <strong>{{ $content['support_phone'] }}</strong></a>
                @endif
            </div>
        </form>
    </div>
</section>

<script>
    (() => {
        const root = document.currentScript.previousElementSibling;

        if (!root || !root.classList.contains('seed-mobile-checkout')) {
            return;
        }

        root.querySelectorAll('[data-mobile-package-card] input[type="radio"]').forEach((input) => {
            input.addEventListener('change', () => {
                const card = input.closest('[data-mobile-package-card]');
                const price = card?.dataset.price || '';

                root.querySelectorAll('[data-mobile-package-card]').forEach((item) => {
                    item.classList.toggle('is-selected', item === card);
                });
                root.querySelectorAll('[data-mobile-subtotal], [data-mobile-total]').forEach((item) => {
                    item.textContent = price;
                });
            });
        });
    })();
</script>

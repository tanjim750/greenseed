{{-- resources/views/frontend/products/partials/product_section.blade.php --}}

@php
    $data = getProductInfo($product);

    use App\Models\Information;
    use App\Models\Variation;
    use App\Models\ProductStock;
    use Illuminate\Support\Str;

    $info = Information::first();
    $curr = $info->currency ?? 'BDT';

    $brandGradient = $info->gradient_code ?? 'linear-gradient(135deg,#0d6efd,#00276C)';
    $brandText     = $info->primary_color ?? '#ffffff';

    // Fallback for $adminText if not passed from parent
    $adminText = $adminText ?? \App\Models\AdminText::first();

    $productParam = $product->slug ?: $product->id;

    $variationId = optional($product->variation)->id
        ?? optional($product->variations->first())->id
        ?? Variation::where('product_id', $product->id)->orderBy('id','asc')->value('id');

    $isVariable = $product->type === 'variable';

    $productStock   = (int)($product->stock_quantity ?? 0);
    $isStockEnabled = (int)($product->is_stock ?? 0) === 1;

    $variationStock = 0;
    if($product->type === 'single' && $isStockEnabled && (int)$variationId > 0){
        $variationStock = (int) ProductStock::where('variation_id', (int)$variationId)->sum('quantity');
    }

    $qtySource = ($product->type === 'single' && $isStockEnabled && (int)$variationId > 0)
        ? $variationStock
        : $productStock;

    $isOut = ($product->type === 'single') && ( $isStockEnabled ? ($qtySource <= 0) : ($productStock <= 0) );

    $hasDiscount = ($product->after_discount ?? 0) > 0;
    $discountPercent = 0;
    if($hasDiscount){
        $p = (float)($product->sell_price ?? 0);
        $a = (float)($product->after_discount ?? 0);
        $discountPercent = $p > 0 ? round((($p - $a) / $p) * 100, 0) : 0;
    }

    $isFreeShipping = (int)($product->is_free_shipping ?? 0) === 1;
@endphp

@once
<style>
    :root {
        /* Brand Colors from Information */
        --brand-gradient: {!! $brandGradient !!};
        --brand-text: {{ $brandText }};
        
        /* Dynamic Colors from AdminText */
        --card-bg: {{ $adminText->bg_color ?? '#ffffff' }};
        --card-border: {{ $adminText->border_color ?? '#efe9e3' }};
        --thumb-bg: {{ $adminText->section_bg ?? '#f4f1ec' }};
        --text-main: {{ $adminText->text_color ?? '#0f172a' }};
        --text-muted: {{ $adminText->text_muted_color ?? '#9aa0a6' }};
        --primary-color: {{ $adminText->primary_color ?? '#000000' }};
        --primary-hover: {{ $adminText->primary_hover_color ?? '#222222' }};
    }

    /* ============================================================
       CARD
       ============================================================ */
    .axil-product.product-style-one {
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: var(--card-bg) !important;
        border: 1px solid var(--card-border) !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow: 0 1px 2px rgba(15,23,42,.03) !important;
        transition:
            transform .45s cubic-bezier(.22,.61,.36,1),
            box-shadow .45s cubic-bezier(.22,.61,.36,1),
            border-color .35s ease !important;
    }
    .axil-product.product-style-one:hover {
        transform: translateY(-4px) !important;
        border-color: rgba(15,23,42,.10) !important;
        box-shadow:
            0 18px 32px -16px rgba(15,23,42,.18),
            0 6px 14px -6px rgba(15,23,42,.06) !important;
    }

    /* ============================================================
       THUMBNAIL
       ============================================================ */
    .axil-product .thumbnail {
        position: relative !important;
        background: var(--thumb-bg) !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        padding: 0 !important;
        aspect-ratio: 1 / 1;
    }
    .axil-product .thumbnail a {
        display: block; width: 100%; height: 100%;
        position: relative; z-index: 1;
    }
    .axil-product .thumbnail img.product_img {
        width: 100% !important;
        height: 100% !important;
        aspect-ratio: 1 / 1;
        object-fit: cover !important;
        transition: transform .9s cubic-bezier(.22,.61,.36,1) !important;
        display: block;
    }
    .axil-product.product-style-one:hover .thumbnail img.product_img {
        transform: scale(1.06) !important;
    }

    /* ============================================================
       LEFT BADGE STACK — tiny, all black text
       ============================================================ */
    .axil-product .label-block.label-left {
        position: absolute !important;
        top: 7px !important;
        left: 7px !important;
        z-index: 6 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
        align-items: flex-start;
    }
    .axil-product .mini-badge {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2.5px 7px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 9px;
        line-height: 1;
        letter-spacing: .3px;
        color: var(--text-main);
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        box-shadow: 0 2px 6px rgba(0,0,0,.10);
        font-family: 'Hind Siliguri', sans-serif;
        overflow: hidden;
        animation: badgeIn .5s cubic-bezier(.22,.61,.36,1) both;
    }
    .axil-product .mini-badge i { font-size: 7px; color: var(--text-main); }
    .axil-product .mini-badge::before {
        content: "";
        position: absolute;
        top: 0; left: -150%;
        width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.85), transparent);
        transform: skewX(-20deg);
        animation: badgeShine 4s ease-in-out infinite;
        animation-delay: 1.2s;
    }

    /* Discount — white bg + black text + brand-tinted edge glow */
    .axil-product .mini-badge.is-discount {
        font-size: 10px;
        padding: 3px 8px;
        position: relative;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .axil-product .mini-badge.is-discount::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 999px;
        padding: 1.5px;
        background: var(--brand-gradient);
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
                mask-composite: exclude;
        pointer-events: none;
        animation: discountGlow 2.5s ease-in-out infinite;
    }
    @keyframes discountGlow {
        0%, 100% { opacity: .55; }
        50%      { opacity: 1; }
    }

    /* Free ship */
    .axil-product .mini-badge.is-shipping {
        background: #ecfdf5;
        border-color: rgba(16,185,129,.30);
        color: #065f46;
        box-shadow: 0 2px 6px rgba(16,185,129,.18);
        animation:
            badgeIn .5s cubic-bezier(.22,.61,.36,1) both,
            freeShipFloat 2.4s ease-in-out infinite;
    }
    .axil-product .mini-badge.is-shipping i {
        color: #059669;
        animation: truckMove 2.5s ease-in-out infinite;
    }
    @keyframes freeShipFloat {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-2px); }
    }

    /* Stock out */
    .axil-product .mini-badge.is-out {
        background: #fee2e2;
        border-color: rgba(239,68,68,.30);
        color: #7f1d1d;
        box-shadow: 0 2px 6px rgba(239,68,68,.18);
        animation:
            badgeIn .5s cubic-bezier(.22,.61,.36,1) both,
            outPulse 2.2s ease-in-out infinite;
    }
    .axil-product .mini-badge.is-out i { color: #ef4444; }

    @keyframes badgeIn {
        from { opacity: 0; transform: translateX(-8px) scale(.92); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }
    @keyframes badgeShine {
        0%, 60%   { left: -150%; }
        80%, 100% { left: 150%; }
    }
    @keyframes outPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.35), 0 2px 6px rgba(239,68,68,.18); }
        50%      { box-shadow: 0 0 0 5px rgba(239,68,68,0),   0 2px 6px rgba(239,68,68,.18); }
    }
    @keyframes truckMove {
        0%, 100% { transform: translateX(0); }
        50%      { transform: translateX(2px); }
    }

    /* ============================================================
       RIGHT LABEL STACK (Cart Button & Type Icon)
       ============================================================ */
    .axil-product .label-block.label-right {
        position: absolute !important;
        top: 7px !important;
        right: 7px !important;
        z-index: 6 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center;
        gap: 6px !important;
    }

    /* Cart Button */
    .axil-product .corner-cart-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--card-bg);
        color: var(--text-main) !important;
        border: 1px solid var(--card-border);
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        text-decoration: none !important;
        cursor: pointer;
        font-size: 13px;
        overflow: hidden;
        transition:
            transform .35s cubic-bezier(.34,1.56,.64,1),
            color .3s ease,
            box-shadow .35s ease,
            border-color .3s ease;
        animation: cornerBtnIn .5s cubic-bezier(.22,.61,.36,1) both;
    }
    @keyframes cornerBtnIn {
        from { opacity: 0; transform: translateX(8px) scale(.85); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }
    .axil-product .corner-cart-btn::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: var(--brand-gradient);
        opacity: 0;
        transition: opacity .3s ease;
        z-index: 0;
    }
    .axil-product .corner-cart-btn i {
        position: relative;
        z-index: 1;
        transition: transform .35s cubic-bezier(.34,1.56,.64,1);
    }
    .axil-product .corner-cart-btn:hover {
        transform: translateY(-2px) scale(1.08);
        color: var(--brand-text) !important;
        border-color: transparent;
        box-shadow: 0 10px 22px rgba(15,23,42,.20);
    }
    .axil-product .corner-cart-btn:hover::before { opacity: 1; }
    .axil-product .corner-cart-btn:hover i { animation: cartWobble .6s ease; }
    .axil-product .corner-cart-btn:active { transform: translateY(0) scale(.94); }
    .axil-product .corner-cart-btn .fa-spinner { animation: cartSpin .8s linear infinite; }
    
    @keyframes cartWobble {
        0%,100% { transform: rotate(0) scale(1); }
        25%     { transform: rotate(-12deg) scale(1.15); }
        50%     { transform: rotate(0) scale(1.15); }
        75%     { transform: rotate(8deg) scale(1.15); }
    }
    @keyframes cartSpin { from { transform: rotate(0); } to { transform: rotate(360deg); } }

    /* Type Icon (Below Cart) */
    .axil-product .type-icon-only {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--card-bg);
        color: var(--text-main);
        border: 1px solid var(--card-border);
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        font-size: 11px;
        transition: transform .35s ease, box-shadow .35s ease;
        animation: cornerBtnIn .6s cubic-bezier(.22,.61,.36,1) both;
        animation-delay: 0.1s;
    }
    .axil-product.product-style-one:hover .type-icon-only {
        transform: translateY(-2px) scale(1.08);
        box-shadow: 0 6px 14px rgba(15,23,42,.15);
    }
    .axil-product .type-icon-only.is-variable {
        background: var(--brand-gradient);
        color: var(--brand-text);
        border: none;
    }
    .axil-product .type-icon-only.is-variable::after {
        content: "";
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        border: 2px solid var(--brand-gradient);
        opacity: .35;
        animation: chipRing 1.8s ease-out infinite;
        pointer-events: none;
    }
    @keyframes chipRing {
        0%   { transform: scale(.8); opacity: .55; }
        100% { transform: scale(1.6); opacity: 0; }
    }

    /* ============================================================
       CONTENT
       ============================================================ */
    .axil-product .product-content {
        padding: 14px 14px !important;
        text-align: left !important;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        background: var(--card-bg);
        gap: 6px;
    }
    .axil-product .product-content .title {
        margin: 0 !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        font-size: 14.5px !important;
        min-height: unset !important;
    }
    .axil-product .product-content .title a {
        color: var(--text-main) !important;
        text-decoration: none !important;
        display: block !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        font-family: 'Hind Siliguri', sans-serif !important;
        transition: color .3s ease, letter-spacing .3s ease;
    }
    .axil-product.product-style-one:hover .product-content .title a {
        letter-spacing: .2px;
        color: var(--primary-color) !important;
    }

    /* ============================================================
       PRICE — Dynamic Primary Color
       ============================================================ */
    .axil-product .product-price-variant {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 !important;
        padding: 0 !important;
        flex-wrap: nowrap;
        overflow: hidden;
    }
    .axil-product .product-price-variant .old-price {
        font-family: 'Hind Siliguri', sans-serif !important;
        font-size: 13px !important;
        color: var(--text-muted) !important;
        text-decoration: line-through !important;
        font-weight: 500 !important;
        line-height: 1.2;
        white-space: nowrap;
        order: 1;
    }
    .axil-product .product-price-variant .current-price {
        font-family: 'Hind Siliguri', sans-serif !important;
        font-size: 16px !important;
        font-weight: 800 !important;
        color: var(--primary-color) !important; /* ✅ Dynamic Primary */
        line-height: 1.2;
        white-space: nowrap;
        letter-spacing: -.2px;
        order: 2;
        animation: priceFadeIn .6s ease both;
    }
    @keyframes priceFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ============================================================
       STOCK OUT
       ============================================================ */
    .axil-product.product-style-one[data-is-out="1"] .thumbnail img.product_img {
        opacity: .55 !important;
        filter: grayscale(100%) !important;
    }
    .axil-product.product-style-one[data-is-out="1"]:hover .thumbnail img.product_img {
        transform: none !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-content .title a {
        color: var(--text-muted) !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-price-variant .current-price {
        color: var(--text-muted) !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .product-price-variant .old-price {
        color: rgba(154, 160, 166, 0.6) !important;
    }
    .axil-product.product-style-one[data-is-out="1"]:hover {
        transform: none !important;
        border-color: var(--card-border) !important;
        box-shadow: 0 1px 2px rgba(15,23,42,.03) !important;
    }
    .axil-product.product-style-one[data-is-out="1"] .corner-cart-btn {
        background: var(--card-bg) !important;
        color: var(--text-muted) !important;
        cursor: not-allowed;
        pointer-events: none;
        box-shadow: none !important;
        border-color: var(--card-border) !important;
        opacity: 0.6;
    }
    .axil-product.product-style-one[data-is-out="1"] .corner-cart-btn::before { display: none !important; }
    .axil-product.product-style-one[data-is-out="1"] .type-icon-only {
        opacity: .6;
        filter: grayscale(100%);
        animation: cornerBtnIn .5s cubic-bezier(.34,1.56,.64,1) both !important;
    }

    /* ============================================================
       ✨ VIEW ALL — animated chip with brand gradient
       ============================================================ */
    .section-header a,
    .view-all-link {
        position: relative !important;
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 8px 18px 8px 16px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: var(--brand-text) !important;
        background: var(--brand-gradient) !important;
        border-radius: 999px !important;
        text-decoration: none !important;
        overflow: hidden;
        isolation: isolate;
        box-shadow: 0 6px 16px -4px rgba(15,23,42,.20);
        transition:
            transform .35s cubic-bezier(.22,.61,.36,1),
            box-shadow .35s ease,
            letter-spacing .35s ease;
        white-space: nowrap;
    }
    .section-header a::before,
    .view-all-link::before {
        content: "";
        position: absolute;
        top: 0; left: -120%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.45), transparent);
        transform: skewX(-20deg);
        transition: left .8s cubic-bezier(.22,.61,.36,1);
        z-index: 0;
    }
    .section-header a::after,
    .view-all-link::after {
        content: "→" !important;
        position: relative;
        font-size: 13px;
        font-weight: 800;
        display: inline-block;
        transition: transform .35s cubic-bezier(.34,1.56,.64,1);
        z-index: 1;
    }
    .section-header a > *,
    .view-all-link > * { position: relative; z-index: 1; }
    .section-header a:hover,
    .view-all-link:hover {
        transform: translateY(-2px) !important;
        letter-spacing: 1px;
        color: var(--brand-text) !important;
        text-decoration: none !important;
        box-shadow: 0 12px 22px -6px rgba(15,23,42,.28);
    }
    .section-header a:hover::before,
    .view-all-link:hover::before { left: 130%; }
    .section-header a:hover::after,
    .view-all-link:hover::after  { transform: translateX(5px); }
    .section-header a:active,
    .view-all-link:active        { transform: translateY(0) !important; }

    /* ============================================================
       MOBILE
       ============================================================ */
    @media (max-width: 575.98px) {
        .axil-product.product-style-one { border-radius: 12px !important; }
        .axil-product .product-content { padding: 12px 11px !important; gap: 5px; }
        .axil-product .product-content .title { font-size: 13px !important; }
        .axil-product .product-price-variant .current-price { font-size: 14.5px !important; }
        .axil-product .product-price-variant .old-price { font-size: 12px !important; }

        .axil-product .label-block.label-left  { top: 5px !important; left: 5px !important; gap: 2.5px !important; }
        .axil-product .label-block.label-right { top: 5px !important; right: 5px !important; }

        .axil-product .mini-badge { font-size: 8px; padding: 2px 6px; gap: 2px; }
        .axil-product .mini-badge.is-discount { font-size: 9px; padding: 2.5px 7px; }
        .axil-product .mini-badge i { font-size: 6.5px; }

        .axil-product .corner-cart-btn { width: 30px; height: 30px; font-size: 12px; }
        .axil-product .type-icon-only { width: 24px; height: 24px; font-size: 10px; }

        .section-header a,
        .view-all-link { font-size: 11px !important; padding: 6px 14px 6px 12px !important; }
        .section-header a::after,
        .view-all-link::after { font-size: 12px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .axil-product, .axil-product *, .axil-product *::before, .axil-product *::after,
        .section-header a, .section-header a::before, .section-header a::after {
            animation-duration: .001ms !important;
            transition-duration: .001ms !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if(window.ajaxCartInitialized) return;
    window.ajaxCartInitialized = true;

    document.body.addEventListener('click', function(e) {
        let btn = e.target.closest('.ajax-add-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        if (btn.disabled) return;

        let originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner"></i>';
        btn.disabled = true;

        let url = btn.getAttribute('data-url');
        let formData = new FormData();
        formData.append('_token', btn.getAttribute('data-token'));
        formData.append('product_id', btn.getAttribute('data-product'));
        let variationId = btn.getAttribute('data-variation');
        if(variationId) formData.append('variation_id', variationId);
        formData.append('quantity', 1);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(r => r.json().catch(() => ({})))
        .then(res => {
            btn.innerHTML = '<i class="fas fa-check" style="color:#16a34a;"></i>';

            if (res && typeof res.item !== 'undefined') {
                document.querySelectorAll('.cart-count, .cart-item-count, .pro-count')
                    .forEach(el => el.textContent = res.item);
            }
            if (res && res.amount) {
                document.querySelectorAll('.cart-amount').forEach(el => {
                    const t = String(res.amount);
                    el.textContent = (t.includes('৳') || t.includes('$')) ? t : '৳ ' + t;
                });
            }
            if (res && (res.view || res.html)) {
                const target = document.querySelector('#cart_section, #cart-dropdown, .cart-dropdown-wrap');
                if(target) target.innerHTML = res.view || res.html;
            }

            if (window.toastr) toastr.success((res && res.msg) || 'Added to cart');

            setTimeout(() => {
                btn.innerHTML = originalIcon;
                btn.disabled = false;
            }, 1500);
        })
        .catch(error => {
            console.error("Cart Add Error:", error);
            btn.innerHTML = originalIcon;
            btn.disabled = false;
            if (window.toastr) toastr.error('Failed to add to cart');
        });
    });
});
</script>
@endonce

<div class="axil-product product-style-one" data-is-out="{{ $isOut ? 1 : 0 }}" data-is-variable="{{ $isVariable ? 1 : 0 }}">

    <div class="thumbnail">
        <a href="{{ route('front.products.show', ['product' => $productParam]) }}">
            <img src="{{ getImage('thumb_products', $product->image) }}" class="product_img" alt="{{ $product->name }}">
        </a>

        {{-- TOP-LEFT tiny badges: Discount + Free Ship + Stock Out --}}
        @if($hasDiscount || $isFreeShipping || ($isOut && !$isVariable))
            <div class="label-block label-left">
                @if($hasDiscount)
                    <div class="mini-badge is-discount" title="{{ $discountPercent }}% Off">
                        {{ $discountPercent }}% OFF
                    </div>
                @endif

                @if($isFreeShipping)
                    <div class="mini-badge is-shipping" title="Free Shipping">
                        <i class="fas fa-shipping-fast"></i> Free
                    </div>
                @endif

                @if($isOut && !$isVariable)
                    <div class="mini-badge is-out" title="Out of Stock">
                        <i class="fas fa-circle" style="font-size:5px;"></i> Out
                    </div>
                @endif
            </div>
        @endif

        {{-- TOP-RIGHT cart button & type icon --}}
        <div class="label-block label-right">
            @if($isVariable)
                <a href="{{ route('front.products.show', ['product' => $productParam]) }}"
                   class="corner-cart-btn" title="Select Options">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            @elseif($isOut)
                <button type="button" class="corner-cart-btn" disabled title="Out of Stock">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            @else
                <button type="button" class="corner-cart-btn ajax-add-btn"
                    data-url="{{ route('front.carts.store') }}"
                    data-product="{{ $product->id }}"
                    data-variation="{{ $variationId }}"
                    data-token="{{ csrf_token() }}"
                    title="Add to Cart">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            @endif

            {{-- ✨ TYPE ICON (Single / Variable) — Sits directly under the cart button --}}
            @if($isVariable)
                <div class="type-icon-only is-variable" title="Variable Product">
                    <i class="fas fa-layer-group"></i>
                </div>
            @else
                <div class="type-icon-only is-single" title="Single Product">
                    <i class="fas fa-box"></i>
                </div>
            @endif
        </div>
    </div>

    <div class="product-content">
        <h5 class="title">
            <a href="{{ route('front.products.show', ['product' => $productParam]) }}"
               title="{{ $product->name }}">
                {{ $product->name }}
            </a>
        </h5>

        <div class="product-price-variant">
            @if($hasDiscount)
                <span class="price old-price">
                    @if($curr == 'BDT') ৳ {{ number_format((int)($data['old_price'] ?? 0)) }}
                    @elseif($curr == 'Dollar') $ {{ $data['old_price'] ?? 0 }}
                    @elseif($curr == 'Euro') {{ $data['old_price'] ?? 0 }}
                    @elseif($curr == 'Rupee') {{ $data['old_price'] ?? 0 }}
                    @endif
                </span>
            @endif

            <span class="price current-price">
                @if($curr == 'BDT') ৳ {{ number_format((int)($data['price'] ?? 0)) }}
                @elseif($curr == 'Dollar') $ {{ $data['price'] ?? 0 }}
                @elseif($curr == 'Euro') {{ $data['price'] ?? 0 }}
                @elseif($curr == 'Rupee') {{ $data['price'] ?? 0 }}
                @endif
            </span>
        </div>
    </div>

</div>
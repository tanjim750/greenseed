@php
    $layoutMargin = implode(' ', $themeTokens['layout']['margin'] ?? ['0', '0', '0', '0']);
    $layoutPadding = implode(' ', $themeTokens['layout']['padding'] ?? ['0', '0', '0', '0']);
@endphp
<!doctype html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="landing-page-root"
    style="
        --landing-primary: {{ $themeTokens['primary'] }};
        --landing-secondary: {{ $themeTokens['secondary'] }};
        --landing-background: {{ $themeTokens['background'] }};
        --landing-surface: {{ $themeTokens['surface'] }};
        --landing-text: {{ $themeTokens['text'] }};
        --landing-muted-text: {{ $themeTokens['muted_text'] }};
        --landing-page-margin: {{ $layoutMargin }};
        --landing-page-padding: {{ $layoutPadding }};
    "
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->seo['title'] ?? $page->name }}</title>
    @if(!empty($page->seo['description']))
        <meta name="description" content="{{ $page->seo['description'] }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .landing-page-root{min-height:100%;background:var(--landing-background)}
        .landing-page-root,.landing-page-root body{box-sizing:border-box}
        .landing-page{min-height:100%;margin:var(--landing-page-margin,0);padding:var(--landing-page-padding,0);font-family:"Noto Sans Bengali",Arial,Helvetica,sans-serif;color:var(--landing-text);background:var(--landing-background)}
        .landing-page-frame{box-sizing:border-box;margin:0;padding:0;background:transparent}
        .landing-page-frame *,.landing-page-frame *:before,.landing-page-frame *:after{box-sizing:border-box}
        .material-symbols-outlined{font-family:"Material Symbols Outlined";font-weight:normal;font-style:normal;font-size:24px;line-height:1;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;font-feature-settings:"liga";-webkit-font-feature-settings:"liga";-webkit-font-smoothing:antialiased;font-variation-settings:"FILL" 0,"wght" 400,"GRAD" 0,"opsz" 24}
        .landing-component{box-sizing:border-box;margin:var(--landing-component-margin,0);max-width:var(--landing-component-max-width,none);text-align:var(--landing-component-text-align,inherit)}
        .landing-section-inner{width:min(var(--landing-content-max-width,1120px),calc(100% - 32px));margin:0 auto}
        .landing-hero{background:var(--hero-background);color:var(--hero-title);padding:var(--landing-component-padding,72px 0 72px 0);text-align:var(--landing-component-text-align,center);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .landing-hero h1{margin:0 0 16px;font-size:clamp(32px,5vw,64px);line-height:1.05}
        .landing-hero p{margin:0 auto 28px;max-width:720px;font-size:18px;line-height:1.6}
        .landing-countdown{margin:0 auto 28px;font-size:24px;font-weight:700}
        .landing-button{display:inline-block;background:var(--button-color);color:#fff;text-decoration:none;padding:13px 22px;border-radius:6px;font-weight:700}
        .landing-products{background:var(--products-background);padding:var(--landing-component-padding,56px 0 56px 0);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .landing-products h2{margin:0 0 24px;font-size:32px}
        .landing-product-grid{display:grid;grid-template-columns:repeat(var(--product-columns),minmax(0,1fr));gap:18px}
        .landing-product-card{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:var(--landing-surface)}
        .landing-product-card img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}
        .landing-product-card-body{padding:14px}
        .landing-product-card h3{font-size:16px;margin:0 0 8px}
        .landing-product-card a{color:inherit;text-decoration:none}
        .landing-price{font-weight:700;color:#059669}
        .landing-stock{margin-top:6px;color:#6b7280;font-size:13px}
        .landing-order-form{display:grid;gap:8px;margin-top:12px}
        .landing-order-form input,.landing-order-form select,.landing-order-form textarea{width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:9px 10px;font:inherit;color:var(--landing-text);background:#fff}
        .landing-order-form textarea{resize:vertical;min-height:68px}
        .landing-order-form button{border:0;border-radius:6px;background:var(--landing-primary);color:#fff;padding:10px 12px;font-weight:700;cursor:pointer}
        .landing-order-form button:disabled{opacity:.65;cursor:wait}
        .landing-order-message{min-height:18px;font-size:13px;line-height:1.35}
        .landing-order-message[data-type="error"]{color:#dc2626}
        .landing-order-message[data-type="success"]{color:#059669}
        .seed-offer-hero{padding:var(--landing-component-padding,32px 0 80px 0);background:var(--landing-background)}
        .seed-hero-card{position:relative;overflow:hidden;border-radius:var(--landing-component-border-radius,24px);background:var(--seed-hero-bg);color:var(--seed-hero-text);padding:64px;display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,420px);gap:48px;align-items:center;box-shadow:var(--landing-component-box-shadow,0 20px 50px rgba(0,0,0,.14))}
        .seed-hero-card:before{content:"";position:absolute;inset:0;opacity:.1;background-image:radial-gradient(circle at 2px 2px,#fff 1px,transparent 0);background-size:24px 24px}
        .seed-hero-text,.seed-hero-visual{position:relative;z-index:1}
        .seed-offer-badge{display:inline-flex;align-items:center;gap:8px;background:var(--seed-hero-accent);color:#3e2723;border-radius:999px;padding:8px 16px;font-weight:700;margin-bottom:24px}
        .seed-offer-badge .material-symbols-outlined{font-size:20px}
        .seed-hero-card h1{margin:0 0 24px;font-size:48px;line-height:1.14}
        .seed-hero-card p{margin:0 0 32px;max-width:640px;font-size:18px;line-height:1.6;opacity:.9}
        .seed-hero-meta{display:flex;flex-wrap:wrap;gap:32px;align-items:center}
        .seed-meta-label{text-transform:uppercase;font-size:12px;font-weight:700;letter-spacing:.06em;opacity:.72;margin-bottom:8px}
        .seed-price-row{display:flex;align-items:baseline;gap:12px;font-size:48px;font-weight:800}
        .seed-price-row s{font-size:18px;opacity:.55;font-weight:400}
        .seed-countdown-card{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:16px;padding:16px;text-align:center}
        .seed-countdown-card .landing-countdown{font-size:22px;font-weight:800}
        .seed-hero-image-ring{aspect-ratio:1;border-radius:999px;border:12px solid rgba(255,255,255,.1);padding:16px}
        .seed-hero-image-ring img{width:100%;height:100%;object-fit:cover;border-radius:999px;box-shadow:0 24px 50px rgba(0,0,0,.25);background:rgba(255,255,255,.15)}
        .seed-guarantee-badge{position:absolute;left:-12px;bottom:-12px;display:flex;gap:10px;align-items:center;background:#fff;color:var(--landing-text);border-radius:16px;padding:14px 16px;box-shadow:0 12px 30px rgba(0,0,0,.16);font-weight:700}
        .seed-benefits{padding:var(--landing-component-padding,0 0 80px 0);background:var(--seed-benefits-bg);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .seed-section-heading{text-align:center;margin-bottom:48px}
        .seed-section-heading h2,.seed-gallery h2,.seed-checkout h2{margin:0;color:var(--landing-primary);font-size:32px;line-height:1.25}
        .seed-section-heading span{display:block;width:96px;height:6px;background:var(--seed-benefits-accent);border-radius:999px;margin:16px auto 0}
        .seed-benefits-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:24px}
        .seed-feature-card{grid-column:span 6;grid-row:span 2;background:var(--landing-surface);border:1px solid #d7ded2;border-radius:24px;padding:32px}
        .seed-feature-card>.material-symbols-outlined,.seed-small-card>.material-symbols-outlined{font-size:36px;color:var(--landing-primary)}
        .seed-feature-card h3{margin:20px 0 12px;font-size:24px}
        .seed-feature-card p,.seed-small-card p,.seed-trust-card p{color:var(--landing-muted-text);line-height:1.6}
        .seed-feature-card ul{list-style:none;padding:0;margin:20px 0 0;display:grid;gap:12px}
        .seed-feature-card li{display:flex;gap:10px;align-items:flex-start;color:var(--landing-muted-text)}
        .seed-feature-card li .material-symbols-outlined{color:var(--landing-primary);font-size:20px}
        .seed-small-card{grid-column:span 3;background:#fff;border:1px solid #d7ded2;border-radius:24px;padding:24px}
        .seed-small-card h4,.seed-trust-card h4{margin:16px 0 8px;font-size:18px;color:var(--landing-text)}
        .seed-trust-stack{grid-column:7 / span 6;display:grid;gap:24px}
        .seed-trust-card{display:flex;gap:24px;align-items:center;background:color-mix(in srgb,var(--landing-primary) 7%,#fff);border:1px solid color-mix(in srgb,var(--landing-primary) 15%,#fff);border-radius:24px;padding:24px}
        .seed-trust-card>.material-symbols-outlined{flex:0 0 64px;width:64px;height:64px;border-radius:16px;background:color-mix(in srgb,var(--landing-primary) 12%,#fff);display:grid;place-items:center;color:var(--landing-primary);font-size:32px}
        .seed-gallery{padding:var(--landing-component-padding,0 0 80px 0);background:var(--seed-gallery-bg);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .seed-gallery h2{text-align:center;color:var(--landing-text);margin-bottom:40px}
        .seed-gallery-grid{display:grid;grid-template-columns:repeat(var(--seed-gallery-columns),minmax(0,1fr));gap:16px}
        .seed-gallery figure{aspect-ratio:4/5;margin:0;overflow:hidden;border-radius:18px;box-shadow:0 6px 18px rgba(0,0,0,.08);background:var(--landing-surface)}
        .seed-gallery img{width:100%;height:100%;display:block;object-fit:cover;transition:transform .5s ease}
        .seed-gallery figure:hover img{transform:scale(1.05)}
        .seed-checkout{padding:var(--landing-component-padding,0 0 80px 0)}
        .seed-checkout>.landing-section-inner{background:var(--seed-checkout-bg);border:1px solid #d7ded2;border-radius:var(--landing-component-border-radius,32px);padding:48px;box-shadow:var(--landing-component-box-shadow,none)}
        .seed-checkout h2{text-align:center;margin-bottom:48px}
        .seed-order-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:48px}
        .seed-order-column{display:grid;gap:18px;align-content:start}
        .seed-step-heading{display:flex;align-items:center;gap:12px;margin-bottom:8px}
        .seed-step-heading>span{width:40px;height:40px;border-radius:999px;background:var(--landing-primary);color:#fff;display:grid;place-items:center;font-weight:800}
        .seed-step-heading h3{margin:0;font-size:22px}
        .seed-order-column label>span{display:block;margin-bottom:8px;color:var(--landing-muted-text);font-weight:700}
        .seed-order-column input,.seed-order-column textarea{width:100%;box-sizing:border-box;background:#fff;border:1px solid #d1d5db;border-radius:12px;padding:14px 16px;font:inherit;color:var(--landing-text)}
        .seed-delivery-note{display:flex;gap:16px;background:#fff;border:1px solid color-mix(in srgb,var(--landing-primary) 20%,#fff);border-radius:18px;padding:20px}
        .seed-delivery-note>.material-symbols-outlined{color:var(--landing-primary);font-size:32px}
        .seed-delivery-note strong{display:block;color:var(--landing-primary);margin-bottom:6px}
        .seed-delivery-note p{margin:0;color:var(--landing-muted-text);font-size:14px;line-height:1.5}
        .seed-package-list{display:grid;gap:16px}
        .seed-selected-product{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #d7ded2;border-radius:18px;padding:14px}
        .seed-selected-product img{width:72px;height:72px;object-fit:cover;border-radius:14px;background:var(--landing-surface)}
        .seed-selected-product div{display:grid;gap:3px}
        .seed-selected-product strong{font-size:16px;color:var(--landing-text)}
        .seed-selected-product small{color:var(--landing-muted-text);font-size:12px}
        .seed-selected-product span{color:var(--landing-primary);font-weight:800}
        .seed-selected-product-empty>.material-symbols-outlined{width:54px;height:54px;border-radius:14px;background:color-mix(in srgb,var(--landing-primary) 10%,#fff);display:grid;place-items:center;color:var(--landing-primary);font-size:28px}
        .seed-package-card{display:flex;align-items:center;gap:16px;background:#fff;border:2px solid transparent;border-radius:18px;padding:18px;cursor:pointer}
        .seed-package-card>img{width:58px;height:58px;object-fit:cover;border-radius:14px;background:var(--landing-surface);flex:0 0 58px}
        .seed-package-card:has(input:checked),.seed-package-card.is-selected{border-color:var(--landing-primary)}
        .seed-package-card input{width:22px;height:22px;accent-color:var(--landing-primary)}
        .seed-package-info{display:flex;flex:1;flex-direction:column;gap:4px;min-width:0}
        .seed-package-info strong,.seed-package-info small{display:block}
        .seed-package-info small{color:var(--landing-muted-text)}
        .seed-package-price{display:grid;justify-items:end;gap:2px;margin-left:auto;white-space:nowrap}
        .seed-package-price s,.seed-v2-package-price s,.seed-mobile-package-price s{color:var(--landing-muted-text);font-size:12px;font-weight:700;line-height:1}
        .seed-package-card b{color:var(--landing-primary);font-size:22px}
        .seed-summary-card{background:color-mix(in srgb,var(--landing-surface) 70%,#ddd);border:1px solid #d1d5db;border-radius:18px;padding:20px;display:grid;gap:12px}
        .seed-summary-card h4{margin:0 0 4px;text-transform:uppercase;font-size:13px;color:var(--landing-muted-text);letter-spacing:.06em}
        .seed-summary-card div,.seed-summary-card strong{display:flex;justify-content:space-between;gap:16px}
        .seed-summary-card strong{font-size:20px;color:var(--landing-primary);border-top:1px solid #d1d5db;padding-top:12px}
        .seed-payment-note{display:flex;gap:8px;align-items:center;margin:0;color:var(--landing-muted-text);font-size:13px}
        .seed-checkout button{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;border:0;border-radius:18px;background:var(--seed-checkout-button);color:#fff;padding:18px;font-size:18px;font-weight:800;cursor:pointer;box-shadow:0 12px 24px rgba(13,99,27,.2)}
        .seed-support{text-align:var(--landing-component-text-align,center);padding:var(--landing-component-padding,48px 0 64px 0);border-top:1px solid #d7ded2;border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .seed-support p{margin:0 0 12px;font-size:24px;font-weight:700}
        .seed-support a{display:inline-flex;align-items:center;gap:10px;background:var(--seed-support-button);color:#fff;text-decoration:none;border-radius:999px;padding:12px 28px;font-size:24px;font-weight:800}
        .seed-support-badges{display:flex;flex-wrap:wrap;justify-content:center;gap:28px;margin-top:28px;color:var(--landing-muted-text)}
        .seed-support-badges span{display:inline-flex;align-items:center;gap:8px;font-weight:700}
        .seed-footer{background:var(--seed-footer-bg);padding:var(--landing-component-padding,48px 0 48px 0);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .seed-footer .landing-section-inner{display:flex;justify-content:space-between;align-items:center;gap:32px}
        .seed-footer strong{display:block;color:var(--landing-primary);font-size:28px;margin-bottom:8px}
        .seed-footer p{margin:0;color:var(--landing-muted-text);max-width:420px}
        .seed-footer nav{display:flex;flex-wrap:wrap;gap:24px;justify-content:flex-end}
        .seed-footer a{color:var(--landing-muted-text);text-decoration:none}
        .seed-footer a:hover{text-decoration:underline}
        .seed-checkout-v2{background:var(--seed-v2-bg);padding:var(--landing-component-padding,48px 0 64px 0);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .seed-v2-inner{width:min(var(--landing-content-max-width,1152px),calc(100% - 32px));margin:0 auto}
        .seed-v2-heading{text-align:center;margin-bottom:48px}
        .seed-v2-heading h2{margin:0 0 16px;color:var(--seed-v2-primary);font-size:48px;line-height:1.17;font-weight:800}
        .seed-v2-heading p{margin:0 auto;color:var(--landing-muted-text);font-size:18px;line-height:1.55;max-width:760px}
        .seed-v2-grid{display:grid;grid-template-columns:minmax(0,7fr) minmax(320px,5fr);gap:48px;align-items:start}
        .seed-v2-left,.seed-v2-right{display:grid;gap:32px}
        .seed-v2-right{position:sticky;top:96px}
        .seed-v2-card{background:var(--seed-v2-card);border:1px solid var(--seed-v2-border);border-radius:18px;box-shadow:0 10px 40px -10px rgba(121,85,72,.15);padding:32px}
        .seed-v2-right .seed-v2-card{padding:24px}
        .seed-v2-card h3{margin:0 0 24px;color:var(--landing-text);font-size:24px;line-height:1.3;font-weight:800}
        .seed-v2-card-title{display:flex;align-items:center;gap:12px;margin-bottom:32px}
        .seed-v2-card-title h3{margin:0}
        .seed-v2-card-title>.material-symbols-outlined{color:var(--seed-v2-primary);font-size:32px;font-variation-settings:"FILL" 1}
        .seed-v2-card label:not(.seed-v2-package){display:block;margin-bottom:24px}
        .seed-v2-card label:not(.seed-v2-package)>span{display:block;color:var(--landing-muted-text);font-weight:800;margin-bottom:8px}
        .seed-v2-card input[type="text"],.seed-v2-card input[type="tel"],.seed-v2-card textarea{width:100%;box-sizing:border-box;background:#f5f5f1;border:1px solid #795548;border-radius:10px;color:var(--landing-text);font:inherit;padding:16px;transition:border-color .18s ease,box-shadow .18s ease}
        .seed-v2-card input:focus,.seed-v2-card textarea:focus{border-color:var(--seed-v2-primary);box-shadow:0 0 0 3px color-mix(in srgb,var(--seed-v2-primary) 12%,transparent);outline:0}
        .seed-v2-trust{display:flex;gap:16px;background:color-mix(in srgb,var(--seed-v2-primary) 10%,transparent);border-left:4px solid var(--seed-v2-primary);border-radius:16px;padding:24px}
        .seed-v2-trust>.material-symbols-outlined{color:var(--seed-v2-primary);font-variation-settings:"FILL" 1}
        .seed-v2-trust strong{display:block;color:var(--seed-v2-primary);font-weight:900;margin-bottom:6px}
        .seed-v2-trust p{margin:0;color:var(--landing-muted-text);line-height:1.55}
        .seed-v2-packages{display:grid;gap:24px}
        .seed-v2-package{align-items:center;background:var(--seed-v2-card);border:1px solid transparent;border-radius:12px;cursor:pointer;display:flex;gap:16px;margin:0;padding:16px;transition:border-color .18s ease,background .18s ease}
        .seed-v2-package:hover,.seed-v2-package.is-selected{background:var(--seed-v2-soft);border-color:color-mix(in srgb,var(--seed-v2-primary) 22%,transparent)}
        .seed-v2-package>input{position:absolute;opacity:0;pointer-events:none}
        .seed-v2-package>img{width:80px;height:80px;border-radius:10px;flex:0 0 80px;object-fit:cover;background:var(--seed-v2-soft)}
        .seed-v2-package-info{display:grid;gap:5px;flex:1;min-width:0}
        .seed-v2-package-info strong{color:var(--landing-text);font-size:14px;line-height:1.35}
        .seed-v2-package-info small{color:var(--landing-muted-text);font-size:12px;line-height:1.35}
        .seed-v2-package-price{display:grid;justify-items:start;gap:2px}
        .seed-v2-package-info b{color:var(--seed-v2-primary);font-size:18px}
        .seed-v2-radio{border:2px solid var(--seed-v2-border);border-radius:999px;display:grid;flex:0 0 24px;height:24px;place-items:center;width:24px}
        .seed-v2-radio i{background:var(--seed-v2-primary);border-radius:999px;display:none;height:12px;width:12px}
        .seed-v2-package.is-selected .seed-v2-radio{border-color:var(--seed-v2-primary)}
        .seed-v2-package.is-selected .seed-v2-radio i{display:block}
        .seed-v2-empty-product{align-items:center;background:var(--seed-v2-soft);border:1px solid var(--seed-v2-border);border-radius:12px;display:flex;gap:14px;margin-bottom:18px;padding:14px}
        .seed-v2-empty-product>.material-symbols-outlined{color:var(--seed-v2-primary)}
        .seed-v2-empty-product strong{display:block}
        .seed-v2-empty-product small{color:var(--landing-muted-text)}
        .seed-v2-summary{display:grid;gap:16px;margin-bottom:24px}
        .seed-v2-summary div{display:flex;justify-content:space-between;gap:16px;color:var(--landing-muted-text)}
        .seed-v2-summary strong{color:var(--landing-text)}
        .seed-v2-summary .seed-v2-total{border-top:1px solid var(--seed-v2-border);color:var(--seed-v2-primary);font-size:24px;font-weight:800;padding-top:16px}
        .seed-v2-summary .seed-v2-total strong{color:var(--seed-v2-primary)}
        .seed-v2-payment{background:color-mix(in srgb,var(--seed-v2-primary) 10%,#fff);border-radius:12px;margin-bottom:32px;padding:16px}
        .seed-v2-payment strong{align-items:center;color:var(--seed-v2-primary);display:flex;gap:8px;margin-bottom:8px}
        .seed-v2-payment strong>.material-symbols-outlined{font-variation-settings:"FILL" 1}
        .seed-v2-payment p{color:var(--landing-muted-text);font-size:12px;line-height:1.4;margin:0}
        .seed-v2-card button[type="submit"]{align-items:center;background:var(--seed-v2-primary);border:0;border-radius:12px;box-shadow:0 12px 24px rgba(13,99,27,.2);color:#fff;cursor:pointer;display:flex;font-size:24px;font-weight:800;gap:12px;justify-content:center;padding:16px;width:100%}
        .seed-v2-card button[type="submit"]:disabled{opacity:.68;cursor:wait}
        .seed-v2-secure{color:var(--landing-muted-text);font-size:12px;margin:16px 0 0;text-align:center}
        .seed-v2-whatsapp{align-items:center;color:var(--seed-v2-primary);display:flex;font-weight:800;gap:8px;justify-content:center;text-decoration:none}
        .seed-v2-whatsapp:hover{text-decoration:underline}
        .seed-mobile-block{background:var(--seed-mobile-bg,transparent);padding:var(--landing-component-padding,16px 0);border-radius:var(--landing-component-border-radius,0);box-shadow:var(--landing-component-box-shadow,none)}
        .seed-mobile-inner{width:min(var(--landing-content-max-width,800px),calc(100% - 32px));margin:0 auto}
        .seed-mobile-card{background:var(--seed-mobile-card,#fff);border:1px solid color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 14%,#d1d5db);border-radius:16px;box-shadow:0 10px 30px -10px rgba(121,85,72,.15);padding:24px}
        .seed-mobile-card h2{align-items:center;color:#3e2723;display:flex;font-size:18px;font-weight:800;gap:10px;justify-content:center;line-height:1.35;margin:0 0 18px;text-align:center}
        .seed-mobile-card h2>.material-symbols-outlined{background:color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 10%,#fff);border-radius:10px;color:var(--seed-mobile-primary,#0d631b);padding:8px}
        .seed-mobile-trust-banner{background:var(--seed-mobile-trust-bg,#d1e7dd);border:1px solid color-mix(in srgb,var(--seed-mobile-trust-bg,#d1e7dd) 80%,#0d631b);border-radius:12px;color:#0a3622;font-weight:800;margin-bottom:24px;padding:12px 16px;text-align:center}
        .seed-mobile-hero-copy{text-align:center}
        .seed-mobile-hero-copy h2{color:var(--seed-mobile-primary,#0d631b);font-size:24px;line-height:1.3;margin:0 0 8px}
        .seed-mobile-hero-copy p{color:var(--landing-muted-text);font-size:14px;line-height:1.45;margin:0 auto;max-width:560px}
        .seed-mobile-offer-card{background:linear-gradient(135deg,var(--seed-mobile-card,#fff),color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 7%,#fff));border:2px solid color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 22%,#fff);text-align:center}
        .seed-mobile-offer-card h2{color:#ba1a1a;display:block;font-size:20px;margin-bottom:4px}
        .seed-mobile-offer-card p{color:var(--landing-muted-text);font-size:14px;margin:0 0 16px}
        .seed-mobile-countdown{display:flex;gap:12px;justify-content:center;margin-bottom:20px}
        .seed-mobile-countdown span{background:var(--seed-mobile-primary,#0d631b);border-radius:10px;color:#fff;display:grid;min-width:56px;padding:8px 6px}
        .seed-mobile-countdown b{font-size:20px;line-height:1}
        .seed-mobile-countdown small{font-size:10px;text-transform:uppercase}
        .seed-mobile-price-row{align-items:center;display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
        .seed-mobile-price-row s{color:var(--landing-muted-text);font-size:18px}
        .seed-mobile-price-row strong{background:var(--seed-mobile-accent,#ffb300);border-radius:999px;color:#3e2723;font-size:20px;padding:6px 12px}
        .seed-mobile-checklist ul{display:grid;gap:12px;list-style:none;margin:0;padding:0}
        .seed-mobile-checklist li{align-items:flex-start;display:flex;gap:12px}
        .seed-mobile-checklist li>.material-symbols-outlined{color:var(--seed-mobile-primary,#0d631b);font-size:22px}
        .seed-mobile-checklist p{color:var(--landing-text);font-size:14px;line-height:1.45;margin:0}
        .seed-mobile-gallery-grid{display:grid;gap:12px;grid-template-columns:repeat(2,minmax(0,1fr))}
        .seed-mobile-gallery-grid img{aspect-ratio:1;border:1px solid color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 14%,#d1d5db);border-radius:12px;display:block;object-fit:cover;width:100%}
        .seed-mobile-form-card{display:grid;gap:16px}
        .seed-mobile-form-card h2{border-bottom:1px solid var(--seed-mobile-border,#bfcaba);justify-content:flex-start;margin-bottom:4px;padding-bottom:16px;text-align:left}
        .seed-mobile-form-card label{display:grid;gap:8px}
        .seed-mobile-form-card label>span{color:var(--landing-text);font-size:14px;font-weight:700}
        .seed-mobile-form-card input,.seed-mobile-form-card textarea{background:var(--seed-mobile-soft,#f4f4f0);border:1px solid var(--seed-mobile-border,#bfcaba);border-radius:10px;box-sizing:border-box;color:var(--landing-text);font:inherit;padding:12px 14px;width:100%}
        .seed-mobile-checkout .seed-mobile-inner{padding-bottom:0}
        .seed-mobile-package-list{display:grid;gap:14px}
        .seed-mobile-package{align-items:center;border:2px solid color-mix(in srgb,var(--seed-mobile-border,#bfcaba) 65%,transparent);border-radius:14px;cursor:pointer;display:flex;gap:12px;padding:14px;position:relative}
        .seed-mobile-package.is-selected{background:color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 10%,#fff);border-color:var(--seed-mobile-primary,#0d631b)}
        .seed-mobile-package>input{position:absolute;opacity:0;pointer-events:none}
        .seed-mobile-package>img{background:var(--seed-mobile-soft,#f4f4f0);border-radius:10px;flex:0 0 64px;height:64px;object-fit:cover;width:64px}
        .seed-mobile-package>span{display:grid;gap:4px;flex:1}
        .seed-mobile-package strong{color:#3e2723;font-size:14px;line-height:1.3}
        .seed-mobile-package small{color:var(--landing-muted-text);font-size:12px}
        .seed-mobile-package-price{display:grid;justify-items:start;gap:2px}
        .seed-mobile-package b{color:var(--seed-mobile-primary,#0d631b);font-size:18px}
        .seed-mobile-package em{background:#ffb300;border-radius:6px;color:#3e2723;font-size:10px;font-style:normal;font-weight:800;padding:3px 7px;text-transform:uppercase}
        .seed-mobile-package>i{align-items:center;background:var(--seed-mobile-primary,#0d631b);border-radius:999px;color:#fff;display:none;font-size:16px;height:24px;justify-content:center;width:24px}
        .seed-mobile-package.is-selected>i{display:flex}
        .seed-mobile-empty-product{align-items:center;background:var(--seed-mobile-soft,#f4f4f0);border:1px solid var(--seed-mobile-border,#bfcaba);border-radius:12px;display:flex;gap:12px;padding:14px}
        .seed-mobile-empty-product>.material-symbols-outlined{color:var(--seed-mobile-primary,#0d631b)}
        .seed-mobile-empty-product strong{display:block}
        .seed-mobile-empty-product small{color:var(--landing-muted-text)}
        .seed-mobile-summary{display:grid;gap:12px}
        .seed-mobile-summary h2{border-bottom:1px solid var(--seed-mobile-border,#bfcaba);justify-content:flex-start;margin-bottom:4px;padding-bottom:16px;text-align:left}
        .seed-mobile-summary div:not(.landing-order-message){display:flex;justify-content:space-between;gap:12px}
        .seed-mobile-summary div span{color:var(--landing-muted-text)}
        .seed-mobile-summary div strong{color:var(--landing-text)}
        .seed-mobile-summary .total{border-top:1px dashed var(--seed-mobile-border,#bfcaba);font-size:20px;margin-top:4px;padding-top:14px}
        .seed-mobile-summary .total strong{color:var(--seed-mobile-primary,#0d631b);font-size:24px}
        .seed-mobile-summary p{background:var(--seed-mobile-soft,#f4f4f0);border-top:1px solid var(--seed-mobile-border,#bfcaba);color:var(--landing-muted-text);font-size:12px;font-style:italic;margin:8px -24px -24px;padding:12px;text-align:center}
        .seed-mobile-sticky-cta{backdrop-filter:blur(8px);background:rgba(255,255,255,.95);border:1px solid color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 12%,transparent);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:grid;gap:8px;margin-top:16px;padding:16px;position:static}
        .seed-mobile-sticky-cta button{align-items:center;background:var(--seed-mobile-primary,#0d631b);border:0;border-radius:14px;color:#fff;cursor:pointer;display:flex;font-size:18px;font-weight:900;gap:10px;justify-content:center;padding:16px;width:100%}
        .seed-mobile-sticky-cta button:disabled{opacity:.65;cursor:wait}
        .seed-mobile-sticky-cta a{color:var(--landing-muted-text);font-size:12px;text-align:center;text-decoration:none}
        .seed-mobile-sticky-cta a strong{color:var(--seed-mobile-primary,#0d631b)}
        .seed-mobile-footer{background:var(--seed-mobile-bg,#f4f4f0);border-top:1px solid color-mix(in srgb,var(--seed-mobile-primary,#0d631b) 12%,#d1d5db);display:grid;gap:14px;justify-items:center;padding:var(--landing-component-padding,48px 16px);text-align:center}
        .seed-mobile-footer strong{color:var(--seed-mobile-primary,#0d631b);font-size:20px}
        .seed-mobile-footer p{color:var(--landing-muted-text);font-size:12px;margin:0}
        .seed-mobile-footer nav{display:flex;flex-wrap:wrap;gap:20px;justify-content:center}
        .seed-mobile-footer a{color:var(--landing-muted-text);font-size:12px;text-decoration:none}
        .seed-mobile-footer a:hover{color:var(--seed-mobile-primary,#0d631b)}
        @media (max-width: 767px){
            .landing-product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .landing-hero{padding:48px 0}
            .landing-order-form input,.landing-order-form select,.landing-order-form textarea,.landing-order-form button{font-size:13px}
            .seed-offer-hero,.seed-benefits,.seed-gallery,.seed-checkout{padding-bottom:48px}
            .seed-mobile-checkout{padding-bottom:calc(128px + env(safe-area-inset-bottom,0px))}
            .seed-mobile-checkout form{display:grid;gap:16px}
            .seed-mobile-checkout .seed-mobile-inner{padding-bottom:112px}
            .seed-mobile-form-card{order:20}
            .seed-mobile-product-card{order:10}
            .seed-mobile-summary{order:30}
            .seed-mobile-sticky-cta{order:40}
            .seed-mobile-sticky-cta{border-width:1px 0 0;border-radius:0;bottom:0;box-shadow:0 -4px 12px rgba(0,0,0,.05);left:0;margin-top:0;padding:16px 16px calc(16px + env(safe-area-inset-bottom,0px));position:fixed;right:0;z-index:100}
            .seed-hero-card{grid-template-columns:1fr;padding:28px;gap:28px;border-radius:20px}
            .seed-hero-card h1{font-size:30px}
            .seed-hero-card p{font-size:16px}
            .seed-price-row{font-size:36px}
            .seed-benefits-grid,.seed-order-grid{grid-template-columns:1fr}
            .seed-order-grid{display:flex;flex-direction:column;gap:18px}
            .seed-order-customer,.seed-order-product{display:contents}
            .seed-order-customer>*{order:20}
            .seed-order-product-heading,.seed-order-product-choice{order:10}
            .seed-order-summary,.seed-order-payment,.seed-order-submit,.seed-order-message{order:30}
            .seed-order-product-heading>span,.seed-order-customer-heading>span{font-size:0}
            .seed-order-product-heading>span:after,.seed-order-customer-heading>span:after{font-size:16px}
            .seed-order-product-heading>span:after{content:"১"}
            .seed-order-customer-heading>span:after{content:"২"}
            .seed-feature-card,.seed-small-card,.seed-trust-stack{grid-column:span 1;grid-row:auto}
            .seed-gallery-grid,.seed-mobile-gallery-grid{display:flex;grid-template-columns:none;gap:12px;margin-inline:-16px;overflow-x:auto;overflow-y:hidden;overscroll-behavior-inline:contain;padding:2px 16px 12px;scroll-padding-inline:16px;scroll-snap-type:x mandatory;scrollbar-width:none;-webkit-overflow-scrolling:touch}
            .seed-gallery-grid::-webkit-scrollbar,.seed-mobile-gallery-grid::-webkit-scrollbar{height:0}
            .seed-gallery-grid figure{flex:0 0 min(72vw,280px);scroll-snap-align:start}
            .seed-mobile-gallery-grid img{flex:0 0 min(68vw,240px);scroll-snap-align:start}
            .seed-checkout>.landing-section-inner{padding:24px;border-radius:22px}
            .seed-section-heading h2,.seed-gallery h2,.seed-checkout h2{font-size:24px}
            .seed-footer .landing-section-inner{flex-direction:column;text-align:center}
            .seed-footer nav{justify-content:center}
            .seed-v2-heading h2{font-size:30px}
            .seed-checkout-v2{padding-inline:0}
            .seed-v2-inner{width:100%;padding-inline:12px}
            .seed-v2-heading{padding-inline:4px}
            .seed-v2-grid{align-items:stretch;display:flex;flex-direction:column;gap:16px;width:100%;margin:0}
            .seed-v2-left,.seed-v2-right{display:contents}
            .seed-v2-product-card{order:10}
            .seed-v2-customer-card{order:20}
            .seed-v2-trust{order:30}
            .seed-v2-summary-card{order:40}
            .seed-v2-whatsapp{order:50}
            .seed-v2-right{position:static}
            .seed-v2-product-card,.seed-v2-customer-card,.seed-v2-summary-card,.seed-v2-trust,.seed-v2-whatsapp{box-sizing:border-box;width:100%}
            .seed-v2-card,.seed-v2-right .seed-v2-card{border-radius:14px;padding:20px}
            .seed-v2-package{align-items:flex-start}
            .seed-v2-package>img{width:64px;height:64px;flex-basis:64px}
            .seed-v2-card button[type="submit"]{font-size:18px}
            .seed-support p{font-size:20px}
            .seed-support a{font-size:18px;padding:12px 22px}
            .seed-support-badges{gap:16px 22px}
        }
        @media (max-width: 420px){
            .landing-product-grid{grid-template-columns:1fr}
            .seed-support a{width:100%;justify-content:center}
            .seed-support-badges{align-items:center;flex-direction:column}
        }
    </style>
</head>
<body
    class="landing-page"
>
    <main class="landing-page-frame">
        @foreach($page->components as $component)
            {!! $componentRenderer->renderHtml($component, $themeTokens) !!}
        @endforeach
    </main>
    <script type="module" src="{{ asset('dynamic_landing_pages/js/runtime.js') }}"></script>
</body>
</html>

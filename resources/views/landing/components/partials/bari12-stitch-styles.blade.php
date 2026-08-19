@once
    <style>
        .bari12-component{box-sizing:border-box;margin:var(--landing-component-margin,0);max-width:var(--landing-component-max-width,none);text-align:var(--landing-component-text-align,inherit)}
        .bari12-component *,.bari12-component *:before,.bari12-component *:after{box-sizing:border-box}
        .bari12-inner{width:min(var(--landing-content-max-width,768px),calc(100% - 24px));margin:0 auto}
        .bari12-card{background:var(--bari12-card,#fff);border:1px solid var(--bari12-border,#e5e7eb);border-radius:var(--landing-component-border-radius,12px);box-shadow:var(--landing-component-box-shadow,0 8px 24px rgba(0,0,0,.08))}
        .bari12-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border:0;text-decoration:none;border-radius:999px;background:linear-gradient(to bottom,var(--bari12-button-top,#28b463),var(--bari12-button,#1d8348));color:#fff;font-weight:800;line-height:1.25;box-shadow:0 10px 22px rgba(29,131,72,.24);padding:13px 24px;min-height:48px}
        .bari12-btn:hover{filter:saturate(1.08) brightness(.96)}
        .bari12-icon{font-family:"Material Symbols Outlined";font-size:22px;line-height:1;font-weight:normal;font-style:normal;font-feature-settings:"liga";font-variation-settings:"FILL" 1}
        .bari12-check-list{display:grid;gap:14px;list-style:none;margin:0;padding:0}
        .bari12-check-list li{display:flex;align-items:flex-start;gap:10px;color:var(--bari12-text,#333);font-weight:600;line-height:1.5}
        .bari12-check-list .bari12-icon{color:var(--bari12-check,#c0392b);font-size:20px;flex:0 0 20px;margin-top:2px}
        .bari12-section-title{margin:0;color:var(--bari12-heading,#145a32);font-size:clamp(20px,5vw,28px);font-weight:900;line-height:1.25;text-align:center}
        .bari12-stitch-top-banner{background:var(--bari12-bg,#f2d7d5);border-bottom:2px solid var(--bari12-border-strong,#ef4444);padding:var(--landing-component-padding,12px 16px);color:var(--bari12-heading,#b91c1c);text-align:var(--landing-component-text-align,center)}
        .bari12-stitch-top-banner h1{margin:0;font-size:clamp(20px,5vw,28px);line-height:1.25;font-weight:900}
        .bari12-stitch-top-banner p{margin:10px auto 0;max-width:860px;color:var(--bari12-muted,#dc2626);font-size:clamp(14px,3.4vw,17px);font-weight:800;line-height:1.5}
        .bari12-cta{padding:var(--landing-component-padding,24px 0);background:var(--bari12-bg,#fff);text-align:center}
        .bari12-hero-image{padding:var(--landing-component-padding,8px 0 16px);background:var(--bari12-bg,#fff)}
        .bari12-hero-image .bari12-card{background:var(--bari12-card,#f9ebea);padding:8px;text-align:center}
        .bari12-hero-image h2{display:inline-block;margin:0;background:#fff;color:var(--bari12-heading,#b91c1c);border-radius:8px;padding:10px 12px;font-size:clamp(18px,4.8vw,25px);line-height:1.35;font-weight:900}
        .bari12-hero-image img,.bari12-gallery-grid img,.bari12-product-thumb{display:block;width:100%;height:auto;object-fit:cover}
        .bari12-hero-image figure{margin:16px 0 0}
        .bari12-hero-image img{border-radius:10px}
        .bari12-trust-banner{background:var(--bari12-bg,#22c55e);color:#fff;padding:var(--landing-component-padding,12px 16px);font-size:clamp(16px,4vw,22px);font-weight:900;text-align:center}
        .bari12-benefits{background:var(--bari12-bg,#f0fdf4);padding:var(--landing-component-padding,28px 0)}
        .bari12-benefits .bari12-section-title{background:var(--bari12-title-bg,#fcf3cf);border-bottom:1px solid color-mix(in srgb,var(--bari12-heading,#145a32) 18%,#fff);border-radius:10px 10px 0 0;padding:10px;margin-bottom:22px}
        .bari12-offer{background:var(--bari12-bg,#e6b0aa);padding:var(--landing-component-padding,32px 0);text-align:center;color:var(--bari12-text,#1f2937)}
        .bari12-offer h3,.bari12-offer h4,.bari12-offer p{margin:0}
        .bari12-offer h3{font-size:clamp(19px,4.8vw,26px);font-weight:900;line-height:1.35;margin-bottom:10px}
        .bari12-offer .bari12-gift{color:var(--bari12-accent,#15803d);text-decoration:underline;text-underline-offset:5px}
        .bari12-offer .bari12-limit{font-size:18px;font-weight:900;margin:0 0 22px}
        .bari12-offer .bari12-red{color:#dc2626;font-size:clamp(22px,5.5vw,30px);font-weight:900;margin-bottom:8px}
        .bari12-offer .bari12-blue{color:#1d4ed8;font-size:clamp(21px,5.2vw,30px);font-weight:900;margin-bottom:18px}
        .bari12-price-row{display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:8px;font-size:clamp(21px,5vw,30px);font-weight:900;margin:10px 0 24px}
        .bari12-price-row s{color:#c0392b}
        .bari12-price-badge{border:2px solid #dc2626;border-radius:999px;color:#1d4ed8;padding:2px 12px;font-size:clamp(28px,6vw,38px)}
        .bari12-countdown{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
        .bari12-countdown span{background:var(--bari12-countdown,#e74c3c);border-radius:8px;color:#fff;display:grid;place-items:center;min-width:62px;min-height:62px;padding:8px;box-shadow:0 6px 14px rgba(0,0,0,.14)}
        .bari12-countdown b{font-size:24px;line-height:1}
        .bari12-countdown small{font-size:10px;text-transform:uppercase}
        .bari12-gallery{background:var(--bari12-bg,#f7fef9);padding:var(--landing-component-padding,32px 0)}
        .bari12-gallery h2{display:inline-block;background:linear-gradient(to bottom,#d5f5e3,#a9dfbf);border:1px solid #86efac;border-radius:999px;color:#14532d;padding:9px 24px;box-shadow:0 6px 14px rgba(0,0,0,.08)}
        .bari12-gallery-head{text-align:center;margin-bottom:22px}
        .bari12-gallery-grid{display:grid;grid-template-columns:repeat(var(--bari12-columns,2),minmax(0,1fr));gap:14px}
        .bari12-gallery-grid figure{margin:0;overflow:hidden;border:2px solid var(--bari12-image-border,#3b82f6);border-radius:10px;box-shadow:0 8px 18px rgba(0,0,0,.12);background:#fff}
        .bari12-gallery-grid img{aspect-ratio:1/1;transition:transform .35s ease}
        .bari12-gallery-grid figure:hover img{transform:scale(1.04)}
        .bari12-why-us{background:var(--bari12-bg,#e8f8f5);padding:var(--landing-component-padding,32px 0);text-align:center}
        .bari12-why-us h2{color:var(--bari12-heading,#7f1d1d);margin-bottom:10px}
        .bari12-rule{display:flex;justify-content:center;gap:4px;margin-bottom:24px}
        .bari12-rule span{width:32px;height:4px;background:var(--bari12-rule,#16a34a)}
        .bari12-why-us .bari12-check-list{max-width:620px;margin:0 auto;text-align:left}
        .bari12-why-us .bari12-cta-wrap{margin-top:26px}
        .bari12-whatsapp{background:var(--bari12-bg,#f9ebea);padding:var(--landing-component-padding,32px 0);text-align:center}
        .bari12-whatsapp h2{color:var(--bari12-heading,#7f1d1d);font-size:clamp(20px,5vw,28px);font-weight:900;line-height:1.3;margin:0 0 18px}
        .bari12-whatsapp .bari12-btn,.bari12-floating-whatsapp{background:var(--bari12-button,#6c3453)}
        .bari12-footer{background:var(--bari12-bg,#000);color:#fff;padding:var(--landing-component-padding,10px 12px);font-size:12px;text-align:center}
        .bari12-floating-whatsapp{position:fixed;right:16px;bottom:16px;z-index:80;color:#fff;text-decoration:none;border-radius:999px;display:inline-flex;align-items:center;gap:8px;padding:12px 14px;box-shadow:0 12px 26px rgba(0,0,0,.22);font-weight:900}
        .bari12-floating-whatsapp .bari12-icon{font-size:24px}
        .bari12-checkout{background:var(--bari12-bg,#e8f8f5);padding:var(--landing-component-padding,32px 0);box-shadow:var(--landing-component-box-shadow,inset 0 8px 28px rgba(0,0,0,.06))}
        .bari12-checkout h2{color:#111827;margin:0 0 28px}
        .bari12-checkout-form{display:grid;gap:20px}
        .bari12-fieldset{display:grid;gap:14px}
        .bari12-checkout-form>div>h3,.bari12-fieldset h3,.bari12-order-summary h3{border-bottom:1px solid var(--bari12-border,#d1d5db);color:#374151;font-size:16px;font-weight:900;margin:0 0 8px;padding-bottom:8px}
        .bari12-field label>span{display:block;color:#374151;font-size:14px;font-weight:700;margin-bottom:6px}
        .bari12-field input,.bari12-field textarea{width:100%;border:1px solid var(--bari12-border,#d1d5db);border-radius:8px;background:#fff;color:#111827;font:inherit;padding:11px 12px}
        .bari12-field textarea{min-height:84px;resize:vertical}
        .bari12-form-note{font-size:12px;color:#dc2626;margin-top:5px}
        .bari12-shipping-row,.bari12-summary-row{display:flex;justify-content:space-between;gap:12px;align-items:center;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px;font-size:14px}
        .bari12-packages{display:grid;gap:10px}
        .bari12-package{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:12px;align-items:center;background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:12px;cursor:pointer;transition:border-color .18s ease,opacity .18s ease}
        .bari12-package.is-selected{border-color:var(--bari12-primary,#1d8348);box-shadow:0 0 0 1px var(--bari12-primary,#1d8348)}
        .bari12-package input{accent-color:var(--bari12-primary,#1d8348);width:18px;height:18px}
        .bari12-package-main{display:grid;grid-template-columns:44px minmax(0,1fr);gap:10px;align-items:center;min-width:0}
        .bari12-product-thumb{width:44px;height:44px;border:1px solid #e5e7eb;border-radius:8px;background:#fff}
        .bari12-package-title{display:block;color:#374151;font-size:14px;font-weight:700;line-height:1.35}
        .bari12-package-subtitle{display:block;color:#6b7280;font-size:12px;line-height:1.35}
        .bari12-package-price{display:grid;justify-items:end;gap:2px;color:var(--bari12-primary,#15803d);font-weight:900;white-space:nowrap}
        .bari12-package-price s{color:#9ca3af;font-size:12px;font-weight:700;line-height:1}
        .bari12-order-summary{border-top:1px solid var(--bari12-border,#d1d5db);padding-top:16px}
        .bari12-summary-table{display:grid;gap:9px;margin-top:12px}
        .bari12-summary-row{background:transparent;border-width:0 0 1px;border-radius:0;padding:0 0 9px}
        .bari12-summary-row.total{border-top:1px solid var(--bari12-border,#d1d5db);border-bottom:0;color:var(--bari12-primary,#15803d);font-weight:900;padding-top:10px}
        .bari12-payment{background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;color:#4b5563;font-size:13px;line-height:1.55;margin-top:16px;padding:14px}
        .bari12-payment strong{display:block;color:#374151;margin-bottom:4px}
        .bari12-submit{border:0;border-radius:8px;background:var(--bari12-button,#6c3453);color:#fff;cursor:pointer;font:inherit;font-size:18px;font-weight:900;padding:15px;width:100%}
        .bari12-submit:disabled{opacity:.68;cursor:not-allowed}
        @media (max-width:520px){
            .bari12-inner{width:min(var(--landing-content-max-width,768px),calc(100% - 16px))}
            .bari12-btn{width:min(100%,360px);padding-inline:16px;font-size:15px}
            .bari12-products-block{order:10}
            .bari12-billing-block{order:20}
            .bari12-shipping-block{order:30}
            .bari12-order-summary{order:40}
            .bari12-submit{order:50}
            .bari12-checkout-form>.landing-order-message{order:60}
            .bari12-gallery-grid{display:flex;grid-template-columns:none;gap:10px;margin-inline:-8px;overflow-x:auto;overflow-y:hidden;overscroll-behavior-inline:contain;padding:2px 8px 12px;scroll-padding-inline:8px;scroll-snap-type:x mandatory;scrollbar-width:none;-webkit-overflow-scrolling:touch}
            .bari12-gallery-grid::-webkit-scrollbar{height:0}
            .bari12-gallery-grid figure{flex:0 0 min(72vw,260px);scroll-snap-align:start}
            .bari12-package{grid-template-columns:auto minmax(0,1fr);align-items:flex-start}
            .bari12-package-price{grid-column:2;justify-self:start}
            .bari12-floating-whatsapp span:not(.bari12-icon){display:none}
        }
        @media (max-width:360px){
            .bari12-gallery-grid figure{flex-basis:min(78vw,250px)}
        }
    </style>
@endonce

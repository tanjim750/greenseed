@once
    <style>
        .sheikh-component{box-sizing:border-box;width:100%;margin:var(--landing-component-margin,0);max-width:var(--landing-component-max-width,none);text-align:var(--landing-component-text-align,inherit);font-family:"Noto Sans Bengali","Hind Siliguri",system-ui,sans-serif;overflow-x:clip}
        .sheikh-component *,.sheikh-component *:before,.sheikh-component *:after{box-sizing:border-box}
        .sheikh-component img{max-width:100%}
        .sheikh-inner{width:min(var(--landing-content-max-width,760px),calc(100% - 24px));min-width:0;margin:0 auto}
        .sheikh-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;width:min(100%,420px);min-width:0;border:0;border-radius:999px;background:var(--sheikh-button,#22734e);color:var(--sheikh-button-text,#fff);font-weight:900;font-size:18px;line-height:1.25;text-align:center;text-decoration:none;box-shadow:0 12px 26px rgba(34,115,78,.26);padding:16px 24px;white-space:normal;overflow-wrap:anywhere;transition:transform .16s ease,filter .16s ease}
        .sheikh-btn:hover{filter:saturate(1.08) brightness(.96);transform:translateY(-1px)}
        .sheikh-btn svg{width:22px;height:22px;flex:0 0 22px}
        .sheikh-hero{background:var(--sheikh-bg,#14532d);color:#fff;padding:var(--landing-component-padding,16px 0 0)}
        .sheikh-hero-card{position:relative;overflow:hidden;border-bottom:4px solid var(--sheikh-border,#22c55e);border-radius:0 0 var(--landing-component-border-radius,24px) var(--landing-component-border-radius,24px);background:radial-gradient(circle at 50% 0,rgba(255,255,255,.1),transparent 36%),var(--sheikh-bg,#14532d);box-shadow:var(--landing-component-box-shadow,0 14px 30px rgba(0,0,0,.14));padding:34px 18px;text-align:center}
        .sheikh-hero-card:before{content:"";position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.12) 1px,transparent 1px);background-size:18px 18px;opacity:.28}
        .sheikh-hero-content{position:relative;z-index:1}
        .sheikh-hero-kicker{margin:0 0 6px;font-size:22px;font-weight:800;overflow-wrap:anywhere}
        .sheikh-hero h1{margin:0 0 22px;color:var(--sheikh-accent,#facc15);font-size:40px;font-weight:1000;line-height:1.12;text-shadow:0 2px 8px rgba(0,0,0,.22);overflow-wrap:anywhere}
        .sheikh-price-row{display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;margin-bottom:20px}
        .sheikh-old-price{color:#d1d5db;font-size:18px;text-decoration:line-through}
        .sheikh-new-price{background:#fff;color:var(--sheikh-bg,#14532d);border-radius:999px;padding:8px 22px;font-size:28px;font-weight:1000;box-shadow:0 10px 24px rgba(0,0,0,.18)}
        .sheikh-delivery-badge{display:inline-flex;align-items:center;justify-content:center;gap:8px;max-width:100%;background:rgba(22,101,52,.72);border:1px solid rgba(34,197,94,.55);border-radius:10px;color:#fef9c3;font-weight:800;padding:12px 18px;overflow-wrap:anywhere}
        .sheikh-hero-flags{display:flex;justify-content:center;gap:20px;flex-wrap:wrap;margin-top:20px;font-size:13px}
        .sheikh-hero-flag{display:inline-flex;align-items:center;gap:8px}
        .sheikh-dot{width:14px;height:14px;border-radius:999px;background:var(--sheikh-dot,#22c55e)}
        .sheikh-cta{background:var(--sheikh-bg,#fff);padding:var(--landing-component-padding,30px 0);text-align:center}
        .sheikh-collage{background:var(--sheikh-bg,#dcfce7);padding:var(--landing-component-padding,18px 0)}
        .sheikh-collage-grid{position:relative;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;min-width:0}
        .sheikh-collage-grid figure{margin:0;overflow:hidden;background:#e5e7eb;aspect-ratio:1/1}
        .sheikh-collage-grid figure:first-child{border-top-left-radius:16px}
        .sheikh-collage-grid figure:nth-child(2){border-top-right-radius:16px}
        .sheikh-collage-grid figure:nth-child(3){border-bottom-left-radius:16px}
        .sheikh-collage-grid figure:nth-child(4){border-bottom-right-radius:16px}
        .sheikh-collage img,.sheikh-image-banner img{display:block;width:100%;height:100%;object-fit:cover}
        .sheikh-collage-center{position:absolute;left:50%;top:50%;width:min(38%,220px);aspect-ratio:1/1;transform:translate(-50%,-50%);border:8px solid #fff;border-radius:999px;overflow:hidden;background:#fff;box-shadow:0 20px 44px rgba(0,0,0,.24);z-index:2}
        .sheikh-list{background:var(--sheikh-bg,#fff);padding:var(--landing-component-padding,34px 0)}
        .sheikh-panel{overflow:hidden;background:var(--sheikh-card,#fff);border:1px solid var(--sheikh-border,#e5e7eb);border-radius:var(--landing-component-border-radius,16px);box-shadow:var(--landing-component-box-shadow,0 16px 36px rgba(15,23,42,.09))}
        .sheikh-panel-head{background:var(--sheikh-heading-bg,#5b21b6);color:var(--sheikh-heading-text,#fff);padding:15px 18px;text-align:center}
        .sheikh-panel-head h2,.sheikh-section-title{margin:0;font-size:24px;font-weight:1000;line-height:1.25;overflow-wrap:anywhere}
        .sheikh-items{list-style:none;margin:0;padding:20px;display:grid;gap:0}
        .sheikh-items li{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:start;gap:14px;padding:14px 0;border-bottom:1px solid #f1f5f9;color:#374151;line-height:1.55;min-width:0;overflow-wrap:anywhere}
        .sheikh-items li:last-child{border-bottom:0}
        .sheikh-item-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:999px;background:var(--sheikh-icon-bg,#f3e8ff);font-size:18px}
        .sheikh-check{color:var(--sheikh-check,#16a34a);font-weight:1000}
        .sheikh-image-banner{background:var(--sheikh-bg,#fff);padding:var(--landing-component-padding,20px 0)}
        .sheikh-image-banner img{max-height:380px}
        .sheikh-trust .sheikh-panel-head{background:var(--sheikh-heading-bg,#1e3a8a)}
        .sheikh-trust .sheikh-items li{grid-template-columns:auto minmax(0,1fr) auto;align-items:center}
        .sheikh-trust .sheikh-item-icon{width:48px;height:48px;border-radius:10px;background:#eff6ff}
        .sheikh-countdown-cta{background:var(--sheikh-bg,#fff);padding:var(--landing-component-padding,32px 0);text-align:center}
        .sheikh-countdown-cta .sheikh-inner{display:flex;flex-direction:column;align-items:center;gap:26px}
        .sheikh-countdown-box{display:inline-block;max-width:100%;border:2px solid var(--sheikh-countdown-border,#ca8a04);border-radius:16px;background:var(--sheikh-countdown-bg,#fff7ed);padding:16px}
        .sheikh-countdown-label{margin:0 0 10px;color:#374151;font-weight:800}
        .sheikh-countdown{display:flex;justify-content:center;gap:8px;color:#fff;font-weight:1000;font-size:24px}
        .sheikh-countdown-unit{display:grid;justify-items:center;gap:5px}
        .sheikh-countdown-unit b{display:grid;place-items:center;min-width:48px;border-radius:7px;background:var(--sheikh-countdown-color,#166534);padding:7px 8px;line-height:1}
        .sheikh-countdown-unit small{color:#64748b;font-size:11px;font-weight:700}
        .sheikh-testimonials{background:var(--sheikh-bg,#f8fafc);padding:var(--landing-component-padding,46px 0)}
        .sheikh-testimonials h2{margin-bottom:26px;color:var(--sheikh-heading,#1e3a8a);text-align:center}
        .sheikh-testimonial-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;min-width:0}
        .sheikh-testimonial{position:relative;min-width:0;background:#fff;border:1px solid #eef2f7;border-radius:16px;box-shadow:0 8px 22px rgba(15,23,42,.06);padding:20px;overflow-wrap:anywhere}
        .sheikh-testimonial-mark{position:absolute;top:6px;right:16px;color:#e5e7eb;font-family:serif;font-size:42px}
        .sheikh-person{display:flex;align-items:center;gap:12px;margin-bottom:12px}
        .sheikh-avatar{display:grid;place-items:center;width:46px;height:46px;border-radius:999px;background:#dcfce7}
        .sheikh-stars{color:#facc15;font-size:13px;letter-spacing:1px}
        .sheikh-testimonial p{margin:0 0 14px;color:#4b5563;font-size:14px;line-height:1.65}
        .sheikh-verified{display:inline-flex;align-items:center;gap:5px;border-radius:999px;background:#f0fdf4;color:#16a34a;font-size:12px;font-weight:800;padding:5px 9px}
        .sheikh-footer{background:var(--sheikh-bg,#f3f4f6);border-top:1px solid #e5e7eb;padding:var(--landing-component-padding,24px 0);text-align:center}
        .sheikh-footer .sheikh-btn{background:var(--sheikh-button,#dc2626);margin-bottom:18px}
        .sheikh-footer p{margin:0;color:#6b7280;font-size:12px}
        .sheikh-floating-order{position:fixed;left:0;right:0;bottom:0;z-index:90;background:var(--sheikh-float-bg,#fffefb);box-shadow:0 -12px 34px rgba(15,23,42,.14);padding:10px 0 calc(10px + env(safe-area-inset-bottom,0px));pointer-events:none}
        .sheikh-floating-order.is-hidden{opacity:0;visibility:hidden;transform:translateY(100%);transition:opacity .18s ease,visibility .18s ease,transform .18s ease}
        .sheikh-floating-order-card{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:28px;width:100%;margin:0;background:transparent;border:0;border-top:1px solid color-mix(in srgb,var(--sheikh-float-button,#168a45) 14%,#e5e7eb);border-radius:0;padding:12px clamp(16px,4vw,80px);pointer-events:auto}
        .sheikh-floating-copy{display:grid;justify-items:center;text-align:center;min-width:0}
        .sheikh-floating-badge{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:var(--sheikh-float-badge-bg,#dcfce7);color:var(--sheikh-float-badge-text,#168a45);border:1px solid color-mix(in srgb,var(--sheikh-float-badge-text,#168a45) 28%,#fff);font-size:13px;font-weight:1000;line-height:1;padding:5px 12px;margin-bottom:4px}
        .sheikh-floating-title{display:block;color:var(--sheikh-float-text,#2f3a3f);font-size:20px;font-weight:1000;line-height:1.15;overflow-wrap:anywhere}
        .sheikh-floating-price{display:flex;align-items:baseline;justify-content:center;gap:8px;line-height:1.05;margin-top:3px}
        .sheikh-floating-price s{color:var(--sheikh-float-old-price,#b8c2c6);font-size:15px;font-weight:800}
        .sheikh-floating-price strong{color:var(--sheikh-float-price,#168a45);font-size:25px;font-weight:1000}
        .sheikh-floating-btn{display:inline-flex;align-items:center;justify-content:center;gap:12px;min-width:190px;border-radius:999px;background:var(--sheikh-float-button,#168a45);color:var(--sheikh-float-button-text,#fff);font-size:21px;font-weight:1000;line-height:1;text-decoration:none;box-shadow:0 14px 28px rgba(22,138,69,.28);padding:20px 30px;white-space:nowrap}
        .sheikh-floating-btn svg{width:22px;height:22px;flex:0 0 22px}
        .sheikh-floating-btn:hover{filter:saturate(1.08) brightness(.97);transform:translateY(-1px)}
        .sheikh-floating-order-spacer{height:122px}
        .sheikh-checkout{background:var(--sheikh-bg,#fff);padding:var(--landing-component-padding,34px 0)}
        .sheikh-checkout-card{border:4px solid var(--sheikh-border,#16a34a);border-radius:var(--landing-component-border-radius,18px);background:var(--sheikh-card,#fff);padding:clamp(16px,4vw,30px);box-shadow:var(--landing-component-box-shadow,0 16px 34px rgba(22,101,52,.1))}
        .sheikh-checkout h2{text-align:center;margin:0 0 24px;color:#111827}
        .sheikh-checkout-form{display:grid;gap:24px}
        .sheikh-products{display:grid;gap:12px;margin-bottom:10px}
        .sheikh-package{display:grid;grid-template-columns:auto 52px minmax(0,1fr) auto;gap:12px;align-items:center;min-width:0;border:1px solid #e5e7eb;border-radius:14px;background:#fff;padding:12px;cursor:pointer}
        .sheikh-package.is-selected{border-color:var(--sheikh-primary,#22734e);background:#f0fdf4;box-shadow:0 0 0 1px var(--sheikh-primary,#22734e)}
        .sheikh-package input{width:18px;height:18px;accent-color:var(--sheikh-primary,#22734e)}
        .sheikh-package img{width:52px;height:52px;object-fit:cover;border-radius:8px;background:#e5e7eb}
        .sheikh-package-title{display:block;color:#374151;font-weight:800;line-height:1.35;overflow-wrap:anywhere}
        .sheikh-package-subtitle{display:block;color:#64748b;font-size:12px;line-height:1.35;margin-top:2px}
        .sheikh-package-price{display:grid;justify-items:end;gap:2px;color:var(--sheikh-primary,#22734e);font-weight:1000;white-space:nowrap}
        .sheikh-package-price s{color:#9ca3af;font-size:12px;font-weight:800;line-height:1}
        .sheikh-checkout-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:28px;min-width:0}
        .sheikh-checkout h3{border-bottom:1px solid #e5e7eb;margin:0 0 14px;padding-bottom:10px;color:#111827;font-size:18px;font-weight:1000}
        .sheikh-field{display:grid;gap:6px;margin-bottom:14px}
        .sheikh-field label{color:#374151;font-size:14px;font-weight:800}
        .sheikh-field input,.sheikh-field textarea{width:100%;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#111827;font:inherit;padding:12px}
        .sheikh-field textarea{min-height:88px;resize:vertical}
        .sheikh-shipping-note,.sheikh-summary-box{border:1px solid #e5e7eb;border-radius:10px;background:#f9fafb;color:#4b5563;padding:14px}
        .sheikh-payment-note{margin-top:14px}
        .sheikh-summary-box{display:grid;gap:12px}
        .sheikh-summary-line{display:flex;justify-content:space-between;gap:14px;min-width:0;border-bottom:1px solid #e5e7eb;padding-bottom:10px}
        .sheikh-summary-line>*{min-width:0;overflow-wrap:anywhere}
        .sheikh-summary-line.total{border-bottom:0;border-top:1px solid #e5e7eb;color:var(--sheikh-primary,#22734e);font-size:18px;font-weight:1000;padding-top:12px;padding-bottom:0}
        .sheikh-submit{display:inline-flex;align-items:center;justify-content:center;width:100%;border:0;border-radius:8px;background:var(--sheikh-button,#f97316);color:var(--sheikh-button-text,#fff);cursor:pointer;font:inherit;font-weight:1000;padding:14px;margin-top:16px}
        .sheikh-submit:disabled{opacity:.65;cursor:not-allowed}
        @media (max-width:640px){
            .sheikh-inner{width:min(var(--landing-content-max-width,760px),calc(100% - 16px))}
            .sheikh-hero-card{padding:30px 12px}
            .sheikh-hero-kicker{font-size:18px}
            .sheikh-hero h1{font-size:32px}
            .sheikh-new-price{font-size:23px}
            .sheikh-btn{font-size:16px;padding:14px 18px}
            .sheikh-collage-grid{display:flex;grid-template-columns:none;gap:10px;margin-inline:-8px;overflow-x:auto;overflow-y:hidden;overscroll-behavior-inline:contain;padding:2px 8px 12px;scroll-padding-inline:8px;scroll-snap-type:x mandatory;scrollbar-width:none;-webkit-overflow-scrolling:touch}
            .sheikh-collage-grid::-webkit-scrollbar{height:0}
            .sheikh-collage-grid figure{flex:0 0 min(72vw,260px);border-radius:16px!important;scroll-snap-align:start}
            .sheikh-collage-center{position:relative;left:auto;top:auto;order:-1;flex:0 0 min(58vw,220px);width:auto;transform:none;border-width:6px;scroll-snap-align:center}
            .sheikh-panel-head h2,.sheikh-section-title{font-size:21px}
            .sheikh-items{padding:16px}
            .sheikh-items li{grid-template-columns:auto minmax(0,1fr);gap:10px}
            .sheikh-items .sheikh-check{grid-column:2}
            .sheikh-testimonial-grid,.sheikh-checkout-grid{grid-template-columns:1fr}
            .sheikh-package{grid-template-columns:auto 46px minmax(0,1fr)}
            .sheikh-package-price{grid-column:3;justify-self:start}
            .sheikh-floating-order{padding-inline:0}
            .sheikh-floating-order-card{grid-template-columns:minmax(0,1fr) auto;gap:12px;border-radius:0;padding:10px 12px}
            .sheikh-floating-badge{font-size:11px;padding:4px 9px}
            .sheikh-floating-title{font-size:15px}
            .sheikh-floating-price s{font-size:12px}
            .sheikh-floating-price strong{font-size:18px}
            .sheikh-floating-btn{min-width:118px;font-size:15px;padding:14px 16px}
            .sheikh-floating-btn svg{width:18px;height:18px}
            .sheikh-floating-order-spacer{height:104px}
        }
        @media (max-width:380px){
            .sheikh-inner{width:min(var(--landing-content-max-width,760px),calc(100% - 12px))}
            .sheikh-hero-card{padding:26px 10px}
            .sheikh-hero h1{font-size:28px}
            .sheikh-new-price{font-size:21px;padding-inline:16px}
            .sheikh-delivery-badge{padding:10px 12px}
            .sheikh-btn{border-radius:18px;font-size:15px;padding:13px 14px}
            .sheikh-collage-grid{gap:8px}
            .sheikh-collage-grid figure{flex-basis:min(78vw,250px)}
            .sheikh-collage-center{flex-basis:min(64vw,210px);border-width:5px}
            .sheikh-countdown{gap:5px;font-size:20px}
            .sheikh-countdown-box{padding:12px}
            .sheikh-countdown-unit b{min-width:40px;padding:6px}
            .sheikh-checkout-card{border-width:3px;padding:14px 10px}
            .sheikh-package{grid-template-columns:auto minmax(0,1fr);gap:10px}
            .sheikh-package img{grid-column:2;width:46px;height:46px}
            .sheikh-package>span{grid-column:2}
            .sheikh-package-price{grid-column:2}
            .sheikh-summary-line{display:grid;grid-template-columns:1fr;gap:4px}
            .sheikh-floating-order-card{gap:8px;padding:9px 10px}
            .sheikh-floating-badge{display:none}
            .sheikh-floating-title{font-size:13px}
            .sheikh-floating-price strong{font-size:16px}
            .sheikh-floating-btn{min-width:102px;font-size:14px;padding:12px 13px}
            .sheikh-floating-order-spacer{height:92px}
        }
    </style>
@endonce

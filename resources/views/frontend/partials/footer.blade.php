@php
    use App\Models\Information;
    use Illuminate\Support\Facades\DB;

    $info = Information::orderBy('id','desc')->first();

    $ownerPhone = $info->owner_phone ?? '';
    $ownerEmail = $info->owner_email ?? '';

    $whatsNumberRaw = $info->whats_num ?? $ownerPhone ?? '';
    $whatsNumber = preg_replace('/[^0-9]/', '', $whatsNumberRaw);

    if (substr($whatsNumber, 0, 2) === '01') {
        $whatsNumber = '88' . $whatsNumber;
    } elseif (substr($whatsNumber, 0, 3) === '880') {
    } elseif (substr($whatsNumber, 0, 2) === '88') {
    } else {
        if (strlen($whatsNumber) === 11 && substr($whatsNumber, 0, 1) === '1') {
            $whatsNumber = '88' . $whatsNumber;
        }
    }
@endphp

<style>
    :root{
        --footer-bg1: {{ $info->footer_bg1 ?? '#0f172a' }};
        --footer-bg2: {{ $info->footer_bg2 ?? '#020617' }};
        --footer-bg3: {{ $info->footer_bg3 ?? '#000000' }};
        --footer-text: {{ $info->footer_text ?? '#e5e7eb' }};
        --footer-hover: {{ $info->footer_link_hover ?? '#38bdf8' }};
        --footer-subtitle: {{ $info->footer_subtitle ?? '#9ca3af' }};
        --footer-grad1: {{ $info->footer_border_grad1 ?? '#22d3ee' }};
        --footer-grad2: {{ $info->footer_border_grad2 ?? '#2563eb' }};
        --pill-bg: {{ $info->footer_pill_bg ?? '#0f172a' }};
        --pill-border: {{ $info->footer_pill_border ?? '#94a3b8' }};
        --pill-hover-bg: {{ $info->footer_pill_hover_bg ?? '#0ea5e9' }};
        --pill-hover-text: {{ $info->footer_pill_hover_text ?? '#0b1120' }};
        --underline: {{ $info->footer_underline ?? '#38bdf8' }};
        --social-border: {{ $info->footer_social_border ?? '#94a3b8' }};
        --social-bg: {{ $info->footer_social_bg ?? '#0f172a' }};
        --social-hover-bg: {{ $info->footer_social_hover_bg ?? '#0ea5e9' }};
        --social-hover-text: {{ $info->footer_social_hover_text ?? '#020617' }};
        --mnav-bg: {{ $info->mnav_bg ?? '#ffffff' }};
        --mnav-border: {{ $info->mnav_border ?? '#e5e7eb' }};
        --mnav-icon: {{ $info->mnav_icon ?? '#64748b' }};
        --mnav-home-bg: {{ $info->mnav_home_bg ?? '#00276C' }};
        --mnav-home-border: {{ $info->mnav_home_border ?? '#ffffff' }};
        --mnav-home-icon: {{ $info->mnav_home_icon ?? '#ffffff' }};
        --ease-out: cubic-bezier(.22,.61,.36,1);
        --ease-bounce: cubic-bezier(.34,1.56,.64,1);
    }

    /* ============ FOOTER MAIN ============ */
    .footer-modern{
        position: relative;
        overflow: hidden;
        padding-top: 48px !important;
        padding-bottom: 20px !important;
        background:
            radial-gradient(900px 500px at 0% 0%, color-mix(in srgb, var(--footer-grad1) 18%, transparent), transparent 60%),
            radial-gradient(800px 600px at 100% 100%, color-mix(in srgb, var(--footer-grad2) 20%, transparent), transparent 60%),
            radial-gradient(circle at top left, var(--footer-bg1) 0%, var(--footer-bg2) 55%, var(--footer-bg3) 100%) !important;
        color: var(--footer-text);
        font-family: 'Hind Siliguri', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        isolation: isolate;
    }

    .footer-modern::before{
        content:""; position:absolute; left:0; right:0; top:0; height:2px;
        background: linear-gradient(90deg, transparent, var(--footer-grad1), var(--footer-grad2), var(--footer-grad1), transparent);
        background-size: 300% 100%;
        animation: borderShimmer 6s linear infinite;
        z-index: 3;
    }
    @keyframes borderShimmer {
        from { background-position: 0% 0%; }
        to   { background-position: 300% 0%; }
    }

    .footer-modern::after{
        content:""; position:absolute;
        width: 400px; height: 400px;
        border-radius: 50%; pointer-events: none;
        top: -120px; right: -120px;
        background: radial-gradient(circle, color-mix(in srgb, var(--footer-grad2) 22%, transparent), transparent 70%);
        filter: blur(40px);
        animation: orbDrift 14s ease-in-out infinite;
        z-index: 0;
    }
    .footer-modern .footer-orb-2{
        position:absolute;
        width: 320px; height: 320px;
        border-radius: 50%; pointer-events: none;
        bottom: -100px; left: -100px;
        background: radial-gradient(circle, color-mix(in srgb, var(--footer-grad1) 24%, transparent), transparent 70%);
        filter: blur(40px);
        animation: orbDrift2 18s ease-in-out infinite;
        z-index: 0;
    }
    @keyframes orbDrift  { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-30px,24px) scale(1.06);} }
    @keyframes orbDrift2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(40px,-28px) scale(1.05);} }

    .footer-grid-bg{
        position:absolute; inset:0; pointer-events:none; z-index:0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0);
        background-size: 28px 28px;
        mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
    }

    .footer-modern > .container{ position: relative; z-index: 2; }

    .footer-modern a{
        color: var(--footer-text);
        text-decoration: none;
        transition: color .25s ease, opacity .25s ease, transform .25s ease;
    }
    .footer-modern a:hover{ color: var(--footer-hover); }

    /* ============ TRUST BANNER ============ */
    .footer-brand-banner{
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--footer-grad1) 12%, transparent),
            color-mix(in srgb, var(--footer-grad2) 12%, transparent));
        border: 1px solid color-mix(in srgb, var(--footer-grad1) 25%, transparent);
        border-radius: 18px;
        padding: 12px 18px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }
    .footer-brand-banner::before{
        content:""; position:absolute; top:0; left:-130%;
        width: 50%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.10), transparent);
        transform: skewX(-20deg);
        animation: bannerSweep 8s ease-in-out infinite;
        animation-delay: 2s;
    }
    @keyframes bannerSweep{
        0%, 70% { left: -130%; }
        100%    { left: 200%; }
    }

    .brand-stats-row{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
    }
    .brand-stat{
        flex: 1;
        text-align: center;
        position: relative;
        padding: 2px 6px;
        min-width: 0;
    }
    .brand-stat .stat-num{
        display: block;
        font-size: 1.15rem;
        font-weight: 900;
        letter-spacing: -.3px;
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.1;
    }
    .brand-stat .stat-label{
        display: block;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--footer-subtitle);
        font-weight: 700;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .brand-stat .stat-icon{
        font-size: 16px;
        margin-bottom: 2px;
        color: var(--footer-grad1);
        display: inline-block;
        transition: transform .35s var(--ease-bounce);
    }
    .brand-stat:hover .stat-icon{ transform: rotate(-12deg) scale(1.18); }
    .brand-stat:hover .stat-num{ animation: numPop .4s var(--ease-bounce); }
    @keyframes numPop{
        0%   { transform: scale(1); }
        50%  { transform: scale(1.12); }
        100% { transform: scale(1); }
    }
    .stat-divider{
        flex: 0 0 1px;
        width: 1px; height: 36px;
        background: linear-gradient(180deg, transparent, rgba(255,255,255,.18), transparent);
    }

    @media (max-width: 991.98px){
        .footer-brand-banner{ padding: 10px 12px; border-radius: 16px; margin-bottom: 24px; }
        .brand-stat .stat-num{ font-size: 1.05rem; }
        .brand-stat .stat-label{ font-size: 9.5px; }
        .brand-stat .stat-icon{ font-size: 14px; }
    }
    @media (max-width: 575.98px){
        .footer-brand-banner{ padding: 8px; border-radius: 12px; margin-bottom: 20px; }
        .brand-stats-row{ gap: 4px; }
        .brand-stat{ padding: 2px; }
        .brand-stat .stat-num{ font-size: .9rem; }
        .brand-stat .stat-label{ font-size: 8.5px; letter-spacing: .3px; }
        .brand-stat .stat-icon{ font-size: 13px; margin-bottom: 1px; }
        .stat-divider{ height: 30px; }
    }

    /* ============ COLUMNS (2 Columns Layout) ============ */
    .footer-col{
        position: relative;
        z-index: 2;
    }

    .footer-top-row{
        --bs-gutter-y: 24px;
        --bs-gutter-x: 2rem;
        align-items: flex-start !important;
    }

    /* ===== BRAND COLUMN (Left) ===== */
    .brand-col{
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }
    .footer-logo-link{
        position: relative;
        display: inline-flex;
        align-items: center; justify-content: center;
        padding: 6px 12px;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        transition: transform .35s var(--ease-out), background .3s ease, border-color .3s ease;
    }
    .footer-logo-link:hover{
        transform: translateY(-2px) scale(1.02);
        background: rgba(255,255,255,.07);
        border-color: color-mix(in srgb, var(--footer-grad1) 40%, transparent);
    }
    .footer-logo-img{
        max-height: 46px; width: auto; object-fit: contain;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,.30));
    }

    .footer-tagline,
    .footer-tagline i{ color: var(--footer-text) !important; opacity: 1 !important; }
    .footer-tagline{
        display: inline-flex; align-items: center; gap: 8px;
        padding: 5px 12px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 999px;
        font-size: 12.5px;
        transition: background .3s ease, border-color .3s ease, transform .35s var(--ease-out);
    }
    .footer-tagline:hover{
        background: rgba(255,255,255,.08);
        border-color: color-mix(in srgb, var(--footer-grad1) 40%, transparent);
        transform: translateY(-2px);
    }
    .footer-tagline i{ color: var(--footer-grad1) !important; }

    /* ===== TITLES ===== */
    .footer-title{
        font-size: .95rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        position: relative;
        display: inline-block;
        padding-bottom: 6px;
        margin-bottom: 10px !important;
    }
    .footer-title::after{
        content:"";
        position: absolute; left: 0; bottom: 0;
        width: 34px; height: 2px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        border-radius: 2px;
        transition: width .4s var(--ease-out);
    }
    .footer-col:hover .footer-title::after{ width: 64px; }

    /* ===== CATEGORY PILLS ===== */
    .footer-links{
        row-gap: .4rem;
        position: relative;
        z-index: 2;
    }
    .footer-pill-link{
        position: relative;
        font-size: 12px;
        padding: .35rem .75rem;
        border-radius: 999px;
        background: color-mix(in srgb, var(--pill-bg) 55%, transparent);
        border: 1px solid color-mix(in srgb, var(--pill-border) 35%, transparent);
        white-space: nowrap;
        box-shadow: 0 3px 8px rgba(0,0,0,.16);
        display: inline-flex; align-items: center; justify-content: center;
        z-index: 5; pointer-events: auto; cursor: pointer;
        overflow: hidden;
        isolation: isolate;
        transition: transform .3s var(--ease-out), background .3s ease, border-color .3s ease, color .3s ease, box-shadow .35s ease;
    }
    .footer-pill-link::before{
        content:""; position:absolute; inset:0; border-radius:999px;
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        opacity: 0;
        transition: opacity .35s ease;
        z-index: -1;
    }
    .footer-pill-link:hover{
        transform: translateY(-2px) rotate(-1deg);
        border-color: transparent !important;
        color: var(--pill-hover-text) !important;
        box-shadow: 0 8px 16px rgba(0,0,0,.25);
    }
    .footer-pill-link:hover::before{ opacity: 1; }

    /* ===== QUICK LINK GRID ===== */
    .footer-link-grid{
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 8px 16px;
        padding: 0; margin: 0; list-style: none;
    }
    .footer-link-grid a{
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; color: var(--footer-text);
        opacity: .85; position: relative;
        transition: opacity .25s ease, padding-left .3s var(--ease-out), color .25s ease;
    }
    .footer-link-grid a::before{
        content: "›"; font-size: 16px; line-height: 1;
        color: var(--footer-grad1); opacity: 0;
        transform: translateX(-6px);
        transition: opacity .3s ease, transform .3s var(--ease-out);
    }
    .footer-link-grid a:hover{ opacity: 1; color: var(--footer-hover); padding-left: 4px; }
    .footer-link-grid a:hover::before{ opacity: 1; transform: translateX(0); }

    /* ===== CONTACT GRID ===== */
    .footer-contact-grid{
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
        padding: 0; margin: 0; list-style: none;
    }
    .footer-contact-grid li{
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; transition: transform .25s ease;
    }
    .footer-contact-grid li:hover{ transform: translateX(3px); }

    .contact-ico{
        flex: 0 0 32px; width: 32px; height: 32px;
        border-radius: 9px; display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, color-mix(in srgb, var(--footer-grad1) 18%, transparent), color-mix(in srgb, var(--footer-grad2) 18%, transparent));
        border: 1px solid color-mix(in srgb, var(--footer-grad1) 30%, transparent);
        color: var(--footer-grad1); font-size: 13px;
        transition: transform .35s var(--ease-bounce), background .3s ease;
    }
    .footer-contact-grid li:hover .contact-ico{ transform: rotate(-8deg) scale(1.08); }
    
    .contact-body{ display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
    .contact-body .contact-label{
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; color: var(--footer-subtitle);
    }
    .contact-body a, .contact-body span.contact-val{
        color: var(--footer-text); font-size: 13px; font-weight: 600; word-break: break-word;
    }
    .contact-body a:hover{ color: var(--footer-hover); }

    /* ===== SOCIAL ===== */
    .footer-social{ display: inline-flex; flex-wrap: wrap; gap: 6px; }
    .footer-social-icon{
        position: relative; width: 30px; height: 30px; border-radius: 9px;
        border: 1px solid color-mix(in srgb, var(--social-border) 45%, transparent);
        display: flex; align-items: center; justify-content: center;
        font-size: 12.5px; background: color-mix(in srgb, var(--social-bg) 70%, transparent);
        box-shadow: 0 2px 8px rgba(0,0,0,.15); overflow: hidden; isolation: isolate;
        transition: transform .35s var(--ease-bounce), background .3s ease, border-color .3s ease, color .3s ease, box-shadow .35s ease, border-radius .35s ease;
    }
    .footer-social-icon::before{
        content:""; position:absolute; inset:0; border-radius: inherit;
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        opacity: 0; transition: opacity .35s ease; z-index: -1;
    }
    .footer-social-icon:hover{
        transform: translateY(-2px) scale(1.08) rotate(-6deg);
        border-color: transparent; color: var(--social-hover-text);
        box-shadow: 0 6px 14px rgba(0,0,0,.25); border-radius: 999px;
    }
    .footer-social-icon:hover::before{ opacity: 1; }
    .footer-social-icon i{ transition: transform .35s var(--ease-bounce); }
    .footer-social-icon:hover i{ transform: rotate(10deg) scale(1.15); }

    /* ===== SSL & DIVIDER ===== */
    .premium-payment-box {
        display: inline-block; background: #ffffff; padding: 6px 12px; border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.20), 0 0 0 1px rgba(255,255,255,.06);
        position: relative; overflow: hidden; isolation: isolate;
        transition: transform .35s var(--ease-out), box-shadow .35s ease;
    }
    .premium-payment-box::after{
        content:""; position: absolute; top:0; left:-130%; width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(13,110,253,.20), transparent);
        transform: skewX(-20deg); transition: left 1s var(--ease-out);
    }
    .premium-payment-box:hover{ transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 24px rgba(0,0,0,.30); }
    .premium-payment-box:hover::after{ left: 130%; }
    .premium-payment-box img { display: block; max-width: 100%; height: auto; max-height: 32px; position: relative; z-index: 1; }

    .footer-divider{
        position: relative; height: 1px; width: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
        margin: 24px 0 16px;
    }

    /* ===== BOTTOM COPYRIGHT ===== */
    .footer-bottom{ text-align: center; }
    .footer-copy{ font-size: 12px; opacity: .9; margin: 0; line-height: 1.5; }
    .footer-copy a{ text-decoration: none; position: relative; font-weight: 600; color: var(--footer-grad1); }
    .footer-copy a::after{
        content:""; position: absolute; left: 0; right: 0; bottom: -2px; height: 1.5px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        transform: scaleX(0); transform-origin: left; transition: transform .35s ease;
    }
    .footer-copy a:hover::after{ transform: scaleX(1); }

    /* ===== ANIMATIONS ===== */
    .footer-reveal{
        opacity: 0; will-change: transform, opacity;
        transition: opacity .8s var(--ease-out), transform .8s var(--ease-out);
    }
    .footer-reveal.in-view{ opacity: 1; transform: none !important; }
    .reveal-drop { transform: translateY(-20px) scale(.98); }
    .reveal-left { transform: translateX(-30px); }
    .reveal-right{ transform: translateX(30px); }
    .reveal-up   { transform: translateY(20px); }
    .reveal-wipe { transform: scaleX(.3); transform-origin: center; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991.98px){
        .footer-modern{ padding-top: 36px !important; padding-bottom: 16px !important; }
        .footer-title{ font-size: .9rem; }
    }
    @media (max-width: 767.98px){
        .footer-modern{ text-align: center; }
        .brand-col{ align-items: center; }
        .footer-title::after{ left: 50%; transform: translateX(-50%); }
        .footer-links{ justify-content: center; }
        .footer-link-grid{ grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); justify-items: center; }
        .footer-link-grid a{ text-align: center; justify-content: center; }
        .footer-contact-grid{ justify-content: center; justify-items: center; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
        .footer-contact-grid li{ justify-content: center; text-align: left; }
    }
    @media (max-width: 575.98px){
        .footer-modern{ padding-top: 24px !important; padding-bottom: 12px !important; }
        .footer-top-row{ --bs-gutter-y: 20px; }
        .footer-divider{ margin: 16px 0 12px; }
        .footer-pill-link{ font-size: 11.5px; padding: .3rem .7rem; }
        .premium-payment-box img{ max-height: 28px; }
    }

    /* ====================================================
       MOBILE BOTTOM NAVIGATION — Height Reduced
       ==================================================== */
    .footer-nav{
        position: fixed;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 24px);
        max-width: 340px;
        background: var(--mnav-bg);
        border: 1px solid color-mix(in srgb, var(--mnav-border) 60%, transparent);
        border-radius: 999px;
        box-shadow: 0 10px 24px rgba(15,23,42,.15), 0 4px 10px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.6);
        z-index: 99999;
        display: none;
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
        padding-top: 8px; /* Reduced Top Padding */
        overflow: visible; isolation: isolate;
        animation: navPopIn .55s var(--ease-bounce) both;
    }
    @keyframes navPopIn{
        0%   { opacity: 0; transform: translateX(-50%) translateY(50px) scale(.6); }
        70%  { opacity: 1; transform: translateX(-50%) translateY(-2px) scale(1.02); }
        100% { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    }

    .footer-nav::before{
        content:""; position: absolute; inset: -1px; border-radius: 999px; padding: 1px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2), var(--footer-grad1));
        background-size: 200% 100%;
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude;
        opacity: .55; animation: navBorderFlow 5s linear infinite; pointer-events: none; z-index: 1;
    }
    @keyframes navBorderFlow{ from { background-position: 0% 0%; } to { background-position: 200% 0%; } }

    .m-nav-main{
        display: flex; justify-content: space-around; align-items: flex-end;
        padding: 0 6px 4px; gap: 2px; position: relative; z-index: 2;
    }

    .button-shop{ flex: 1; text-align: center; display: flex; justify-content: center; position: relative; }

    .footerBtn{
        position: relative; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
        gap: 1px; text-decoration: none; -webkit-tap-highlight-color: transparent; user-select: none;
        padding: 4px 2px 2px; /* Reduced Padding */
        border-radius: 999px; min-width: 44px; transition: transform .35s var(--ease-bounce);
    }

    .footerBtn .icon-wrap {
        position: relative; display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; /* Reduced Icon Box */
        border-radius: 50%; background: transparent; margin-bottom: 2px;
        transition: transform .4s var(--ease-bounce), background .35s ease, box-shadow .35s ease;
    }
    .footerBtn i{
        font-size: 13px; /* Reduced Icon Size */
        color: var(--mnav-icon); line-height: 1;
        transition: color .3s ease, transform .35s var(--ease-bounce);
    }
    .footerBtn span{
        font-size: 9px; font-weight: 700; color: var(--mnav-icon); line-height: 1.1;
        text-transform: uppercase; letter-spacing: .2px; transition: color .3s ease; max-height: 14px; overflow: hidden;
    }

    /* Lifted State - Reduced Lift */
    .footerBtn:hover .icon-wrap, .footerBtn.active-nav .icon-wrap{
        transform: translateY(-6px) scale(1.04);
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        box-shadow: 0 4px 10px color-mix(in srgb, var(--footer-grad2) 40%, transparent), 0 0 0 2px color-mix(in srgb, var(--footer-grad1) 16%, transparent);
    }
    .footerBtn:hover i, .footerBtn.active-nav i{ color: #fff; transform: scale(1.05); }
    .footerBtn:hover span, .footerBtn.active-nav span{ color: var(--footer-grad2); font-weight: 800; }
    .footerBtn:active{ transform: scale(.94); }

    .footerBtn .ripple{
        position: absolute; top: 4px; left: 50%; transform: translateX(-50%) scale(0);
        width: 30px; height: 30px; border-radius: 50%; background: color-mix(in srgb, var(--footer-grad1) 40%, transparent);
        opacity: 0; pointer-events: none; z-index: 0;
    }
    .footerBtn.active-nav .ripple{ animation: rippleWave 1.6s ease-out infinite; }

    .footer-cart-badge {
        position: absolute; top: -3px; right: -3px;
        background: linear-gradient(135deg, #ef4444, #b91c1c); color: #ffffff;
        font-size: 8.5px; font-weight: 800; min-width: 14px; height: 14px; padding: 0 4px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 999px; border: 1.5px solid var(--mnav-bg); box-shadow: 0 2px 4px rgba(0,0,0,0.25);
        line-height: 1; z-index: 10; pointer-events: none;
    }

    @media (max-width: 575.98px){
        .footer-nav{ display: block; }
        body{ padding-bottom: 74px; } /* Reduced body padding */
    }
    @media (max-width: 360px){
        .footer-nav{ width: calc(100% - 16px); max-width: 320px; }
        .footerBtn{ min-width: 40px; }
        .footerBtn .icon-wrap{ width: 28px; height: 28px; }
        .footerBtn i{ font-size: 12px; }
        .footerBtn:hover .icon-wrap, .footerBtn.active-nav .icon-wrap{ transform: translateY(-5px) scale(1.04); }
    }
</style>

<footer class="footer-modern text-light">
    <div class="footer-orb-2"></div>
    <div class="footer-grid-bg"></div>

    <div class="container">
        {{-- Top Trust Banner --}}
        <div class="footer-brand-banner footer-reveal reveal-drop">
            <div class="brand-stats-row">
                <div class="brand-stat">
                    <i class="fas fa-shipping-fast stat-icon"></i>
                    <span class="stat-num">100%</span>
                    <span class="stat-label">Fast Delivery</span>
                </div>
                <div class="stat-divider"></div>
                <div class="brand-stat">
                    <i class="fas fa-shield-alt stat-icon"></i>
                    <span class="stat-num">Secure</span>
                    <span class="stat-label">Safe Payment</span>
                </div>
                <div class="stat-divider"></div>
                <div class="brand-stat">
                    <i class="fas fa-headset stat-icon"></i>
                    <span class="stat-num">24/7</span>
                    <span class="stat-label">Live Support</span>
                </div>
            </div>
        </div>

        {{-- Main Footer Layout - 2 Columns --}}
        <div class="row footer-top-row">

            {{-- Left Side: Logo, Address, Social Links, Quick Links --}}
            <div class="col-lg-6 col-md-6 footer-col footer-reveal reveal-left">
                <div class="brand-col">
                    <a href="{{ route('front.home') }}" class="footer-logo-link">
                        <img src="{{ asset('uploads/img/'.(!empty($info->footer_logo) ? $info->footer_logo : ($info->site_logo ?? ''))) }}" alt="Logo" class="footer-logo-img img-fluid">
                    </a>

                    @if(!empty($info->address))
                        <span class="footer-tagline">
                            <i class="fa fa-map-marker-alt"></i> {{ $info->address }}
                        </span>
                    @endif

                    <div class="footer-social">
                        @if(!empty($info->facebook)) <a href="{{ $info->facebook }}" target="_blank" class="footer-social-icon"><i class="fab fa-facebook-f"></i></a> @endif
                        @if(!empty($info->youtube)) <a href="{{ $info->youtube }}" target="_blank" class="footer-social-icon"><i class="fab fa-youtube"></i></a> @endif
                        @if(!empty($info->instagram)) <a href="{{ $info->instagram }}" target="_blank" class="footer-social-icon"><i class="fab fa-instagram"></i></a> @endif
                        @if(!empty($info->tiktok)) <a href="{{ $info->tiktok }}" target="_blank" class="footer-social-icon"><i class="fab fa-tiktok"></i></a> @endif
                        @if(!empty($info->twitter)) <a href="{{ $info->twitter }}" target="_blank" class="footer-social-icon"><span style="font-family: system-ui; font-weight: 700;">𝕏</span></a> @endif
                    </div>
                </div>

                @php $legalPages = DB::table('pages')->take(5)->get(); @endphp
                @if($legalPages->count())
                    <h5 class="footer-title mt-3">Quick Links</h5>
                    <ul class="footer-link-grid">
                        @foreach($legalPages as $page)
                            <li><a href="{{ route('front.page.name', $page->page)}}">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Right Side: Popular Categories, Get In Touch --}}
            <div class="col-lg-6 col-md-6 footer-col footer-reveal reveal-right">
                <h5 class="footer-title">Popular Categories</h5>
                <nav class="footer-links d-flex flex-wrap gap-2 mb-4">
                    @foreach(DB::table('categories')->where('is_popular', 1)->take(6)->get() as $cat)
                        <a href="{{ route('front.subCategories1',[$cat->url])}}" class="footer-pill-link">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </nav>

                <h5 class="footer-title mt-2">Get in Touch</h5>
                <ul class="footer-contact-grid">
                    @if(!empty($ownerPhone))
                        <li>
                            <span class="contact-ico"><i class="fa fa-phone-volume"></i></span>
                            <span class="contact-body">
                                <span class="contact-label">Call Us</span>
                                <a href="tel:{{ $ownerPhone }}">{{ $ownerPhone }}</a>
                            </span>
                        </li>
                    @endif
                    @if(!empty($ownerEmail))
                        <li>
                            <span class="contact-ico"><i class="fa fa-envelope"></i></span>
                            <span class="contact-body">
                                <span class="contact-label">Email Us</span>
                                <a href="mailto:{{ $ownerEmail }}">{{ $ownerEmail }}</a>
                            </span>
                        </li>
                    @endif
                    @if(!empty($whatsNumber))
                        <li>
                            <span class="contact-ico"><i class="fab fa-whatsapp"></i></span>
                            <span class="contact-body">
                                <span class="contact-label">WhatsApp</span>
                                <a href="https://wa.me/{{ $whatsNumber }}" target="_blank">{{ $whatsNumberRaw }}</a>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- SSL Commerz Span / Center --}}
        <div class="row mt-4 pt-2 footer-reveal reveal-up">
            <div class="col-12 text-center">
                <div class="premium-payment-box">
                    <img src="{{ asset('frontend/images/ssl.png') }}" alt="We Accept Secure Payment" class="img-fluid">
                </div>
            </div>
        </div>

        <div class="footer-divider footer-reveal reveal-wipe"></div>

        {{-- Bottom Copyright Area --}}
        <div class="footer-bottom">
            <p class="footer-copy w-100 text-center">
                {!! $info->copyright ?? '' !!}
                <span class="mx-1">|</span>
                <a href="https://www.facebook.com/bizcareit" target="_blank" rel="noopener noreferrer">
                    Design &amp; Development by Biz Care IT
                </a>
            </p>
        </div>
    </div>
</footer>

{{-- ============ MOBILE BOTTOM NAV ============ --}}
<div class="footer-nav d-sm-block d-md-none" id="footerNav">
    <div class="m-nav-main">

        <div class="button-shop">
            <a href="{{ !empty($ownerPhone) ? 'tel:'.$ownerPhone : '#' }}" class="footerBtn" data-nav="call">
                <span class="ripple"></span>
                <div class="icon-wrap">
                    <i class="fa fa-phone-volume"></i>
                </div>
                <span>Call</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="{{ route('front.home') }}" class="footerBtn" data-nav="home" aria-label="Home">
                <span class="ripple"></span>
                <div class="icon-wrap">
                    <i class="fa fa-home"></i>
                </div>
                <span>Home</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="{{ route('front.products.index') }}" class="footerBtn" data-nav="shop">
                <span class="ripple"></span>
                <div class="icon-wrap">
                    <i class="fa fa-store"></i>
                </div>
                <span>Shop</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="{{ route('front.carts.index')}}?segment={{request()->segment(1)}}" class="footerBtn cart-dropdown-btn" data-nav="cart">
                <span class="ripple"></span>
                <div class="icon-wrap">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="footer-cart-badge cart-count">{{ getTotalCart() }}</span>
                </div>
                <span>Cart</span>
            </a>
        </div>

    </div>
</div>

<script>
(function(){
    /* Reveal-on-scroll */
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.10, rootMargin: '0px 0px 0px 0px' });
        document.querySelectorAll('.footer-reveal').forEach(el => io.observe(el));
    } else {
        document.querySelectorAll('.footer-reveal').forEach(el => el.classList.add('in-view'));
    }

    /* Mobile bottom-nav active state */
    function setActiveNavFromUrl(){
        const buttons = document.querySelectorAll('#footerNav .footerBtn');
        if (!buttons.length) return;

        const current = window.location.pathname.replace(/\/+$/, '') || '/';
        let best = null;
        let bestLen = -1;

        buttons.forEach(btn => {
            const raw = btn.getAttribute('href') || '';
            if (!raw || raw.startsWith('tel:') || raw.startsWith('mailto:') || raw === '#') return;

            let path;
            try { path = new URL(raw, window.location.origin).pathname.replace(/\/+$/, '') || '/'; }
            catch(e){ return; }

            if (path === current) {
                if (path.length > bestLen) { best = btn; bestLen = path.length + 1000; }
            } else if (path !== '/' && current.indexOf(path + '/') === 0) {
                if (path.length > bestLen) { best = btn; bestLen = path.length; }
            } else if (path === '/' && current === '/') {
                if (bestLen < 0) { best = btn; bestLen = 0; }
            }
        });

        buttons.forEach(b => b.classList.remove('active-nav'));
        if (best) best.classList.add('active-nav');
    }

    function bindNavButtons(){
        const buttons = document.querySelectorAll('#footerNav .footerBtn');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                buttons.forEach(b => b.classList.remove('active-nav'));
                btn.classList.add('active-nav');
            });
            btn.addEventListener('mouseleave', () => {
                setActiveNavFromUrl();
            });
            btn.addEventListener('touchstart', () => {
                buttons.forEach(b => b.classList.remove('active-nav'));
                btn.classList.add('active-nav');
            }, { passive: true });
        });
    }

    setActiveNavFromUrl();
    bindNavButtons();
})();
</script>

<script>
    $(document).ready(function() {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": false,
                "progressBar": false,
                "timeOut": "1000",
                "extendedTimeOut": "300",
                "showDuration": "200",
                "hideDuration": "200"
            };
        }

        $(document).on('click', '.remove-cart, .cart-delete-btn, .close-item, .delete-cart-item, .remove-item, .btn-remove, .remove', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let btn = $(this);
            let url = btn.attr('href') || btn.data('url');

            if(!url || url === '#') return;

            let originalHtml = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if (res && typeof res === 'object') {
                        if (res.view || res.html) {
                            $('#cart-dropdown, .cart-content-wrap').html(res.view || res.html);
                        } else {
                            btn.closest('li, .cart-item, .item, tr').fadeOut(300, function() { $(this).remove(); });
                        }

                        if (res.item !== undefined) $('.cart-count, .cart-item-count').text(res.item);
                        if (res.amount) $('.cart-amount').text('৳ ' + res.amount);

                        if (window.toastr) toastr.success(res.msg || 'Item removed');
                    } else {
                        btn.closest('li, .cart-item, .item, tr').fadeOut(300, function() { $(this).remove(); });
                        if (window.toastr) toastr.success('Item removed');
                    }
                },
                error: function(err) {
                    console.error("Cart Remove Error:", err);
                    btn.closest('li, .cart-item, .item, tr').fadeOut(300, function() { $(this).remove(); });
                    if (window.toastr) toastr.error('Item removed!');
                }
            });
        });
    });
</script>

<div class="cart-dropdown" id="cart-dropdown"></div>
@include('frontend.partials.js')
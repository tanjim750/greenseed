@php
    use App\Models\Information;
    use App\Models\Category;
    $information   = Information::first();
    $categories    = Category::whereNull('parent_id')->where('is_menu', 1)->with('subcats')->get();
    $brandGradient = $information->gradient_code ?? 'linear-gradient(90deg,#0d6efd,#00276C)';
    $brandText     = $information->primary_color ?? '#ffffff';
@endphp

<style>
:root {
    --brand-gradient: {!! $brandGradient !!};
    --brand-text: {{ $brandText }};
}

/* ============ GLOBAL ============ */
body { font-family: 'Hind Siliguri', sans-serif; }
.main-bg { background: var(--brand-gradient) !important; color: var(--brand-text) !important; }
.main-bg a, .main-bg p, .main-bg span, .main-bg i { color: var(--brand-text) !important; }

/* ============ TOP NOTICE BAR (smooth scrolling) ============ */
.topbar-strides {
    background: #fff5f0;
    border-bottom: 1px solid #ffe5d6;
    height: 36px;
    overflow: hidden;
    position: relative;
}
.topbar-strides .notice-track {
    display: inline-flex;
    align-items: center;
    height: 100%;
    gap: 60px;
    white-space: nowrap;
    will-change: transform;
    animation: noticeScroll 200s linear infinite;
    padding-left: 100%;
}
.topbar-strides:hover .notice-track { animation-play-state: paused; }
.topbar-strides .notice-text {
    font-size: 13px;
    font-weight: 500;
    color: #2a2a2a;
    letter-spacing: .3px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.topbar-strides .notice-text::before {
    content: "\f005";
    font-family: "Font Awesome 5 Free", "Font Awesome 5 Pro", sans-serif;
    font-weight: 900;
    font-size: 10px;
    color: #ff6b35;
}
@keyframes noticeScroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

/* ============ DESKTOP HEADER ============ */
.strides-header {
    width: 100%;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    position: relative;
    z-index: 999;
    animation: headerFadeIn .6s ease both;
}
@keyframes headerFadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ===== MAIN ROW ===== */
.strides-main-row {
    background: var(--brand-gradient);
    padding: 14px 30px;
    position: relative;
    overflow: hidden;
}
.strides-main-row::after {
    content: "";
    position: absolute;
    top: -50%; right: -10%;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,.08), transparent 70%);
    border-radius: 50%;
    animation: floatGlow 8s ease-in-out infinite;
    pointer-events: none;
}
@keyframes floatGlow {
    0%, 100% { transform: translate(0,0) scale(1); }
    50%      { transform: translate(-30px, 20px) scale(1.1); }
}
.strides-main-inner {
    max-width: 1500px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 30px;
    position: relative;
    z-index: 2;
}

/* LOGO */
.strides-logo { flex: 0 0 auto; transition: transform .3s ease; }
.strides-logo:hover { transform: scale(1.03); }
.strides-logo img { max-height: 50px; object-fit: contain; display: block; filter: drop-shadow(0 2px 6px rgba(0,0,0,.1)); }

/* SEARCH */
.strides-search { flex: 1; max-width: 720px; margin: 0 auto; }
.strides-search form { margin: 0; }
.strides-search-box { position: relative; width: 100%; }
.strides-search-box input {
    width: 100%;
    height: 48px;
    border-radius: 999px;
    border: 2px solid transparent;
    padding: 0 60px 0 25px;
    font-size: 15px;
    color: #333;
    outline: none;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.10);
    transition: all .3s ease;
}
.strides-search-box input:focus {
    border-color: rgba(0,0,0,.85);
    box-shadow: 0 6px 22px rgba(0,0,0,.18);
    transform: translateY(-1px);
}
.strides-search-box input::placeholder { color: #999; transition: color .3s ease; }
.strides-search-box input:focus::placeholder { color: #bbb; }
.strides-search-box button {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #000;
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    transition: all .35s cubic-bezier(.34,1.56,.64,1);
}
.strides-search-box button:hover {
    background: #1a1a1a;
    transform: translateY(-50%) scale(1.1) rotate(8deg);
    box-shadow: 0 4px 14px rgba(0,0,0,.3);
}

/* ===== RIGHT ACTIONS ===== */
.strides-actions {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
}
.strides-actions a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand-text) !important;
    text-decoration: none !important;
    position: relative;
    font-size: 15px;
    transition: all .35s cubic-bezier(.34,1.56,.64,1);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(6px);
}
.strides-actions a::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(255,255,255,.25);
    transform: scale(0);
    transition: transform .35s ease;
    z-index: -1;
}
.strides-actions a:hover {
    transform: translateY(-3px);
    border-color: rgba(255,255,255,.4);
    box-shadow: 0 6px 16px rgba(0,0,0,.18);
}
.strides-actions a:hover::before { transform: scale(1); }
.strides-actions a:hover i { animation: iconPop .5s ease; }
@keyframes iconPop {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.25) rotate(-8deg); }
    100% { transform: scale(1); }
}

.strides-actions a.cart-btn {
    background: #000;
    border-color: #000;
}
.strides-actions a.cart-btn:hover {
    background: #1a1a1a;
    box-shadow: 0 6px 18px rgba(0,0,0,.35);
}

.strides-actions .custom-cart-badge {
    position: absolute !important;
    top: -5px !important;
    right: -5px !important;
    background: #fff !important;
    color: #000 !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    width: 19px !important;
    height: 19px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 50% !important;
    line-height: 1 !important;
    border: 2px solid var(--brand-text) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
    animation: badgePulse 2.5s ease-in-out infinite;
}
@keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50%      { transform: scale(1.12); }
}

/* ============ MENU ROW ============ */
.strides-menu-row {
    background: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    padding: 6px 30px;
    position: relative;
}
.strides-menu-row::after {
    content: "";
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 80px; height: 2px;
    background: var(--brand-gradient);
    border-radius: 999px;
    opacity: .35;
}
.strides-menu-inner {
    max-width: 1500px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
}
.strides-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
}
.strides-menu > li { position: relative; }
.strides-menu > li > a {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: .8px;
    text-transform: uppercase;
    color: #1a1a1a !important;
    text-decoration: none !important;
    border-radius: 999px;
    transition: all .35s cubic-bezier(.34,1.56,.64,1);
    white-space: nowrap;
    position: relative;
    overflow: hidden;
}
.strides-menu > li > a::before {
    content: "";
    position: absolute;
    inset: 0;
    background: var(--brand-gradient);
    border-radius: 999px;
    transform: scale(0);
    transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    z-index: -1;
}
.strides-menu > li > a:hover,
.strides-menu > li:hover > a,
.strides-menu > li.active > a {
    color: var(--brand-text) !important;
    transform: translateY(-1px);
}
.strides-menu > li > a:hover::before,
.strides-menu > li:hover > a::before,
.strides-menu > li.active > a::before { transform: scale(1); }

.strides-menu > li.has-sub > a::after {
    content: "\f107";
    font-family: "Font Awesome 5 Free", "Font Awesome 5 Pro", sans-serif;
    font-weight: 900;
    font-size: 11px;
    margin-left: 3px;
    opacity: .85;
    transition: transform .3s ease;
}
.strides-menu > li.has-sub:hover > a::after { transform: rotate(180deg); }

.strides-submenu {
    position: absolute;
    top: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%) translateY(15px);
    min-width: 230px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 15px 40px rgba(0,0,0,.14);
    list-style: none;
    padding: 12px 0;
    margin: 0;
    opacity: 0;
    visibility: hidden;
    transition: all .35s cubic-bezier(.34,1.56,.64,1);
    z-index: 9999;
}
.strides-submenu::before {
    content: "";
    position: absolute;
    top: -4px;
    left: 0; right: 0;
    height: 4px;
    background: var(--brand-gradient);
    border-radius: 12px 12px 0 0;
}
.strides-menu > li.has-sub:hover .strides-submenu {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}
.strides-submenu li a {
    display: block;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 500;
    color: #333 !important;
    text-decoration: none !important;
    text-transform: capitalize;
    transition: all .25s ease;
}
.strides-submenu li a:hover {
    background: #fafafa;
    color: #000 !important;
    padding-left: 32px;
}

/* ============================================== */
/* ============ NEW MOBILE HEADER ================ */
/* ============================================== */
.strides-mobile-header {
    background: #fff5f0;
    box-shadow: 0 2px 12px rgba(0,0,0,.04);
    border-bottom: 1px solid #ffe5d6;
    position: relative;
    z-index: 998;
}

/* Logo row */
.strides-mobile-top {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px 15px 8px;
    position: relative;
}
.strides-mobile-logo img {
    max-height: 42px;
    object-fit: contain;
    display: block;
}

/* Action row (hamburger + search + user) */
.strides-mobile-bottom {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 15px 14px;
}

/* Hamburger button */
.strides-mobile-menu-btn {
    flex: 0 0 auto;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #f0d8c8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a1a1a;
    font-size: 18px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: all .3s ease;
    padding: 0;
}
.strides-mobile-menu-btn:hover,
.strides-mobile-menu-btn:focus {
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,.10);
    transform: translateY(-1px);
}

/* Search */
.strides-mobile-search {
    flex: 1;
    margin: 0;
    position: relative;
}
.strides-mobile-search input {
    width: 100%;
    height: 44px;
    border-radius: 999px;
    border: 1px solid #f0d8c8;
    background: #ffffff;
    padding: 0 50px 0 45px;
    font-size: 14px;
    color: #333;
    outline: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    transition: all .3s ease;
}
.strides-mobile-search input::placeholder { color: #999; }
.strides-mobile-search input:focus {
    border-color: #ff6b35;
    box-shadow: 0 4px 14px rgba(255,107,53,.12);
}
.strides-mobile-search::before {
    content: "\f002";
    font-family: "Font Awesome 5 Free", "Font Awesome 5 Pro", sans-serif;
    font-weight: 900;
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    font-size: 14px;
    pointer-events: none;
}
.strides-mobile-search button {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #ff6b35;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* User icon */
.strides-mobile-user {
    flex: 0 0 auto;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #f0d8c8;
    display: flex !important;
    align-items: center;
    justify-content: center;
    color: #1a1a1a !important;
    font-size: 17px;
    text-decoration: none !important;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: all .3s ease;
    position: relative;
}
.strides-mobile-user:hover {
    box-shadow: 0 4px 14px rgba(0,0,0,.10);
    transform: translateY(-1px);
}

/* ============ RESPONSIVE ============ */
@media (max-width: 991px) {
    .desktop, .strides-header { display: none !important; }
    .custom-back-top { display: none !important; }
    .custom-floating-wa { bottom: 90px !important; right: 12px !important; }
    .topbar-strides .notice-track { animation-duration: 200s; }
}
@media (min-width: 992px) {
    .mobile, .strides-mobile-header { display: none !important; }
}
@keyframes fadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

/* ===================================================== */
/* ====== NEW PREMIUM OFFCANVAS CATEGORY MENU ========== */
/* ===================================================== */
.premium-mobile-menu {
    width: 320px !important;
    z-index: 999999;
    border: none !important;
    background: #ffffff !important;
    box-shadow: 8px 0 40px rgba(0,0,0,.18) !important;
    display: flex;
    flex-direction: column;
}

/* Header */
.pmm-header {
    position: relative;
    background: #fff5f0;
    padding: 20px 18px 18px;
    border-bottom: 1px solid #ffe5d6;
    overflow: hidden;
}
.pmm-header::after {
    content: "";
    position: absolute;
    bottom: -1px; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ff6b35, #ffb088, #ff6b35);
    background-size: 200% 100%;
    animation: pmmBarMove 4s linear infinite;
}
@keyframes pmmBarMove {
    0% { background-position: 0% 0; }
    100% { background-position: 200% 0; }
}
.pmm-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #ffffff !important;
    border: 1px solid #f0d8c8 !important;
    color: #1a1a1a !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
.pmm-close:hover { background: #ff6b35 !important; color: #fff !important; transform: rotate(90deg); }
.pmm-close i { font-size: 13px; }

.pmm-logo { display: inline-block; margin-bottom: 14px; }
.pmm-logo img { max-height: 38px; display: block; }

.pmm-user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ffffff;
    border: 1px solid #f0d8c8;
    padding: 10px 12px;
    border-radius: 14px;
    text-decoration: none !important;
    transition: all .3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.pmm-user-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); }
.pmm-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #ff6b35, #ff8f5e);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 800; flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(255,107,53,.30);
}
.pmm-user-info { flex: 1; min-width: 0; }
.pmm-welcome { display: block; font-size: 11px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }
.pmm-username { display: block; font-size: 15px; color: #1a1a1a !important; font-weight: 700; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none !important; }
.pmm-user-card i.fa-chevron-right { color: #ff6b35; font-size: 12px; }

/* Quick grid */
.pmm-quick-grid {
    margin: 14px;
    background: #ffffff;
    border: 1px solid #f0e6df;
    border-radius: 16px;
    padding: 12px 8px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    box-shadow: 0 4px 14px rgba(0,0,0,.04);
}
.pmm-quick-item {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
    border-radius: 10px;
    text-decoration: none !important;
    color: #1a1a1a !important;
    transition: all .3s cubic-bezier(.34,1.56,.64,1);
    text-align: center;
}
.pmm-quick-item:hover, .pmm-quick-item:active {
    background: #fff5f0;
    transform: translateY(-2px);
}
.pmm-quick-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: linear-gradient(135deg, #ff6b35, #ff8f5e);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(255,107,53,.25);
    transition: transform .3s cubic-bezier(.34,1.56,.64,1);
}
.pmm-quick-item:hover .pmm-quick-icon { transform: scale(1.12) rotate(-5deg); }
.pmm-quick-label { font-size: 10.5px; font-weight: 700; color: #1a1a1a; letter-spacing: .2px; line-height: 1.2; }

/* Section title */
.pmm-section-title {
    padding: 14px 18px 10px;
    font-size: 11px;
    font-weight: 800;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pmm-section-title::before {
    content:"";
    width: 4px; height: 14px;
    background: linear-gradient(180deg, #ff6b35, #ff8f5e);
    border-radius: 999px;
}

/* Body / cat list */
.pmm-body {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 110px;
}
.pmm-body::-webkit-scrollbar { width: 4px; }
.pmm-body::-webkit-scrollbar-thumb { background: #f0d8c8; border-radius: 999px; }

.pmm-cat-list { list-style: none; margin: 0; padding: 0 14px; }
.pmm-cat-item {
    margin-bottom: 8px;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #f4ece6;
    transition: all .3s ease;
}
.pmm-cat-item:hover {
    border-color: #ffd0b8;
    box-shadow: 0 4px 14px rgba(255,107,53,.08);
}
.pmm-cat-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
}
.pmm-cat-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: #fff5f0;
    color: #ff6b35;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
    border: 1px solid #ffe5d6;
    transition: all .3s ease;
}
.pmm-cat-item:hover .pmm-cat-icon {
    background: linear-gradient(135deg, #ff6b35, #ff8f5e);
    color: #fff;
    border-color: transparent;
    transform: scale(1.08);
}
.pmm-cat-link {
    flex: 1;
    color: #1a1a1a !important;
    font-weight: 600;
    font-size: 14.5px;
    text-decoration: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.pmm-cat-arrow {
    width: 30px; height: 30px; border-radius: 8px;
    background: #fff5f0;
    border: 0;
    color: #ff6b35;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; cursor: pointer;
    transition: all .3s ease; flex-shrink: 0;
}
.pmm-cat-arrow:not(.collapsed) {
    background: linear-gradient(135deg, #ff6b35, #ff8f5e);
    color: #fff;
    transform: rotate(180deg);
}
.pmm-cat-arrow.collapsed { transform: rotate(0); }

.pmm-subcat-list {
    list-style: none;
    margin: 0;
    padding: 0 12px 10px 56px;
    background: #fffaf6;
}
.pmm-subcat-list li { margin: 2px 0; }
.pmm-subcat-list a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    color: #555 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: all .25s ease;
}
.pmm-subcat-list a::before {
    content:"";
    width: 5px; height: 5px;
    background: #ffb088;
    border-radius: 50%;
    flex-shrink: 0;
    transition: all .25s ease;
}
.pmm-subcat-list a:hover {
    background: #ffffff;
    color: #ff6b35 !important;
    padding-left: 14px;
    box-shadow: 0 2px 6px rgba(0,0,0,.04);
}
.pmm-subcat-list a:hover::before { background: #ff6b35; transform: scale(1.4); }

/* Footer */
.pmm-footer {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    padding: 12px 14px;
    background: #ffffff;
    border-top: 1px solid #f4ece6;
    box-shadow: 0 -4px 20px rgba(0,0,0,.05);
    z-index: 5;
}
.pmm-contact {
    display: flex !important;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #ff6b35, #ff8f5e);
    padding: 11px 14px;
    border-radius: 12px;
    text-decoration: none !important;
    color: #fff !important;
    transition: all .3s ease;
    box-shadow: 0 6px 18px rgba(255,107,53,.25);
    position: relative;
    overflow: hidden;
}
.pmm-contact::before {
    content:"";
    position: absolute;
    top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.30), transparent);
    transform: skewX(-20deg);
    transition: left .8s ease;
}
.pmm-contact:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255,107,53,.35); }
.pmm-contact:hover::before { left: 200%; }
.pmm-contact-icon {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    flex-shrink: 0; font-size: 15px;
    animation: pmmRing 1.8s ease-in-out infinite;
}
@keyframes pmmRing { 0%, 100% { transform: rotate(0); } 10%, 30% { transform: rotate(-12deg); } 20%, 40% { transform: rotate(12deg); } 50% { transform: rotate(0); } }
.pmm-contact-text { flex: 1; min-width: 0; line-height: 1.2; }
.pmm-contact-text small { display: block; font-size: 10px; opacity: .9; text-transform: uppercase; letter-spacing: .5px; font-weight: 500; }
.pmm-contact-text strong { display: block; font-size: 14px; font-weight: 800; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.offcanvas-backdrop.show { opacity: .55; backdrop-filter: blur(2px); }

.premium-mobile-menu.show .pmm-cat-item { animation: pmmSlideIn .4s ease both; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(1) { animation-delay: .06s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(2) { animation-delay: .10s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(3) { animation-delay: .14s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(4) { animation-delay: .18s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(5) { animation-delay: .22s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(6) { animation-delay: .26s; }
.premium-mobile-menu.show .pmm-cat-item:nth-child(n+7) { animation-delay: .30s; }
@keyframes pmmSlideIn { from { opacity: 0; transform: translateX(-15px); } to { opacity: 1; transform: translateX(0); } }
.premium-mobile-menu.show .pmm-quick-item { animation: pmmFadeUp .4s ease both; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(1) { animation-delay: .12s; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(2) { animation-delay: .16s; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(3) { animation-delay: .20s; }
.premium-mobile-menu.show .pmm-quick-item:nth-child(4) { animation-delay: .24s; }
@keyframes pmmFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 360px) { .premium-mobile-menu { width: 290px !important; } .pmm-quick-label { font-size: 9.5px; } }

/* ============ FLOATING WA + BACK TOP ============ */
.custom-floating-wa { position: fixed !important; right: 20px !important; bottom: 175px !important; width: 45px !important; height: 45px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; background: var(--brand-gradient) !important; color: var(--brand-text) !important; box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important; text-decoration: none !important; z-index: 999999 !important; transition: all 0.3s ease !important; border: 1px solid rgba(255,255,255,0.2) !important; }
.custom-floating-wa::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; background: var(--brand-gradient); z-index: -1; opacity: 0.6; animation: pulse-ring 2s infinite cubic-bezier(0.215, 0.61, 0.355, 1); }
@keyframes pulse-ring { 0% { transform: scale(0.95); opacity: 0.7; } 50% { opacity: 0.4; } 100% { transform: scale(1.6); opacity: 0; } }
.custom-floating-wa:hover { transform: scale(1.1) !important; background: #25D366 !important; color: #fff !important; border-color: #25D366 !important; }
.custom-floating-wa:hover::before { background: #25D366 !important; }
.custom-floating-wa i { font-size: 24px !important; z-index: 2; }
.scroll-top, #scroll-top, .back-to-top:not(.custom-back-top) { display: none !important; }
.custom-back-top { position: fixed !important; bottom: 25px !important; right: 20px !important; width: 42px !important; height: 42px !important; border-radius: 8px !important; display: flex !important; align-items: center !important; justify-content: center !important; background: #000000 !important; color: #ffffff !important; box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important; text-decoration: none !important; z-index: 999998 !important; opacity: 0; visibility: hidden; transition: all 0.3s ease !important; border: 1px solid rgba(255,255,255,0.1) !important; }
.custom-back-top.show-btn { opacity: 1; visibility: visible; transform: translateY(0); }
.custom-back-top:hover { transform: translateY(-5px) !important; background: var(--brand-gradient) !important; color: var(--brand-text) !important; }
.custom-back-top i { font-size: 16px !important; }

body.hide-header .strides-header,
body.hide-header .strides-mobile-header,
body.hide-header .topbar-strides,
body.hide-header .axil-header { display: none !important; }

#toast-container > div { max-width: 250px !important; padding: 10px 15px 10px 45px !important; border-radius: 6px !important; min-height: 40px !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; }
.toast-message { font-size: 13px !important; font-weight: 500 !important; line-height: 1.4 !important; }
.toast-title { font-size: 14px !important; font-weight: bold !important; }

.cart-dropdown .cart-content-wrap { width: 320px !important; }
@media (max-width: 767px) {
    .cart-dropdown .cart-content-wrap { width: 280px !important; height: calc(100vh - 30px) !important; bottom: 30px !important; top: 0 !important; }
    .cart-dropdown { height: calc(100vh - 30px) !important; bottom: 30px !important; }
}
</style>

<div class="container">
    {{-- ============ TOP NOTICE BAR ============ --}}
    @if(isset($information) && $information->topbar_active == 1 && !empty($information->topbar_notice))
        <div class="topbar-strides">
            <div class="notice-track">
                <span class="notice-text">{{ $information->topbar_notice }}</span>
                <span class="notice-text">{{ $information->topbar_notice }}</span>
                <span class="notice-text">{{ $information->topbar_notice }}</span>
                <span class="notice-text">{{ $information->topbar_notice }}</span>
                <span class="notice-text">{{ $information->topbar_notice }}</span>
            </div>
        </div>
    @endif

    {{-- ============ DESKTOP HEADER ============ --}}
    <header class="desktop header axil-header strides-header">
        <div class="strides-main-row">
            <div class="strides-main-inner">
                <div class="strides-logo">
                    <a href="{{ route('front.home') }}">
                        <img src="{{ asset('uploads/img/'.$information->site_logo) }}" alt="Site Logo">
                    </a>
                </div>
                <div class="strides-search">
                    <form action="{{ route('front.products.index') }}" method="GET">
                        <div class="strides-search-box">
                            <input type="search" name="q" value="{{ request('q') ?? '' }}" placeholder="Looking for something?" autocomplete="off">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="strides-actions">
                    <a href="{{ route('front.order.track') }}" title="Track Order"><i class="fas fa-truck"></i></a>
                    <a href="tel:{{ $information->owner_phone }}" title="Call Us"><i class="fas fa-phone-alt"></i></a>
                    @guest
                        <a href="{{ route('login') }}" title="Login"><i class="far fa-user"></i></a>
                    @else
                        <a href="{{ route('front.dashboard.index') }}" title="My Account"><i class="fas fa-user-check"></i></a>
                    @endguest
                    <a href="{{ route('front.carts.index') }}?segment={{ request()->segment(1) }}" class="cart-btn cart-dropdown-btn" title="Cart">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="custom-cart-badge cart-count">{{ getTotalCart() }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="strides-menu-row">
            <div class="strides-menu-inner">
                <ul class="strides-menu">
                    <li><a href="{{ route('front.home') }}">Home</a></li>
                    @foreach($categories as $cat)
                        <li class="{{ $cat->subcats->count() ? 'has-sub' : '' }}">
                            <a href="{{ route('front.subCategories1', [$cat->url]) }}">{{ $cat->name }}</a>
                            @if($cat->subcats->count())
                                <ul class="strides-submenu">
                                    @foreach($cat->subcats as $sub)
                                        <li><a href="{{ route('front.subsubCategories', [$sub->url]) }}">{{ $sub->name }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                    <li><a href="{{ route('front.products.index') }}">Shop</a></li>
                </ul>
            </div>
        </div>
    </header>

    {{-- ============ NEW MOBILE HEADER (matches reference image) ============ --}}
    <header class="mobile header axil-header strides-mobile-header">

        {{-- Row 1: Logo centered --}}
        <div class="strides-mobile-top">
            <a href="{{ route('front.home') }}" class="strides-mobile-logo">
                <img src="{{ asset('uploads/img/'.$information->site_logo) }}" alt="Site Logo">
            </a>
        </div>

        {{-- Row 2: Hamburger | Search | User --}}
        <div class="strides-mobile-bottom">
            <button class="strides-mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>

            <form action="{{ route('front.products.index') }}" method="GET" class="strides-mobile-search">
                <input type="search" name="q" value="{{ request('q') ?? '' }}" placeholder="Looking for..." autocomplete="off">
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </form>

            @guest
                <a href="{{ route('login') }}" class="strides-mobile-user" title="Login">
                    <i class="far fa-user"></i>
                </a>
            @else
                <a href="{{ route('front.dashboard.index') }}" class="strides-mobile-user" title="My Account">
                    <i class="fas fa-user-check"></i>
                </a>
            @endguest
        </div>
    </header>
</div> 
{{-- ============ NEW PREMIUM OFFCANVAS CATEGORY MENU ============ --}}
<div class="offcanvas offcanvas-start premium-mobile-menu" tabindex="-1" id="mobileMenu">
    <div class="pmm-header">
        <button type="button" class="pmm-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <a href="{{ route('front.home') }}" class="pmm-logo">
            <img src="{{ asset('uploads/img/'.$information->site_logo) }}" alt="Logo">
        </a>
        @guest
            <a href="{{ route('login') }}" class="pmm-user-card">
                <div class="pmm-avatar"><i class="fas fa-user"></i></div>
                <div class="pmm-user-info">
                    <span class="pmm-welcome">Welcome</span>
                    <span class="pmm-username">Login / Register</span>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <a href="{{ route('front.dashboard.index') }}" class="pmm-user-card">
                <div class="pmm-avatar">
                    {{ strtoupper(substr(auth()->user()->first_name ?: auth()->user()->mobile, 0, 1)) }}
                </div>
                <div class="pmm-user-info">
                    <span class="pmm-welcome">Hello,</span>
                    <span class="pmm-username">{{ auth()->user()->first_name ? auth()->user()->first_name : auth()->user()->mobile }}</span>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
        @endguest
    </div>

    <div class="pmm-quick-grid">
        <a href="{{ route('front.home') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="fas fa-home"></i></div>
            <span class="pmm-quick-label">Home</span>
        </a>
        <a href="{{ route('front.products.index') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="fas fa-store"></i></div>
            <span class="pmm-quick-label">Shop</span>
        </a>
        <a href="{{ route('front.order.track') }}" class="pmm-quick-item">
            <div class="pmm-quick-icon"><i class="fas fa-truck"></i></div>
            <span class="pmm-quick-label">Track</span>
        </a>
        @auth
            <a href="{{ route('front.dashboard.index') }}" class="pmm-quick-item">
                <div class="pmm-quick-icon"><i class="fas fa-user-check"></i></div>
                <span class="pmm-quick-label">Account</span>
            </a>
        @else
            <a href="tel:{{ $information->owner_phone }}" class="pmm-quick-item">
                <div class="pmm-quick-icon"><i class="fas fa-phone-alt"></i></div>
                <span class="pmm-quick-label">Call</span>
            </a>
        @endauth
    </div>

    <div class="pmm-section-title">
        <span>Shop by Category</span>
    </div>

    <div class="pmm-body">
        <ul class="pmm-cat-list">
            @foreach($categories as $key => $cat)
                <li class="pmm-cat-item">
                    <div class="pmm-cat-row">
                        <div class="pmm-cat-icon">
                            <i class="fas fa-{{ $cat->subcats->count() ? 'layer-group' : 'tag' }}"></i>
                        </div>
                        <a href="{{ route('front.subCategories1', [$cat->url]) }}" class="pmm-cat-link">
                            {{ $cat->name }}
                        </a>
                        @if($cat->subcats->count() > 0)
                            <button class="pmm-cat-arrow collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#pmmCat_{{ $key }}"
                                    aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        @endif
                    </div>
                    @if($cat->subcats->count() > 0)
                        <div class="collapse" id="pmmCat_{{ $key }}">
                            <ul class="pmm-subcat-list">
                                @foreach($cat->subcats as $sub)
                                    <li>
                                        <a href="{{ route('front.subsubCategories', [$sub->url]) }}">
                                            {{ $sub->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    @if(!empty($information->owner_phone))
        <div class="pmm-footer">
            <a href="tel:{{ $information->owner_phone }}" class="pmm-contact">
                <div class="pmm-contact-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="pmm-contact-text">
                    <small>Need help? Call us</small>
                    <strong>{{ $information->owner_phone }}</strong>
                </div>
            </a>
        </div>
    @endif
</div>

@if($information->whats_active == '1')
    <a href="https://wa.me/+88{{ $information->whats_num }}" target="_blank" class="custom-floating-wa">
        <i class="fab fa-whatsapp"></i>
    </a>
@endif

<a href="#top" class="custom-back-top" id="backto-top">
    <i class="fas fa-arrow-up"></i>
</a>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
    function onScroll(){
        var st = $(window).scrollTop();
        var btn = $('#backto-top');
        if (st > 300) btn.addClass('show-btn'); else btn.removeClass('show-btn');
    }
    onScroll();
    $(window).on('scroll', onScroll);

    $('#backto-top').on('click', function(e) {
        e.preventDefault(); $('html, body').animate({scrollTop:0}, '300');
    });

    $(document).on('click', '.cart-dropdown-btn', function() {
        $('body').addClass('hide-header');
    });
    $(document).on('click', '.close-cart, .cart-close, .close, .cart-cancel, .cart-overlay, .closeModal', function() {
        $('body').removeClass('hide-header');
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.cart-dropdown, .cart-dropdown-btn, #cart-dropdown, #cart_section, .cart-content-wrap').length) {
            $('body').removeClass('hide-header');
        }
    });

    $('#mobileMenu').on('click', '.pmm-cat-link, .pmm-quick-item, .pmm-subcat-list a, .pmm-user-card', function(){
        var off = bootstrap.Offcanvas.getInstance(document.getElementById('mobileMenu'));
        if(off) setTimeout(function(){ off.hide(); }, 150);
    });
});
</script>
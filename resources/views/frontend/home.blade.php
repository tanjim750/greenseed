@extends('frontend.app')

@section('content')

@php
    use App\Models\AdminText;
    $adminText = AdminText::first();
    
    // Dynamic Texts
    $popularTitle = $adminText->popular_category_title ?? 'POPULAR CATEGORIES';
    $viewAllText  = $adminText->view_all_text ?? 'View All';

    // Dynamic Colors
    $primaryColor = $adminText->primary_color ?? '#000000';
    $primaryHover = $adminText->primary_hover_color ?? '#222222';
    $bgColor      = $adminText->bg_color ?? '#ffffff';
    $textColor    = $adminText->text_color ?? '#111111';
    $sectionBg    = $adminText->section_bg ?? '#f8f8f8';
@endphp

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />

    <style>
        /* ============================================================
           GLOBAL
           ============================================================ */
        html, body { overflow-x: hidden !important; max-width: 100%; }
        body { background: {{ $bgColor }} !important; font-family:'Hind Siliguri', sans-serif; color: {{ $textColor }}; }
        .main-wrapper { background: {{ $bgColor }}; overflow-x:hidden; width:100%; max-width:100vw; padding-top: 20px; padding-bottom: 20px; }
        a { text-decoration:none; }
        .main-wrapper .row { margin-left:0 !important; margin-right:0 !important; }

        .swiper:not(.swiper-initialized) .swiper-slide { display:none; }
        .swiper:not(.swiper-initialized) .swiper-slide:first-child { display:block; }

        .desktop-slide { display:block; }
        .mobile-slide  { display:none; }
        @media (max-width: 991px){
            .desktop-slide { display:none !important; }
            .mobile-slide  { display:block !important; }
        }

        /* ============================================================
           KEYFRAMES
           ============================================================ */
        @keyframes fadeInUp   { from{opacity:0;transform:translate3d(0,40px,0);} to{opacity:1;transform:translate3d(0,0,0);} }
        @keyframes fadeInDown { from{opacity:0;transform:translate3d(0,-30px,0);} to{opacity:1;transform:translate3d(0,0,0);} }
        @keyframes bannerTextReveal { from{opacity:0;transform:translate(-50%,40px);} to{opacity:1;transform:translate(-50%,0);} }
        @keyframes kenBurnsZoom { 0%{transform:scale(1) translate(0,0);} 50%{transform:scale(1.12) translate(-1%,-1%);} 100%{transform:scale(1) translate(0,0);} }
        @keyframes pulseZoom { 0%,100%{transform:scale(1);} 50%{transform:scale(1.08);} }
        @keyframes pulseGlow { 0%,100%{box-shadow:0 0 0 0 rgba(0,0,0,.3);} 50%{box-shadow:0 0 0 12px rgba(0,0,0,0);} }
        @keyframes underlineGrow { from{width:0;} to{width:60px;} }
        @keyframes arrowSlide { 0%,100%{transform:translateX(0);} 50%{transform:translateX(6px);} }
        @keyframes textReveal { 0%{opacity:0;letter-spacing:4px;} 100%{opacity:1;letter-spacing:1.5px;} }
        @keyframes catRingSpin { from{transform:rotate(0deg);} to{transform:rotate(360deg);} }
        @keyframes dotBounce { 0%,80%,100%{transform:scale(.6);opacity:.5;} 40%{transform:scale(1.2);opacity:1;} }

        /* ============================================================
           1. HERO SLIDER
           ============================================================ */
        .hero-slider-wrap { width:100%; margin:0; padding:0; background: {{ $sectionBg }}; position:relative; overflow:hidden; animation:fadeInDown 1s ease both; border-radius: 8px; }
        .hero-slider-wrap .swiper-slide { overflow:hidden; position:relative; }
        .hero-slider-wrap img { width:100%; height:auto; aspect-ratio:21/9; display:block; object-fit:cover; will-change:transform; }
        @media (max-width:768px){ .hero-slider-wrap img { aspect-ratio:16/9; } }
        .main-swiper .swiper-slide-active img { animation: kenBurnsZoom 8s ease-in-out infinite; }

        .img-overlay::after {
            content:""; position:absolute; inset:0;
            background:linear-gradient(180deg, rgba(0,0,0,.05) 0%, rgba(0,0,0,.28) 100%);
            pointer-events:none; transition:background .5s ease; z-index:2;
        }
        .promo-banner.img-overlay:hover::after { background:linear-gradient(180deg, rgba(0,0,0,.1) 0%, rgba(0,0,0,.45) 100%); }

        .banner-text {
            position:absolute; bottom:50px; left:50%; text-align:center; z-index:10;
            width:auto; max-width:90%; pointer-events:none;
            opacity:0; transform:translate(-50%, 40px);
            animation: bannerTextReveal 1s ease .5s forwards;
            white-space:nowrap;
        }
        .banner-text span {
            display:inline-block; font-size:16px; font-weight:700;
            text-transform:uppercase; letter-spacing:1.5px;
            color:#fff; padding-bottom:4px;
            text-shadow:0 2px 10px rgba(0,0,0,.5); position:relative;
        }
        .main-swiper .swiper-slide-active .banner-text span { animation:textReveal 1.2s cubic-bezier(.22,1,.36,1) .4s forwards; }
        .banner-text span::after {
            content:""; position:absolute; left:0; bottom:0; height:2px; width:100%;
            background:#fff; transform-origin:left; transition:transform .5s ease;
        }
        .promo-banner:hover .banner-text span::after { transform:scaleX(1.2); }

        @media (max-width:768px){
            .banner-text span { font-size:12px; letter-spacing:1px; padding-bottom:3px; }
            .banner-text { bottom:30px; }
        }
        @media (max-width:480px){
            .banner-text { bottom:20px; }
            .banner-text span { font-size:11px; letter-spacing:.8px; }
        }

        /* ============================================================
           2. PROMO BANNERS
           ============================================================ */
        .promo-banner { display:block; width:100%; position:relative; overflow:hidden; background: {{ $sectionBg }}; transition:all .5s cubic-bezier(.22,1,.36,1); border-radius: 8px; }
        .promo-banner img { transition:transform 1s cubic-bezier(.22,1,.36,1), filter .6s ease; will-change:transform; width:100%; object-fit:cover; display:block; }
        .banner-full img { aspect-ratio:16/5; }
        .banner-half img { aspect-ratio:16/9; }
        @media (max-width:768px){ .banner-full img { aspect-ratio:16/7; } }
        @media (min-width:992px){ .promo-banner:hover img { transform:scale(1.08); filter:brightness(1.05); } }

        .promo-banner::before {
            content:""; position:absolute; top:0; left:-75%;
            width:50%; height:100%;
            background:linear-gradient(120deg, transparent, rgba(255,255,255,.3), transparent);
            transform:skewX(-20deg); z-index:5; pointer-events:none; transition:left 1s ease;
        }
        @media (min-width:992px){ .promo-banner:hover::before { left:125%; } }

        .banner-row { display:flex; flex-wrap:nowrap; gap:8px; padding:0; width:100%; margin:0; }
        .banner-row .banner-col { flex:1 1 50%; min-width:0; padding:0; }

        @media (max-width:991px){
            .promo-banner img { animation: pulseZoom 6s ease-in-out infinite; }
            .banner-row .banner-col:nth-child(1) .promo-banner img { animation-delay:.5s; }
            .banner-row .banner-col:nth-child(2) .promo-banner img { animation-delay:2s; }
        }
        @media (max-width:480px){ .banner-row { gap:6px; } }

        /* ============================================================
           3. SECTION TITLES
           ============================================================ */
        .section-header {
            display:flex; justify-content:space-between; align-items:flex-end;
            margin-bottom:20px; padding-bottom:10px;
            border-bottom:1px solid #eee; position:relative;
        }
        .section-header::after {
            content:""; position:absolute; left:0; bottom:-1px;
            height:2px; width:0; background: {{ $primaryColor }};
            animation: underlineGrow 1.2s ease forwards;
        }
        .section-header h3 {
            font-size:24px; font-weight:700; color: {{ $textColor }}; margin:0;
            text-transform:uppercase; letter-spacing:.5px;
            position:relative; display:inline-block;
        }
        .section-header a {
            font-size:14px; color: {{ $primaryColor }};
            text-decoration:underline !important; text-underline-offset:4px;
            font-weight:500; transition:all .3s ease;
            display:inline-flex; align-items:center; gap:6px; white-space:nowrap;
        }
        .section-header a:hover { color: {{ $primaryHover }}; text-underline-offset:6px; }
        .section-header a::after { content:"→"; display:inline-block; transition:transform .3s ease; }
        .section-header a:hover::after { transform:translateX(4px); }

        @media (max-width:767px){
            .section-header h3 { font-size:18px; }
            .section-header a  { font-size:12px; }
        }

        /* ============================================================
           4. PRODUCT GRID
           ============================================================ */
        .product-section-wrap { padding:30px 0; background: {{ $bgColor }}; }
        .product-section-wrap .row { margin-left:-6px !important; margin-right:-6px !important; }
        .product-section-wrap .row > [class*=col] { padding-left:6px !important; padding-right:6px !important; }

        .product-section-wrap .row > .col {
            opacity:0;
            animation: fadeInUp .7s ease forwards;
        }
        .product-section-wrap .row > .col:nth-child(1)  { animation-delay:.05s; }
        .product-section-wrap .row > .col:nth-child(2)  { animation-delay:.10s; }
        .product-section-wrap .row > .col:nth-child(3)  { animation-delay:.15s; }
        .product-section-wrap .row > .col:nth-child(4)  { animation-delay:.20s; }
        .product-section-wrap .row > .col:nth-child(5)  { animation-delay:.25s; }
        .product-section-wrap .row > .col:nth-child(6)  { animation-delay:.30s; }

        .product-section-wrap .row > .col > * {
            transition: transform .4s cubic-bezier(.22,1,.36,1), box-shadow .4s ease;
            border-radius:6px; overflow:hidden;
            background: {{ $bgColor }};
        }
        .product-section-wrap .row > .col:hover > * {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px -10px rgba(0,0,0,.15);
        }
        .product-section-wrap .row > .col img {
            transition: transform .6s ease;
            width:100%; aspect-ratio:1/1; object-fit:cover;
        }
        .product-section-wrap .row > .col:hover img { transform: scale(1.05); }

        /* ============================================================
           5. POPULAR CATEGORIES
           ============================================================ */
        .popular-section {
            padding: 30px 0 10px;
            background: {{ $bgColor }};
        }
        .popular-section .section-header { margin-bottom: 14px; }

        .popular-swiper-wrap {
            position: relative;
            padding: 6px 0 14px;
            -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 5%, #000 95%, transparent 100%);
                    mask-image: linear-gradient(90deg, transparent 0, #000 5%, #000 95%, transparent 100%);
        }
        .popular-swiper {
            width: 100%;
            overflow: hidden;
        }
        .popular-swiper .swiper-wrapper {
            transition-timing-function: linear !important;
            align-items: center;
        }
        .popular-swiper .swiper-slide {
            width: auto;
            height: auto;
        }

        .popular-cat-box {
            display: block;
            text-align: center;
            color: {{ $textColor }};
            transition: transform .4s cubic-bezier(.22,1,.36,1);
            padding: 6px 4px;
        }
        .popular-cat-box:hover { transform: translateY(-6px); }
        .popular-cat-box .img-wrapper {
            background: {{ $sectionBg }};
            border-radius: 50%;
            width: 110px; height: 110px;
            margin: 0 auto 10px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            border: 1px solid #eee;
            position: relative;
            transition: all .5s cubic-bezier(.22,1,.36,1);
        }
        .popular-cat-box .img-wrapper::before {
            content: "";
            position: absolute; inset: 0;
            border-radius: 50%;
            border: 2px solid {{ $primaryColor }};
            transform: scale(.7);
            opacity: 0;
            transition: all .5s ease;
        }
        .popular-cat-box .img-wrapper::after {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 70%, rgba(0,0,0,.15), transparent);
            opacity: 0;
            animation: catRingSpin 4s linear infinite;
            transition: opacity .4s ease;
            z-index: -1;
        }
        .popular-cat-box:hover .img-wrapper {
            background: {{ $bgColor }};
            border-color: transparent;
            box-shadow: 0 12px 28px -8px rgba(0,0,0,.25);
        }
        .popular-cat-box:hover .img-wrapper::before { transform: scale(1); opacity: 1; }
        .popular-cat-box:hover .img-wrapper::after  { opacity: 1; }

        .popular-cat-box img {
            max-height: 70px;
            width: auto;
            object-fit: contain;
            transition: transform .5s ease;
        }
        .popular-cat-box:hover img { transform: scale(1.12) rotate(-5deg); }
        .popular-cat-box p {
            font-size: 13.5px;
            font-weight: 600;
            margin: 0;
            color: {{ $textColor }};
            transition: all .3s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }
        .popular-cat-box:hover p { color: {{ $primaryColor }}; letter-spacing: .5px; }

        @media (max-width: 768px) {
            .popular-cat-box .img-wrapper { width: 86px; height: 86px; margin-bottom: 8px; }
            .popular-cat-box img { max-height: 56px; }
            .popular-cat-box p   { font-size: 12px; max-width: 100px; }
        }
        @media (max-width: 480px) {
            .popular-cat-box .img-wrapper { width: 78px; height: 78px; }
            .popular-cat-box img { max-height: 50px; }
        }

        /* ============================================================
           6. BOTTOM CTA
           ============================================================ */
        .bottom-cta-container { text-align:center; margin:20px 0 50px; padding:0 15px; }
        .bottom-view-btn {
            display:inline-flex; align-items:center; gap:8px;
            padding:14px 34px; font-size:14px; font-weight:700;
            text-transform:uppercase; letter-spacing:1px;
            color:#fff !important; background: {{ $primaryColor }};
            border-radius:4px; position:relative; overflow:hidden;
            transition:all .4s cubic-bezier(.22,1,.36,1);
            animation: pulseGlow 2.5s ease-in-out infinite;
        }
        .bottom-view-btn::before {
            content:""; position:absolute; top:0; left:-100%;
            width:100%; height:100%;
            background:linear-gradient(120deg, transparent, rgba(255,255,255,.3), transparent);
            transition:left .6s ease;
        }
        .bottom-view-btn:hover::before { left:100%; }
        .bottom-view-btn:hover {
            background: {{ $primaryHover }}; transform:translateY(-3px);
            box-shadow:0 10px 25px -8px rgba(0,0,0,.4);
        }
        .bottom-view-btn i { transition:transform .3s ease; }
        .bottom-view-btn:hover i { animation: arrowSlide .8s ease infinite; }

        @media (max-width: 480px){
            .bottom-view-btn { padding:12px 24px; font-size:12px; letter-spacing:.5px; }
        }

        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
@endpush

<main class="main-wrapper">
    {{-- Puratai ekta Container e Wrap kora holo --}}
    <div class="container bg-white" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); padding-top: 15px;">

        {{-- 1. DESKTOP HERO SLIDER --}}
        <div class="desktop-slide p-0">
            <div class="hero-slider-wrap">
                <div class="swiper main-swiper desktop-swiper">
                    <div class="swiper-wrapper">
                        @foreach($sliders as $s)
                            <div class="swiper-slide img-overlay">
                                <a href="{{$s->link}}">
                                    <img src="{{ getImage('sliders', $s->image) }}" alt="Slider Image">
                                    <div class="banner-text"><span>Shop Now</span></div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 1. MOBILE HERO SLIDER --}}
        <div class="mobile-slide p-0">
            <div class="hero-slider-wrap">
                <div class="swiper main-swiper mobile-swiper">
                    <div class="swiper-wrapper">
                        @foreach($sliders as $s)
                            <div class="swiper-slide img-overlay">
                                <a href="{{$s->link}}">
                                    <img src="{{ getImage('mobile_sliders', $s->mobile_image) }}" alt="Mobile Slider">
                                    <div class="banner-text"><span>Shop Now</span></div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($featured_images))

        {{-- 2. 1st FULL WIDTH BANNER --}}
        <div class="px-0 mt-3" data-aos="fade-up" data-aos-duration="900">
            <a href="{{ $featured_images->right_link ?? '#' }}" class="promo-banner banner-full img-overlay">
                <img src="{{ !empty($featured_images->right_image) ? asset('homeimages/'.$featured_images->right_image) : 'https://via.placeholder.com/1600x500?text=Full+Width+Banner+1' }}" alt="Full Banner 1">
                <div class="banner-text"><span>Shop Now</span></div>
            </a>
        </div>

        {{-- 3. TWO IMAGES SIDE-BY-SIDE --}}
        <div class="px-0 mt-3">
            <div class="banner-row">
                <div class="banner-col" data-aos="fade-right" data-aos-duration="900">
                    <a href="{{ $featured_images->left_link_1 ?? '#' }}" class="promo-banner banner-half img-overlay">
                        <img src="{{ $featured_images->left_image_1 ? asset('homeimages/'.$featured_images->left_image_1) : 'https://via.placeholder.com/800x400?text=Image+1' }}" alt="Promo 1">
                        <div class="banner-text"><span>Shop Now</span></div>
                    </a>
                </div>
                <div class="banner-col" data-aos="fade-left" data-aos-duration="900">
                    <a href="{{ $featured_images->left_link_2 ?? '#' }}" class="promo-banner banner-half img-overlay">
                        <img src="{{ $featured_images->left_image_2 ? asset('homeimages/'.$featured_images->left_image_2) : 'https://via.placeholder.com/800x400?text=Image+2' }}" alt="Promo 2">
                        <div class="banner-text"><span>Shop Now</span></div>
                    </a>
                </div>
            </div>
        </div>

        {{-- 4. 2nd FULL WIDTH BANNER --}}
        <div class="px-0 mt-3" data-aos="fade-up" data-aos-duration="900">
            <a href="{{ $featured_images->left_link_3 ?? '#' }}" class="promo-banner banner-full img-overlay">
                <img src="{{ !empty($featured_images->left_image_3) ? asset('homeimages/'.$featured_images->left_image_3) : 'https://via.placeholder.com/1600x500?text=Full+Width+Banner+2' }}" alt="Full Banner 2">
                <div class="banner-text"><span>Shop Now</span></div>
            </a>
        </div>

        @endif

        {{-- 5. POPULAR CATEGORY --}}
        <div class="popular-section mt-4 px-2" data-aos="fade-up" data-aos-duration="800">
            <div class="section-header">
                <h3>{{ $popularTitle }}</h3>
            </div>

            <div class="popular-swiper-wrap">
                <div class="swiper popular-swiper">
                    <div class="swiper-wrapper">
                        @foreach($cats as $cat)
                            <div class="swiper-slide">
                                <a href="{{ route('front.subCategories1', [$cat->url]) }}" class="popular-cat-box">
                                    <div class="img-wrapper">
                                        <img src="{{ getImage('categories', $cat->image) }}" alt="{{ $cat->name }}">
                                    </div>
                                    <p>{{ $cat->name }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. DYNAMIC PRODUCT SECTIONS --}}
        @foreach ($homeProducts as $categoryId => $products)
            @php
                $catUrl  = $products->first()->category->url ?? null;
                $catName = $products->first()->category->name ?? '';
            @endphp

            <div class="product-section-wrap mt-4 px-2" data-aos="fade-up" data-aos-duration="800">
                @if($catName)
                    <div class="section-header">
                        <h3>{{ $catName }}</h3>
                        @if($catUrl)
                            <a href="{{ route('front.subCategories1', [$catUrl]) }}">{{ $viewAllText }}</a>
                        @endif
                    </div>
                @endif

                <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-2">
                    @foreach($products as $product)
                        @continue($loop->iteration > 6)
                        <div class="col">
                            @include('frontend.products.partials.product_section', ['adminText' => $adminText])
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- 7. BOTTOM CTA --}}
        <div class="bottom-cta-container" data-aos="zoom-in" data-aos-duration="900">
            <a href="{{ route('front.products.index') }}" class="bottom-view-btn">
                View All Products <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>

    </div>
</main>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 60,
        disable: function() {
            return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        }
    });

    new Swiper('.desktop-swiper', {
        loop: true,
        speed: 1500,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: 5500, disableOnInteraction: false, pauseOnMouseEnter: true },
        grabCursor: true
    });

    new Swiper('.mobile-swiper', {
        loop: true,
        speed: 1200,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: 4500, disableOnInteraction: false },
        touchRatio: 1.2,
        grabCursor: true
    });

    new Swiper('.popular-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 18,
        loop: true,
        loopAdditionalSlides: 6,
        speed: 4000,
        allowTouchMove: true,
        grabCursor: true,
        freeMode: false,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
            pauseOnMouseEnter: false
        },
        on: {
            touchEnd(swiper) {
                setTimeout(function () {
                    if (swiper && swiper.autoplay && !swiper.autoplay.running) {
                        swiper.autoplay.start();
                    }
                }, 50);
            },
            slideChangeTransitionEnd(swiper) {
                if (swiper.autoplay && !swiper.autoplay.running) {
                    swiper.autoplay.start();
                }
            }
        }
    });

    window.addEventListener('load', function () { AOS.refresh(); });

});
</script>

<script>
(function(){
    function getCsrf(){
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    document.addEventListener('click', function(e){
        const btn = e.target.closest('button[type="submit"]');
        if(!btn) return;
        const form = btn.closest('form');
        if(!form) return;

        if(form.getAttribute('action') && form.getAttribute('action').includes('carts')){
            form.classList.add('cart_form');
            let at = form.querySelector('input[name="action_type"]');
            if(!at){
                at = document.createElement('input');
                at.type = 'hidden';
                at.name = 'action_type';
                form.appendChild(at);
            }
            at.value = btn.getAttribute('data-action') || 'order';
        }
    });

    document.addEventListener('submit', function(e){
        const form = e.target;
        if(!form || !form.classList.contains('cart_form')) return;
        e.preventDefault();

        const actionUrl = form.getAttribute('action');
        const fd = new FormData(form);

        fetch(actionUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrf() },
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if(res && res.url){ window.location.href = res.url; return; }
            window.location.reload();
        })
        .catch(() => window.location.reload());
    }, true);
})();
</script>
@endpush
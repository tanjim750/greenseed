<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $ln_pg->title1 ?? 'Premium Landing Page' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @php
        $information = \App\Models\Information::first();
        $activeManuals = \App\Models\ManualPayment::where('status', 1)->get();
        
        $charges = \App\Models\DeliveryCharge::whereNotNull('status')->get();
        $sslActive = $information->ssl_active ?? 0;
        $sslTermsActive = $information->ssl_terms_active ?? 0;
        
        $globalSetting = DB::table('delivery_charges')->first();
        $isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';

        $headerBg     = $ln_pg->theme_primary_col ?? '#0a6624'; 
        $primaryColor = $headerBg;
        $btnBg        = $ln_pg->btn_bg_color ?? '#900b0b'; 
        $contactBg    = '#1877f2'; 
        $bodyBg       = '#f5f6f8'; 
        
        $productId = $product->id ?? 0;
        $productName = $product->name ?? 'Product';
        $phoneNumber = $ln_pg->phone ?? '01XXX-XXXXXX';
        
        $waNumberClean = preg_replace('/\D+/', '', $phoneNumber);

        $defaultPrice = ($product && $product->after_discount > 0) ? $product->after_discount : ($product->sell_price ?? 0);
        
        if(!empty($ln_pg->new_price)) {
            $defaultPrice = $ln_pg->new_price;
        }

        $variations = collect();
        if($product){
            try{
                $product->loadMissing(['variations.size','variations.color', 'variations.stocks']);
                $variations = $product->variations ?? collect();
            }catch(\Throwable $e){
                $variations = $product->variations ?? collect();
            }
        }
        $pixelId = setting('fb_pixel_id') ?? null;
        
        $isFreeShipping = (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) ? 1 : 0;
    @endphp

    <style>
        .variation-cards { display:flex; flex-direction:column; gap:10px; }
        .variation-card { display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; cursor:pointer; transition:all .15s ease; }
        .variation-card:hover { border-color:#f0b27a; }
        .variation-card.active { border-color:#d35400; background:#fff7f0; box-shadow:0 0 0 3px rgba(211,84,0,.10); }
        .variation-card .vc-check { color:#cbd5e1; font-size:20px; line-height:1; flex-shrink:0; }
        .variation-card.active .vc-check { color:#d35400; }
        .variation-card .vc-name { font-weight:700; color:#2c3e50; font-size:15px; flex:1; }
        .variation-card .vc-price { font-weight:800; color:#d35400; font-size:16px; white-space:nowrap; }
        body { font-family: 'Hind Siliguri', sans-serif; background-color: {{ $bodyBg }}; color: #333; margin: 0; padding: 0;}
        
        .header-title { font-size: clamp(24px, 4vw, 36px); font-weight: 800; text-align: center; color: #111827; margin: 20px 0; padding: 0 15px; }
        .header-title span { color: {{ $btnBg }}; }

        .content-box { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .section-title { font-size: 20px; font-weight: 800; color: #fff; background-color: {{ $headerBg }}; padding: 12px; margin-bottom: 20px; text-align: center; border-radius: 4px; }
        
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        .feature-list { list-style: none; padding: 0; margin: 0; }
        .feature-list li { position: relative; padding-left: 30px; margin-bottom: 12px; font-size: 16px; font-weight: 600; color: #111;}
        .feature-list li i { position: absolute; left: 0; top: 4px; color: {{ $headerBg }}; font-size: 18px; }

        .trust-images { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; }
        .trust-images img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 8px; border: 2px solid #ddd; }

        .sticky-form-wrapper { position: sticky; top: 20px; z-index: 100; }
        .order-form-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #ddd;}
        .form-header { font-size: 22px; font-weight: 800; text-align: center; color: #fff; background: {{ $headerBg }}; padding: 12px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
        
        .package-option { display: block; border: 2px solid #e5e7eb; border-radius: 6px; padding: 12px 15px; margin-bottom: 15px; cursor: pointer; transition: 0.3s; background: #f9fafb; }
        .package-option:hover { border-color: {{ $headerBg }}; background: #fff; }
        .package-input:checked + .package-option { border-color: {{ $headerBg }}; background: #f0fff4; box-shadow: 0 0 0 1px {{ $headerBg }}; }
        .package-input { display: none; }
        
        .pkg-flex { display: flex; align-items: center; justify-content: space-between; }
        .pkg-title { font-weight: 700; font-size: 16px; color: #1f2937; display: flex; align-items: center; gap: 10px; }
        .pkg-radio-circle { width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; display: inline-block; position: relative; }
        .package-input:checked + .package-option .pkg-radio-circle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 10px; height: 10px; background: {{ $headerBg }}; border-radius: 50%; }
        .pkg-price { font-weight: 800; color: {{ $headerBg }}; font-size: 18px; }
        .pkg-discount { display: block; font-size: 13px; color: #ef4444; font-weight: 600; margin-top: 5px; margin-left: 30px; }

        .form-control, .form-select { padding: 10px 15px; border-radius: 4px; border: 1px solid #ccc; font-weight: 500; font-size: 15px;}
        .form-control:focus, .form-select:focus { border-color: {{ $headerBg }}; box-shadow: 0 0 0 3px rgba(10, 102, 36, 0.1); }
        .form-label { font-weight: 700; color: #333; font-size: 14px; margin-bottom: 6px; }
        
        .payment-radio-box { cursor: pointer; border: 1px solid #ccc; padding: 12px; border-radius: 6px; width: 100%; background: #f9fafb; display: flex; align-items: center; transition: 0.2s;}
        .payment-radio-box input { margin-right: 10px; accent-color: {{ $headerBg }}; width: 18px; height: 18px;}
        .payment-radio-box.active { border-color: {{ $headerBg }}; background: #f0fff4; }

        #manual_payment_area { border: 1px dashed #cbd5e1; background-color: #f8fafc; }
        .manual-instruction-box { background: rgba(226, 19, 110, 0.08); border: 1px solid rgba(226, 19, 110, 0.2); color: #C90D5E; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap i { position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: #64748b; font-size: 15px; }
        .input-icon-wrap input { padding-left: 45px !important; }

        .calculation-table { width: 100%; margin: 15px 0; font-weight: 600; font-size: 15px; color: #444;}
        .calculation-table td { padding: 8px 0; }
        .calc-total { font-size: 20px; font-weight: 800; color: #000; border-top: 1px solid #ddd; }

        .btn-submit { background: {{ $btnBg }}; color: #fff; font-size: 20px; font-weight: 800; border: none; padding: 12px; width: 100%; border-radius: 4px; transition: 0.3s; animation: pulse 1.5s infinite; }
        .btn-submit:hover { background: #660000; color: #fff; transform: translateY(-2px); }
        .btn-submit.is-disabled { opacity: 0.65; pointer-events: none; animation: none; }
        
        .btn-order-floating { background: {{ $btnBg }}; color: #fff !important; font-weight: 700; font-size: 18px; padding: 10px 30px; border-radius: 4px; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; width: max-content; margin: 20px auto; transition: 0.3s; text-decoration: none; box-shadow: 0 4px 6px rgba(144, 11, 11, 0.3); }
        .btn-order-floating:hover { background-color: #660000; transform: translateY(-2px); }

        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }

        .contact-banner { background-color: {{ $contactBg }}; color: #fff; text-align: center; padding: 25px 15px; border-radius: 6px; margin: 20px 0; box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3); }
        .contact-banner h4 { font-weight: 800; font-size: 20px; margin-bottom: 8px; }
        .contact-banner .phone { font-size: 26px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; }

        .bottom-timer-banner { background: rgb(0, 39, 108); color: #fff; padding: 15px; text-align: center; border-radius: 8px; margin-top: 20px; border: 2px solid {{ $headerBg }}; }
        .timer-box { font-size: 28px; font-weight: 800; color: #fff; font-family: monospace; letter-spacing: 2px; }

        .faq-section { margin-top: 40px; margin-bottom: 40px; }
        .faq-item { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #fff; overflow: hidden; }
        .accordion-button { font-weight: 700; color: #333; background-color: #fff; box-shadow: none !important; font-size: 15px;}
        .accordion-button:not(.collapsed) { color: {{ $headerBg }}; background-color: #f0fdf4; }
        .accordion-body { color: #555; font-size: 15px; line-height: 1.6; }

        .whats_btn { position: fixed; right: 20px; bottom: 50px; z-index: 9999; width: 60px; height: 60px; background: #25D366; display: flex; align-items: center; justify-content: center; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.25); text-decoration: none; transition: transform 0.3s ease; }
        .whats_btn:hover { transform: scale(1.1); }
        .whats_btn img { width: 35px; height: 35px; }

        #otpModal { z-index: 99999 !important; }
        .otp-modal-content { border: none !important; border-radius: 20px !important; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); text-align: center; overflow: hidden; position: relative; }
        .otp-modal-content::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #E2136E, #F6921E); }
        .otp-icon-box { width: 80px; height: 80px; background: #fdf2f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 20px; color: #E2136E; }
        .otp-input { width: 100%; letter-spacing: 15px; text-align: center; font-size: 28px; font-weight: bold; color: #333; border: 2px solid #eee !important; border-radius: 12px !important; background: #fafafa; height: 65px; transition: all 0.3s ease; position: relative; z-index: 999999 !important; }
        .otp-input:focus { border-color: #E2136E !important; background: #fff; box-shadow: 0 5px 15px rgba(226, 19, 110, 0.1) !important; outline: none; }
        .btn-verify { background: linear-gradient(135deg, #E2136E 0%, #C90D5E 100%); border: none; padding: 12px; font-size: 18px; border-radius: 12px; box-shadow: 0 8px 20px rgba(226, 19, 110, 0.3); width: 100%; color: white; font-family: 'Hind Siliguri', sans-serif; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(226, 19, 110, 0.4); }

        @media (max-width: 991px) {
            .sticky-form-wrapper { position: static; margin-top: 30px; }
        }

        @media (max-width: 768px) {
            .whats_btn { width: 50px; height: 50px; right: 10px; bottom: 80px; }
            .whats_btn img { width: 28px; height: 28px; }
        }
    </style>

    @if(!empty($pixelId))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    <script>
        var timeSteps = [10, 30, 60, 120]; 
        timeSteps.forEach(function(seconds) {
            setTimeout(function() {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'time_on_page',
                    'time_spent': seconds,
                    'page_path': window.location.pathname
                });
                if (typeof fbq === 'function') {
                    fbq('trackCustom', 'TimeSpent', { time_in_seconds: seconds });
                }
            }, seconds * 1000);
        });

        var scrollSteps = [25, 50, 75, 90, 100];
        var scrolled = [];
        window.addEventListener('scroll', function() {
            var s = window.scrollY,
                d = document.documentElement.scrollHeight,
                c = window.innerHeight;
            var scrollPercent = (s / (d - c)) * 100;

            scrollSteps.forEach(function(step) {
                if (scrollPercent >= step && !scrolled.includes(step)) {
                    scrolled.push(step);
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'scroll_depth',
                        'scroll_percent': step,
                        'page_path': window.location.pathname
                    });
                    if (typeof fbq === 'function') {
                        fbq('trackCustom', 'ScrollDepth', { scroll_percent: step });
                    }
                }
            });
        });
    </script>
</head>
<body>

<div class="container py-4">
    <h1 class="header-title">{!! $ln_pg->title1 ?? 'Murdha Moaharee মুসলমানের শেষ গোসল ও শেষ বিদায় হোক পরিপূর্ণ শরীয়া মোতাবেক' !!}</h1>
    
    @if($ln_pg->title2)
    <h4 class="text-center text-muted fw-bold mb-4">{{ $ln_pg->title2 }}</h4>
    @endif

    <div class="row">
        <div class="col-lg-7 col-xl-8 mb-4">
            
            @if($ln_pg->right_product_image)
            <div class="content-box p-0 overflow-hidden text-center bg-light">
                <img src="{{ asset('landing_pages/'.$ln_pg->right_product_image) }}" class="img-fluid w-100" style="max-height: 500px; object-fit: contain;">
            </div>
            <a href="#orderForm" class="btn-order-floating"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>
            @endif

            @if($ln_pg->video_url)
            <div class="section-title">অর্ডারের জন্য ভিডিওটি দেখুন</div>
            <div class="content-box p-0 overflow-hidden">
                <div class="video-container text-center bg-dark">
                    @if(strpos($ln_pg->video_url, 'iframe') !== false)
                        {!! $ln_pg->video_url !!}
                    @else
                        <iframe src="{{ $ln_pg->video_url }}" frameborder="0" allowfullscreen></iframe>
                    @endif
                </div>
            </div>
            <a href="#orderForm" class="btn-order-floating"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>
            @endif

            <div class="contact-banner">
                <h4>সরাসরি কথা বলুন এবং অর্ডার নিশ্চিত করুন</h4>
                <p>যেকোনো তথ্য জানতে কল করুন</p>
                <div class="phone"><i class="fas fa-phone-alt"></i> {{ $phoneNumber }}</div>
            </div>

            @if($ln_pg->feature_list)
            <div class="section-title">{{ $ln_pg->feature_title ?? 'কেন আমাদের প্রোডাক্ট কেনা উচিত?' }}</div>
            <div class="content-box">
                <div class="feature-list">
                    {!! str_replace('<ul>', '<ul class="feature-list">', $ln_pg->feature_list) !!} 
                </div>
            </div>
            <a href="#orderForm" class="btn-order-floating"><i class="fas fa-shopping-cart"></i> অর্ডার করতে ক্লিক করুন</a>
            @endif

            @if($ln_pg->left_side_desc)
            <div class="content-box">
                {!! $ln_pg->left_side_desc !!}
            </div>
            @endif

            @if($ln_pg->review_images && $ln_pg->review_images->count() > 0)
            <div class="section-title">{{ $ln_pg->review_title ?? 'কাস্টমার রিভিউ' }}</div>
            <div class="content-box">
                <div class="trust-images">
                    @foreach($ln_pg->review_images as $rv)
                        <img src="{{ asset('review_landing_sliders/'.$rv->review_image) }}" alt="Review">
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bottom-timer-banner">
                <h4 class="mb-2 fw-bold text-white">{{ $ln_pg->countdown_title ?? '১০ মিনিটের মধ্যে অর্ডার করলে ডেলিভারি ফ্রি' }}</h4>
                <div class="timer-box" id="bottom_timer">10:00</div>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            <div class="sticky-form-wrapper" id="orderForm">
                <div class="order-form-card">
                    <div class="form-header">
                        {{ $ln_pg->form_title ?? 'অর্ডারটি কনফার্ম করুন' }}
                    </div>

                    <form id="checkout_land_form" action="{{ route('front.storelandData') }}" method="POST">
                        @csrf
                        <input type="hidden" name="prd_id" value="{{ $productId }}">
                        <input type="hidden" name="amount" id="subtotal_input" value="{{ $defaultPrice }}">
                        <input type="hidden" name="final_amount" id="final_total_input" value="{{ $defaultPrice }}">
                        <input type="hidden" name="quantity" id="form_qty" value="1">
                        <input type="hidden" name="selected_package_id" id="selected_package_id" value="">
                        <input type="hidden" name="purchase_event_id" id="purchase_event_id" value="">

                        <div class="mb-4">
                            <label class="form-label fw-bold">প্যাকেজ সিলেক্ট করুন</label>
                            
                            <label class="w-100">
                                <input type="radio" name="pkg_selection" class="package-input" value="default" data-price="{{ $defaultPrice }}" data-qty="1" checked>
                                <div class="package-option">
                                    <div class="pkg-flex">
                                        <div class="pkg-title">
                                            <span class="pkg-radio-circle"></span> 
                                            {{ $product ? $product->name : 'Product' }} (১ পিস)
                                        </div>
                                        <div class="pkg-price">৳ {{ $defaultPrice }}</div>
                                    </div>
                                </div>
                            </label>

                            @if($ln_pg->packages && $ln_pg->packages->count() > 0)
                                @foreach($ln_pg->packages as $key => $pkg)
                                    <label class="w-100">
                                        <input type="radio" name="pkg_selection" class="package-input" value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-qty="{{ $pkg->qty }}">
                                        <div class="package-option">
                                            <div class="pkg-flex">
                                                <div class="pkg-title">
                                                    <span class="pkg-radio-circle"></span>
                                                    {{ $product ? $product->name : 'Product' }} ({{ $pkg->qty }} পিস)
                                                </div>
                                                <div class="pkg-price">৳ {{ $pkg->price }}</div>
                                            </div>
                                            @if($pkg->discount_text)
                                                <span class="pkg-discount"><i class="fas fa-gift"></i> {{ $pkg->discount_text }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            @endif
                        </div>

                        @if($variations->count() > 0)
                        <div class="mb-3">
                            <label class="form-label fw-bold">সাইজ/কালার সিলেক্ট করুন <span class="text-danger">*</span></label>
                            <div class="variation-cards">
                                @foreach($variations as $v)
                                    @php
                                        $label = trim(($v->size->name ?? '') . ' ' . ($v->color->name ?? ''));
                                    @endphp
                                    <div class="variation-card {{ $loop->first ? 'active' : '' }}" data-id="{{ $v->id }}">
                                        <span class="vc-check"><i class="fas fa-check-circle"></i></span>
                                        <span class="vc-name">{{ $label ?: ('Variation #'.$v->id) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="variation_id" id="variation_id" value="{{ $variations->first()->id ?? '' }}">
                        </div>
                        @else
                            <input type="hidden" name="variation_id" id="variation_select" value="">
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold">আপনার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="name" class="form-control" placeholder="আপনার নাম লিখুন" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="01XXXXXXXXX" maxlength="11" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" id="address" class="form-control" rows="2" placeholder="বাসা নং, রোড, এলাকা, থানা" required></textarea>
                        </div>

                        {{-- ডেলিভারি এলাকা সেকশন সম্পূর্ণ লুকানো — ফ্রি ডেলিভারি --}}

                        <input type="hidden" name="payment_method" value="Cash on Delivery">
                        <div class="mb-4" style="display:none;">
                            <label class="form-label fw-bold">পেমেন্ট মাধ্যম</label>
                            
                            @if(isset($information->cod_active) && $information->cod_active == 1)
                            <label class="payment-radio-box mb-2 active">
                                <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentAction('cod')">
                                <span class="fw-bold">ক্যাশ অন ডেলিভারি</span>
                            </label>
                            @endif
                            
                            @if(isset($information->ssl_active) && $information->ssl_active == 1)
                            <label class="payment-radio-box mb-2">
                                <input type="radio" name="payment_method" value="sslcommerz" onchange="togglePaymentAction('sslcommerz')">
                                <span class="fw-bold">অনলাইন পেমেন্ট (কার্ড/SSL)</span>
                            </label>
                            @endif

                            @if(isset($information->bkash_active) && $information->bkash_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #E2136E;">
                                <input type="radio" name="payment_method" value="bkash" class="me-2 payment-radio" onchange="togglePaymentAction('bkash')">
                                <span class="fw-bold d-flex justify-content-between w-100 align-items-center">
                                    <span>বিকাশ পেমেন্ট (bKash)</span>
                                    <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 20px; width: auto; object-fit: contain;">
                                </span>
                            </label>
                            @endif

                            @if(isset($information->eps_active) && $information->eps_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #17a2b8;">
                                <input type="radio" name="payment_method" value="eps" class="me-2 payment-radio" onchange="togglePaymentAction('eps')">
                                <span class="fw-bold">EPS পেমেন্ট (Easy Payment System)</span>
                            </label>
                            @endif

                            @if(isset($information->nagad_active) && $information->nagad_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #ED1C24;">
                                <input type="radio" name="payment_method" value="nagad" class="me-2 payment-radio" onchange="togglePaymentAction('nagad')">
                                <span class="fw-bold d-flex justify-content-between w-100 align-items-center">
                                    <span>নগদ পেমেন্ট (Nagad)</span>
                                    <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 20px; width: auto; object-fit: contain;">
                                </span>
                            </label>
                            @endif

                            @if(isset($information->uddoktapay_active) && $information->uddoktapay_active == 1)
                            <label class="payment-radio-box mb-2" style="border-color: #28a745;">
                                <input type="radio" name="payment_method" value="uddoktapay" class="me-2 payment-radio" onchange="togglePaymentAction('uddoktapay')">
                                <span class="fw-bold">উদ্দোক্তাপে (UddoktaPay)</span>
                            </label>
                            @endif

                            @foreach($activeManuals as $mp)
                            <label class="payment-radio-box mb-2">
                                <input type="radio" name="payment_method" value="{{ $mp->name }}" 
                                       data-number="{{ $mp->number }}" data-type="{{ $mp->type }}"
                                       onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                                <span class="fw-bold">{{ $mp->name }} ({{ $mp->type }})</span>
                            </label>
                            @endforeach
                        </div>

                        <div id="manual_payment_area" style="display: none;" class="mb-4 p-3 border rounded bg-light" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                            <div class="manual-instruction-box mb-3 d-flex align-items-center p-2 rounded">
                                <i class="fas fa-info-circle fa-2x me-2"></i>
                                <div>
                                    <p id="payment_instruction" class="mb-0 fw-bold hind" style="font-size: 14px;"></p>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-phone-alt"></i>
                                        <input type="text" name="sender_number" id="sender_number" class="form-control" placeholder="017XXXXXXXX">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-bold hind" style="font-size: 13px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-receipt"></i>
                                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="TRX123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($information->ssl_terms_active) && $information->ssl_terms_active == 1)
                        <div class="mb-3 mt-3" id="terms_checkbox_area" style="display: none;">
                            <div class="form-check d-flex align-items-start gap-2">
                                <input class="form-check-input mt-1" type="checkbox" id="agree_terms" name="agree_terms" value="1" style="width: 18px; height: 18px; cursor: pointer; accent-color: {{ $headerBg }};">
                                <label class="form-check-label text-dark mb-0" for="agree_terms" style="cursor: pointer; font-size: 14px; font-weight: 600; line-height: 1.5;">
                                    আমি এই ওয়েবসাইটের 
                                    <a href="{{ route('front.privacyPolicy') ?? '#' }}" target="_blank" style="color: {{ $headerBg }}; text-decoration: underline;">প্রাইভেসি পলিসি</a>, 
                                    <a href="{{ url('/page/terms-condition') ?? '#' }}" target="_blank" style="color: {{ $headerBg }}; text-decoration: underline;">শর্তাবলী</a> এবং 
                                    <a href="{{ route('front.returnPolicy') ?? '#' }}" target="_blank" style="color: {{ $headerBg }}; text-decoration: underline;">রিটার্ন পলিসি</a> 
                                    পড়েছি এবং এর সাথে একমত।
                                </label>
                            </div>
                            <small class="text-danger d-none fw-bold mt-2" id="terms_error">অনলাইন পেমেন্ট করতে হলে আপনাকে শর্তাবলীতে সম্মত হতে হবে।</small>
                        </div>
                        @endif

                        <table class="calculation-table">
                            <tr>
                                <td>সাবটোটাল</td>
                                <td class="text-end">৳ <span id="display_subtotal">{{ $defaultPrice }}</span></td>
                            </tr>
                            <tr>
                                <td>ডেলিভারি চার্জ</td>
                                <td class="text-end" id="calc_shipping_text">৳ <span id="display_delivery">0</span></td>
                            </tr>
                            <tr class="calc-total">
                                <td class="pt-3">সর্বমোট বিল</td>
                                <td class="text-end pt-3">৳ <span id="display_total">{{ $defaultPrice }}</span></td>
                            </tr>
                        </table>

                        <button type="submit" id="submit_btn" class="btn-submit">
                            {{ $ln_pg->btn_text_form ?? 'অর্ডার কনফার্ম করুন' }} <i class="fas fa-arrow-right ms-1"></i>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @if(!empty($ln_pg->faq_title) || !empty($ln_pg->faq_1_q))
    <div class="row">
        <div class="col-lg-8">
            <div class="faq-section">
                <div class="section-title">{{ $ln_pg->faq_title ?? 'সচরাচর জিজ্ঞাসা (FAQ)' }}</div>
                <div class="accordion" id="faqAccordion">
                    @for($i = 1; $i <= 4; $i++)
                        @php 
                            $question = $ln_pg->{'faq_'.$i.'_q'};
                            $answer = $ln_pg->{'faq_'.$i.'_a'};
                        @endphp
                        @if(!empty($question) && !empty($answer))
                        <div class="accordion-item faq-item">
                            <h2 class="accordion-header" id="heading{{$i}}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$i}}" aria-expanded="false" aria-controls="collapse{{$i}}">
                                    {{ $question }}
                                </button>
                            </h2>
                            <div id="collapse{{$i}}" class="accordion-collapse collapse" aria-labelledby="heading{{$i}}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ $answer }}
                                </div>
                            </div>
                        </div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<div style="background: #fff; padding-top: 30px; border-top: 1px solid #e5e7eb; margin-top: 40px;">
    @include('frontend.partials.footer')
</div>

@if(!empty($waNumberClean))
    <a href="https://wa.me/{{ $waNumberClean }}" target="_blank" class="whats_btn" aria-label="WhatsApp">
        <img src="https://img.icons8.com/color/96/whatsapp--v1.png" alt="whatsapp">
    </a>
@endif

<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4 mx-3">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-4">
        <div class="otp-icon-box"><i class="fas fa-shield-alt fa-2x"></i></div>
        <h4 class="fw-bold mb-2 otp-title" style="font-size: 20px;">মোবাইল ভেরিফিকেশন</h4>
        <p class="otp-subtitle" style="font-size: 14px;">আপনার <span class="fw-bold text-dark" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
        <div class="form-group mb-4">
            <input type="text" id="otp_input" maxlength="4" class="form-control otp-input" placeholder="____" autocomplete="one-time-code" inputmode="numeric">
            <small class="text-danger mt-2 d-block fw-bold" id="otp_error"></small>
        </div>
        <button type="button" class="btn-verify" onclick="verifyOtpNow()">যাচাই করুন (Verify)</button>
        <div class="text-center mt-3">
             <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" id="resendOtpBtn" onclick="sendOtpBeforeSubmit(true)">
                 কোড পাননি? <span class="text-primary fw-bold">আবার পাঠান</span>
             </button>
        </div>
      </div>
    </div>
  </div>
</div>

@if(isset($information->bkash_active) && $information->bkash_active == 1)
    <button id="bKash_button" style="display: none;"></button>
    @php
        $bkashScriptUrl = (isset($information->bkash_sandbox) && $information->bkash_sandbox == 1) 
            ? 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js' 
            : 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
    @endphp
    <script src="{{ $bkashScriptUrl }}"></script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    var paymentID;
    var dynamicOrderId;
    var successUrl = '';
    var isFreeShipping = {{ $isFreeShipping }};

    function scrollToForm() {
        document.getElementById('orderForm').scrollIntoView({ behavior: 'smooth' });
    }

    $(document).ready(function() {
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "timeOut": "3000" };

        var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};
        var isTermsEnabled = {{ (isset($information->ssl_terms_active) && $information->ssl_terms_active == 1) ? 'true' : 'false' }};
        
        var isOtpVerified = false;
        var otpSystemEnabled = {{ $information->otp_system ?? 0 }};
        var otpTimerInterval;
        var isSendingOtp = false;

        $('input[name="pkg_selection"]').on('change', function() {
            if($(this).is(':checked')) {
                let p = parseFloat($(this).data('price'));
                let q = parseInt($(this).data('qty'));
                $('#subtotal_input').val(p);
                $('#form_qty').val(q);
                $('#selected_package_id').val($(this).val() !== 'default' ? $(this).val() : '');
                $('#display_subtotal').text(p);
                calculateTotal();
            }
        });

        function calculateTotal() {
            let subtotal = parseFloat($('#subtotal_input').val()) || 0;
            let qty = parseInt($('#form_qty').val()) || 1;
            let selectedDelivery = $('#delivery_charge').find(':selected');
            let cid = selectedDelivery.val();

            if(isFreeShipping == 1) {
                $('#calc_shipping_text').html('<span class="text-success fw-bold">ফ্রি ডেলিভারি</span>');
                let total = subtotal;
                $('#display_total').text(total);
                $('#final_total_input').val(total);
            } else if(isWeightBased && cid) {
                $.ajax({
                    url: "{{ route('front.getDeliveryChargeAjax') }}",
                    type: "POST",
                    data: { delivery_charge_id: cid, product_id: "{{ $productId }}", quantity: qty, _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if(res.success) {
                            let delivery = Math.round(parseFloat(res.charge));
                            $('#calc_shipping_text').html('৳ <span id="display_delivery">' + delivery + '</span>');
                            let total = subtotal + delivery;
                            $('#display_total').text(total);
                            $('#final_total_input').val(total);
                        }
                    }
                });
            } else {
                let delivery = parseFloat(selectedDelivery.data('charge')) || 0;
                $('#calc_shipping_text').html('৳ <span id="display_delivery">' + delivery + '</span>');
                let total = subtotal + delivery;
                $('#display_total').text(total);
                $('#final_total_input').val(total);
            }
        }

        $('#delivery_charge').on('change', calculateTotal);
        calculateTotal();

        // Card-style variation selector: clicking a card selects it (like a radio)
        $(document).on('click', '.variation-card', function(){
            $('.variation-card').removeClass('active');
            $(this).addClass('active');
            $('#variation_id').val($(this).data('id'));
        });

        var autoSaveTimer;
        function saveIncompleteData() {
            var mobile = $('#mobile').val();
            if (mobile && mobile.length >= 11) {
                $.ajax({
                    url: "{{ route('incompleteStore') }}", 
                    type: "POST",
                    data: {
                        mobile: mobile,
                        name: $('#name').val(),
                        address: $('#address').val(),
                        prd_id: "{{ $productId }}",
                        variation_id: ($('#variation_id').val() || $('#variation_select').val() || ''),
                        quantity: $('#form_qty').val(),
                        amount: $('#subtotal_input').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) { }
                });
            }
        }
        $(document).on('keyup', '#mobile', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveIncompleteData, 2000); 
        });
        $(document).on('blur', '#mobile, #name, #address', saveIncompleteData);

        let timerKey = 'lp8_timer_{{ $productId }}';
        let endTime = localStorage.getItem(timerKey);
        let now = new Date().getTime();

        if (!endTime || now > endTime) {
            endTime = now + (10 * 60 * 1000);
            localStorage.setItem(timerKey, endTime);
        }

        setInterval(function() {
            let currentTime = new Date().getTime();
            let timeRemaining = Math.floor((endTime - currentTime) / 1000);

            if (timeRemaining >= 0) {
                let min = Math.floor(timeRemaining / 60);
                let sec = timeRemaining % 60;
                $('#bottom_timer').text((min < 10 ? '0'+min : min) + ':' + (sec < 10 ? '0'+sec : sec));
            } else {
                $('#bottom_timer').text('00:00');
            }
        }, 1000);

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: "begin_checkout",
            ecommerce: {
                currency: "BDT",
                value: $('#subtotal_input').val(),
                items: [{ item_id: "{{ $productId }}", item_name: "{{ $productName }}", price: $('#subtotal_input').val() }]
            }
        });
    });

    window.togglePaymentAction = function(method, name = '', number = '', type = '') {
        var form = document.getElementById('checkout_land_form');
        var btn = $('#submit_btn');
        var termsArea = $('#terms_checkbox_area'); 
        var manualArea = $('#manual_payment_area');
        var sNum = $('#sender_number');
        var tId = $('#transaction_id');

        $('.payment-radio-box').removeClass('active');
        
        var radioVal = method === 'manual' ? name : method;
        $('input[name="payment_method"][value="'+radioVal+'"]').parent('.payment-radio-box').addClass('active');

        if(method === 'sslcommerz' || method === 'bkash' || method === 'eps' || method === 'nagad' || method === 'uddoktapay') {
            if (method === 'sslcommerz') {
                form.action = "{{ url('/pay') }}"; 
                btn.html('Pay Now (Card/SSL) <i class="fas fa-arrow-right ms-1"></i>');
            } else {
                form.action = "{{ route('front.storelandData') }}";
                btn.html('Pay with ' + method.toUpperCase() + ' <i class="fas fa-arrow-right ms-1"></i>');
            }
            if(isTermsEnabled) termsArea.slideDown(); 
            manualArea.slideUp();
            sNum.removeAttr('required');
            tId.removeAttr('required');
        } else if(method === 'manual') {
            form.action = "{{ route('front.storelandData') }}";
            btn.html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            if(isTermsEnabled) termsArea.slideUp(); 
            
            $('#payment_instruction').html(`দয়া করে আপনার টোটাল বিল <b>${number} (${type})</b> নাম্বারে Send Money করুন। এরপর নিচের তথ্যগুলো দিন।`);
            manualArea.slideDown();
            sNum.attr('required', 'required');
            tId.attr('required', 'required');
        } else {
            form.action = "{{ route('front.storelandData') }}";
            btn.html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            if(isTermsEnabled) termsArea.slideUp(); 
            manualArea.slideUp();
            sNum.removeAttr('required');
            tId.removeAttr('required');
        }
    };

    function startTimer(duration, display) {
        var timer = duration, seconds;
        clearInterval(timerInterval);
        $('#resendOtpBtn').prop('disabled', true).addClass('text-muted').removeClass('text-primary');
        timerInterval = setInterval(function () {
            seconds = parseInt(timer % 60, 10);
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.html("Wait (" + seconds + "s)");
            if (--timer < 0) {
                clearInterval(timerInterval);
                display.html("Resend Code");
                $('#resendOtpBtn').prop('disabled', false).removeClass('text-muted').addClass('text-primary');
            }
        }, 1000);
    }

    window.sendOtpBeforeSubmit = function(isResend = false) {
        if(isSendingOtp) return;
        var mobile = $('#mobile').val();
        if(mobile.length !== 11) { toastr.error('সঠিক মোবাইল নাম্বার দিন'); return; }
          
        isSendingOtp = true;
        $('#submit_btn').addClass('is-disabled').text('Sending OTP...');
          
        $.ajax({
            url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
            success: function(res) {
                isSendingOtp = false;
                $('#submit_btn').removeClass('is-disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                if(res.success) {
                    $('#otp_sent_number').text(mobile);
                    var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                    myModal.show();
                    setTimeout(function() { $('#otp_input').focus(); }, 500);
                    startTimer(30, $('#resendOtpBtn'));
                } else { toastr.error(res.msg); }
            },
            error: function() { 
                isSendingOtp = false; 
                $('#submit_btn').removeClass('is-disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
            }
        });
    }

    window.verifyOtpNow = function() {
        var code = $('#otp_input').val();
        var mobile = $('#mobile').val();
        $.ajax({
            url: "{{ route('verifyOtp') }}", type: "POST", data: { otp: code, mobile: mobile, _token: "{{ csrf_token() }}" },
            success: function(res) {
                if(res.success) {
                    isOtpVerified = true;
                    bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
                    toastr.success('ভেরিফিকেশন সফল!');
                    submitOrderFinal(); 
                } else { $('#otp_error').text(res.msg); }
            }
        });
    }

    $('#checkout_land_form').submit(function(e) {
        e.preventDefault();
        
        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 

        if((paymentMethod === 'online' || paymentMethod === 'sslcommerz' || paymentMethod === 'bkash' || paymentMethod === 'eps' || paymentMethod === 'nagad' || paymentMethod === 'uddoktapay') && isTermsEnabled) {
            if(!$('#agree_terms').is(':checked')) {
                $('#terms_error').removeClass('d-none');
                return false;
            } else {
                $('#terms_error').addClass('d-none');
            }
        }

        if(paymentMethod !== 'online' && paymentMethod !== 'Cash on Delivery' && paymentMethod !== 'cod' && paymentMethod !== 'sslcommerz' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay') {
            if(!$('#sender_number').val() || !$('#transaction_id').val()) {
                toastr.warning('দয়া করে পেমেন্ট নাম্বার এবং Transaction ID দিন');
                return false; 
            }
        }

        if (paymentMethod === 'sslcommerz' || paymentMethod === 'online') {
            if(otpSystemEnabled == 1 && !isOtpVerified) {
                sendOtpBeforeSubmit();
            } else {
                this.action = "{{ url('/pay') }}";
                this.submit();
            }
            return;
        }

        if(otpSystemEnabled == 1 && !isOtpVerified) {
            sendOtpBeforeSubmit();
        } else {
            submitOrderFinal();
        }
    });

    function submitOrderFinal() {
        let $form = $('#checkout_land_form');
        let paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod'; 
        
        var purchaseEventId = "PUR_{{ $productId }}_" + Date.now();
        $('#purchase_event_id').val(purchaseEventId);

        $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');

        var total = parseFloat($('#final_total_input').val()) || 0;
        var ship_charge = parseFloat($('#display_delivery').text()) || 0;

        let fbItems = [{
            item_id: "{{ $productId }}",
            item_name: @json($productName),
            price: $('#subtotal_input').val(),
            quantity: $('#form_qty').val()
        }];

        $.ajax({
            url: "{{ route('front.storelandData') }}", 
            method: "POST", 
            data: $form.serialize(),
            success: function(res){
                if(res.success){ 
                    if(paymentMethod === 'bkash') {
                        dynamicOrderId = res.order_id || res.url.split('/').pop();
                        successUrl = res.url;
                        @if(isset($information->bkash_active) && $information->bkash_active == 1)
                            initBkash();
                            setTimeout(() => { $('#bKash_button').click(); }, 300);
                        @endif
                    } else if(paymentMethod === 'nagad') {
                        let finalOrderId = res.order_id || res.url.split('/').pop();
                        window.location.href = "{{ url('nagad/pay') }}/" + finalOrderId;
                    } else if(paymentMethod === 'uddoktapay') {
                        let finalOrderId = res.order_id || res.url.split('/').pop();
                        window.location.href = "{{ url('uddoktapay/pay') }}/" + finalOrderId;
                    } else if(paymentMethod === 'eps') {
                        window.location.href = res.url;
                    } else {
                        toastr.success(res.msg); 
                        setTimeout(function(){ window.location.href = res.url; }, 800);
                    }
                } else { 
                    toastr.error(res.msg); 
                    $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>'); 
                }
            },
            error: function () { 
                $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>'); 
                toastr.error('সার্ভারে সমস্যা হচ্ছে।'); 
            }
        });
    }

    @if(isset($information->bkash_active) && $information->bkash_active == 1)
    function initBkash() {
        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: { "amount": "0", "intent": "sale" },
            createRequest: function (request) {
                $.ajax({
                    url: "{{ route('bkash.create') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", order_id: dynamicOrderId },
                    success: function (data) {
                        if (data && data.paymentID != null) {
                            paymentID = data.paymentID;
                            bKash.create().onSuccess(data);
                        } else {
                            bKash.create().onError();
                            toastr.error("Payment Error: " + (data.errorMessage || "Something went wrong"));
                            $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                        }
                    },
                    error: function (err) {
                        bKash.create().onError();
                        toastr.error("Server error while connecting to bKash.");
                        $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                    }
                });
            },
            executeRequestOnAuthorization: function () {
                $.ajax({
                    url: "{{ route('bkash.execute') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", paymentID: paymentID },
                    success: function (data) {
                        if (data && data.paymentID != null && data.transactionStatus === 'Completed') {
                            window.location.href = successUrl;
                        } else {
                            bKash.execute().onError();
                            toastr.error("Payment Failed! " + (data.errorMessage || ""));
                            $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                        toastr.error("Failed to execute bKash payment.");
                        $('#submit_btn').removeClass('is-disabled').removeAttr('disabled').html('{{ $ln_pg->btn_text_form ?? "অর্ডার কনফার্ম করুন" }} <i class="fas fa-arrow-right ms-1"></i>');
                    }
                });
            },
            onClose: function () {
                window.location.href = successUrl;
            }
        });
    }
    @endif
    
    $(document).ready(function(){
        var selectedPaymentInit = $('input[name="payment_method"]:checked');
        if(selectedPaymentInit.length > 0){ 
            var initialMethod = selectedPaymentInit.val();
            if(initialMethod !== 'cod' && initialMethod !== 'sslcommerz' && initialMethod !== 'bkash' && initialMethod !== 'eps' && initialMethod !== 'nagad' && initialMethod !== 'uddoktapay') {
                var initialName = selectedPaymentInit.val();
                var initialNumber = selectedPaymentInit.data('number') || '';
                var initialType = selectedPaymentInit.data('type') || '';
                togglePaymentAction('manual', initialName, initialNumber, initialType);
            } else {
                togglePaymentAction(initialMethod);
            }
        }
    });
</script>

</body>
</html>
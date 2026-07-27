@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    :root {
        --primary-color: #556ee6;
        --primary-soft: rgba(85, 110, 230, 0.08);
        --text-dark: #343a40;
        --text-muted: #74788d;
        --bg-body: #f8f8fb;
        --card-bg: #ffffff;
        --border-radius: 16px;
        --transition: all 0.3s ease-in-out;
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
        --shadow-md: 0 8px 20px rgba(18, 38, 63, 0.06);
    }

    .page-title-box h4 {
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    .accordion-item {
        border: 1px solid rgba(0,0,0,0.03);
        border-radius: var(--border-radius);
        background: var(--card-bg);
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: var(--transition);
    }

    .accordion-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .accordion-button {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        background-color: var(--card-bg);
        padding: 1.5rem;
        border: none;
        box-shadow: none !important;
        display: flex;
        align-items: center;
    }

    .header-icon {
        width: 45px;
        height: 45px;
        background: var(--bg-body);
        color: var(--primary-color);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18px;
        font-size: 20px;
        transition: var(--transition);
    }

    .accordion-button::after { display: none; }

    .custom-chevron {
        margin-left: auto;
        font-size: 1.5rem;
        color: #adb5bd;
        transition: transform 0.3s;
    }

    .accordion-button:not(.collapsed) {
        background-color: #fff;
        color: var(--primary-color);
    }

    .accordion-button:not(.collapsed) .header-icon {
        background: var(--primary-color);
        color: #fff;
        box-shadow: 0 4px 10px rgba(85, 110, 230, 0.3);
    }

    .accordion-button:not(.collapsed) .custom-chevron {
        transform: rotate(180deg);
        color: var(--primary-color);
    }

    .accordion-item:has(.accordion-button:not(.collapsed)) {
        border-left: 4px solid var(--primary-color);
    }

    .accordion-body {
        padding: 0 1.5rem 2rem 1.5rem;
        background-color: #fff;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.88rem;
        margin-bottom: 0.6rem;
    }
    
    .form-control, .form-select {
        border: 1px solid #e2e5e8;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: var(--transition);
        background-color: #fdfdfd;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 4px var(--primary-soft);
    }

    .preview-box {
        width: 100%;
        height: 100px;
        border: 2px dashed #ced4da;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        margin-top: 10px;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
    }
    .preview-box:hover {
        border-color: var(--primary-color);
        background: var(--primary-soft);
    }
    .preview-box img {
        max-height: 80px;
        max-width: 90%;
        object-fit: contain;
    }

    .sticky-actions {
        position: sticky;
        bottom: 25px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 15px 30px;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        border: 1px solid rgba(255,255,255,0.5);
        z-index: 1000;
        transition: transform 0.3s;
    }

    .sticky-actions:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }

    .btn-save {
        background: linear-gradient(135deg, #556ee6, #34c38f);
        border: none;
        color: white;
        padding: 12px 40px;
        border-radius: 30px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(52, 195, 143, 0.4);
        transition: all 0.3s;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 195, 143, 0.6);
        color: white;
    }

    @media (max-width: 768px) {
        .sticky-actions {
            flex-direction: column;
            bottom: 15px;
            padding: 15px;
            border-radius: 20px;
        }
        .btn-save { width: 100%; }
        .sticky-text { display: none; }
    }
</style>
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-0">System Configuration</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
        <p class="text-muted">Manage your website's general settings, analytics, and integrations from one place.</p>
    </div>
</div>

<form action="{{ route('admin.settings.update', [$information->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @if(Session::has('msg'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-decagram fs-3 me-2"></i>
                <div>{{ Session::get('msg') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="accordion" id="settingsAccordion">

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingGeneral">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-tune-vertical"></i></span> 
                    General Information
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseGeneral" class="accordion-collapse collapse" aria-labelledby="headingGeneral" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Site Name</label>
                            <input type="text" class="form-control" name="site_name" value="{{ $information->site_name }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label" for="gemini_api_key" style="color: #6f42c1;">Gemini API Key (AI Note Check)</label>
                            <input type="text" class="form-control border-primary" id="gemini_api_key" name="gemini_api_key" value="{{ $information->gemini_api_key ?? '' }}" placeholder="Enter Google Gemini API Key">
                            <small class="text-muted">Used to automatically verify worker notes on order status updates.</small>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Site Logo</label>
                            <small class="text-muted d-block mb-2">Recommended Size: 180px x 50px</small>
                            <input type="file" id="site_logo" class="form-control" name="site_logo" accept="image/*">
                            <div class="preview-box">
                                <img src="{{ asset('uploads/img/'.$information->site_logo) }}" id="preview_logo" alt="Logo">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Footer Logo</label>
                             <small class="text-muted d-block mb-2">Recommended Size: 180px x 50px</small>
                            <input type="file" id="footer_logo" class="form-control" name="footer_logo" accept="image/*">
                            <div class="preview-box">
                                <img src="{{ !empty($information->footer_logo) ? asset('uploads/img/'.$information->footer_logo) : asset('uploads/img/'.$information->site_logo) }}" id="preview_footer_logo">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Favicon</label>
                             <small class="text-muted d-block mb-2">Recommended Size: 32px x 32px (Square)</small>
                            <input type="file" id="fav_icon" class="form-control" name="fav_icon" accept="image/*">
                            <div class="preview-box">
                                <img src="{{ asset('uploads/img/'.$information->fav_icon) }}" id="preview_favicon">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Topbar Announcement</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="topbarSwitch" name="topbar_active" value="1" {{ $information->topbar_active == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="topbarSwitch">Show/Hide</label>
                                </div>
                            </div>
                            <textarea class="form-control" name="topbar_notice" rows="2">{{ $information->topbar_notice }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingContact">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContact" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-card-account-details-outline"></i></span> 
                    Contact & Social Media
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseContact" class="accordion-collapse collapse" aria-labelledby="headingContact" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Support Phone</label>
                            <input type="tel" class="form-control" name="owner_phone" value="{{ $information->owner_phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Support Email</label>
                            <input type="email" class="form-control" name="owner_email" value="{{ $information->owner_email }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Office Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ $information->address }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Copyright Text</label>
                            <input type="text" class="form-control" name="copyright" value="{{ $information->copyright }}">
                        </div>
                        
                        <div class="col-12 mt-4"><h6 class="text-primary fw-bold">Social Media Links</h6></div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-facebook text-primary"></i></span>
                                <input type="url" class="form-control" name="facebook" placeholder="Facebook URL" value="{{ $information->facebook }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-instagram text-danger"></i></span>
                                <input type="url" class="form-control" name="instagram" placeholder="Instagram URL" value="{{ $information->instagram }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-youtube text-danger"></i></span>
                                <input type="url" class="form-control" name="youtube" placeholder="YouTube URL" value="{{ $information->youtube }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-twitter text-info"></i></span>
                                <input type="url" class="form-control" name="twitter" placeholder="Twitter URL" value="{{ $information->twitter }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-music-note-eighth text-dark"></i></span>
                                <input type="url" class="form-control" name="tiktok" placeholder="TikTok URL" value="{{ $information->tiktok }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item" style="border-color: #20c997;">
            <h2 class="accordion-header" id="headingAnalytics">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnalytics" aria-expanded="false">
                    <span class="header-icon" style="background: rgba(32, 201, 151, 0.1); color: #20c997;"><i class="mdi mdi-google-analytics"></i></span> 
                    Tracking & Analytics
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseAnalytics" class="accordion-collapse collapse" aria-labelledby="headingAnalytics" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label">GTM (Google Tag Manager) & Header Scripts</label>
                            <textarea class="form-control font-monospace" name="tracking_code" rows="4" style="font-size: 13px;" placeholder="Paste GTM Head code or other scripts here...">{{ $information->tracking_code }}</textarea>
                            <small class="text-muted">এই কোডটি সরাসরি ওয়েবসাইটের <code>&lt;head&gt;</code> সেকশনে বসবে।</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">GA4 Measurement ID</label>
                            <input type="text" class="form-control" name="ga4_id" value="{{ $information->ga4_id }}" placeholder="e.g., G-XXXXXXXXXX">
                            <small class="text-danger fw-bold d-block mt-1">নোট: আপনি যদি GTM দিয়ে GA4 সেটআপ করে থাকেন, তবে ডাবল-ট্র্যাকিং এড়াতে এই ঘরটি ফাঁকা রাখুন।</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Microsoft Clarity Project ID</label>
                            <input type="text" class="form-control" name="clarity_id" value="{{ $information->clarity_id }}" placeholder="e.g., abcdef1234">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item" style="border-color: #0d6efd;">
            <h2 class="accordion-header" id="headingPixel">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePixel" aria-expanded="false">
                    <span class="header-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;"><i class="mdi mdi-facebook"></i></span> 
                    Meta (Facebook) Pixel
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapsePixel" class="accordion-collapse collapse" aria-labelledby="headingPixel" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Pixel ID</label>
                            <input type="text" class="form-control" name="fb_pixel_id" value="{{ $information->fb_pixel_id }}">
                            <small class="text-danger fw-bold d-block mt-1">নোট: আপনি যদি GTM দিয়ে Facebook Pixel সেটআপ করে থাকেন, তবে ডাবল-ট্র্যাকিং এড়াতে এই ঘরটি ফাঁকা রাখুন।</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Test Event Code</label>
                            <input type="text" class="form-control" name="fb_pixel_test_code" value="{{ $information->fb_pixel_test_code }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Conversion API Access Token</label>
                            <textarea class="form-control" name="fb_access_token" rows="3">{{ $information->fb_access_token }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item" style="border-color: #000;">
            <h2 class="accordion-header" id="headingTiktok">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTiktok" aria-expanded="false">
                    <span class="header-icon" style="background: rgba(0, 0, 0, 0.08); color: #000;"><i class="mdi mdi-music-note-eighth"></i></span>
                    TikTok Pixel (Events API)
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseTiktok" class="accordion-collapse collapse" aria-labelledby="headingTiktok" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Pixel ID</label>
                            <input type="text" class="form-control" name="tt_pixel_id" value="{{ $information->tt_pixel_id ?? '' }}">
                            <small class="text-danger fw-bold d-block mt-1">নোট: আপনি যদি GTM দিয়ে TikTok Pixel সেটআপ করে থাকেন, তবে ডাবল-ট্র্যাকিং এড়াতে এই ঘরটি ফাঁকা রাখুন।</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Test Event Code</label>
                            <input type="text" class="form-control" name="tt_test_event_code" value="{{ $information->tt_test_event_code ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Events API Access Token</label>
                            <textarea class="form-control" name="tt_access_token" rows="3">{{ $information->tt_access_token ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSecurity">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-shield-lock-outline"></i></span>
                    Security & Fraud Check API
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseSecurity" class="accordion-collapse collapse" aria-labelledby="headingSecurity" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="tel" class="form-control" name="whats_num" value="{{ $information->whats_num }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp Order Feature</label>
                            <select class="form-select" name="whats_active">
                                <option value="1" {{ $information->whats_active == '1' ? 'selected':'' }}>Enabled</option>
                                <option value="0" {{ $information->whats_active == '0' ? 'selected':'' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hotline 2</label>
                            <input type="tel" class="form-control" name="supp_num1" value="{{ $information->supp_num1 }}">
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Fraud Check API Key</label>
                                <a href="https://www.hoorin.com/" target="_blank" class="btn btn-sm btn-soft-primary rounded-pill">
                                    <i class="mdi mdi-web me-1"></i> Visit Hoorin.com
                                </a>
                                </div>
                            <input type="text" class="form-control font-monospace" name="fraudApi" value="{{ $information->fraudApi }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOrder">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-gavel"></i></span> 
                    Order Guard (Rules)
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseOrder" class="accordion-collapse collapse" aria-labelledby="headingOrder" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">IP Check</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_ip_check" value="1" {{ $information->is_ip_check == 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <small class="text-muted">Block multiple orders from same IP Address.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Phone Check</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_mobile_check" value="1" {{ $information->is_mobile_check == 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <small class="text-muted">Block duplicate orders from same Phone Number.</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Order Frequency Limit (Minutes)</label>
                            <input type="number" class="form-control w-50" name="time_limit" value="{{ $information->time_limit }}">
                            <small class="text-muted">Time to wait before placing another order.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item" style="border-color: #f1b44c;">
            <h2 class="accordion-header" id="headingInventory">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInventory" aria-expanded="false">
                    <span class="header-icon" style="background: #fff4e5; color: #f1b44c;">
                        <i class="mdi mdi-store-alert"></i>
                    </span> 
                    Inventory & Order Limits
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseInventory" class="accordion-collapse collapse" aria-labelledby="headingInventory" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row g-4">
                        
                        <div class="col-md-4">
                            <label class="form-label">Stock Warning Limit</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-alert-outline text-danger"></i></span>
                                <input type="number" class="form-control border-start-0 ps-0" name="stock_warning_limit" min="0" value="{{ $information->stock_warning_limit ?? 0 }}" placeholder="Example: 5">
                            </div>
                            <small class="text-muted">কত পিসের নিচে স্টক নামলে ওয়ার্নিং দেখাবে।</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Maximum Order Amount (Tk)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white fw-bold">৳</span>
                                <input type="number" step="0.01" class="form-control border-start-0 ps-0" name="max_order_amount" value="{{ $information->max_order_amount }}" placeholder="Leave empty for NO limit">
                            </div>
                            <small class="text-muted">একসাথে সর্বোচ্চ কত টাকার অর্ডার করা যাবে।</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Maximum Quantity Per Order</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="mdi mdi-shopping-outline text-primary"></i></span>
                                <input type="number" class="form-control border-start-0 ps-0" name="max_order_qty" value="{{ $information->max_order_qty }}" placeholder="Leave empty for NO limit">
                            </div>
                            <small class="text-muted">একটি অর্ডারে সর্বোচ্চ কত পিস প্রোডাক্ট নেওয়া যাবে।</small>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingCourier">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCourier" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-truck-delivery-outline"></i></span> 
                    Courier Integrations
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseCourier" class="accordion-collapse collapse" aria-labelledby="headingCourier" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="card border-0 shadow-none bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-danger mb-3"><i class="mdi mdi-package"></i> RedX Courier</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redx_api_base_url" value="{{ $information->redx_api_base_url }}" placeholder="API Base URL">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redx_api_access_token" value="{{ $information->redx_api_access_token }}" placeholder="Access Token">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1"><i class="mdi mdi-key-variant"></i> Webhook Bearer Token <span class="text-muted">(RedX callback secret)</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="redx_webhook_token" name="redx_webhook_token" value="{{ $information->redx_webhook_token ?? '' }}" placeholder="Your custom webhook secret — same value goes in RedX panel">
                                        <button type="button" class="btn btn-dark webhook-generate-btn" data-target="redx_webhook_token"><i class="mdi mdi-key-plus"></i> Generate</button>
                                        <button type="button" class="btn btn-outline-secondary webhook-copy-btn" data-target="redx_webhook_token" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-none bg-light mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <h6 class="fw-bold text-danger"><i class="mdi mdi-bike-fast"></i> Pathao Courier</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="pathao_status" value="1" {{ $information->pathao_status == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="pathao_api_base_url" value="{{ $information->pathao_api_base_url }}" placeholder="API URL">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="pathao_store_id" value="{{ $information->pathao_store_id }}" placeholder="Store ID">
                                </div>
                                <div class="col-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="pathao_api_access_token" value="{{ $information->pathao_api_access_token }}" placeholder="Access Token">
                                        <a href="{{ route('admin.viewAccessToken') }}" class="btn btn-dark">Generate Token</a>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1"><i class="mdi mdi-key-variant"></i> Webhook Bearer Token <span class="text-muted">(Pathao callback secret)</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="pathao_webhook_token" name="pathao_webhook_token" value="{{ $information->pathao_webhook_token ?? '' }}" placeholder="Your custom webhook secret — same value goes in Pathao panel">
                                        <button type="button" class="btn btn-dark webhook-generate-btn" data-target="pathao_webhook_token"><i class="mdi mdi-key-plus"></i> Generate</button>
                                        <button type="button" class="btn btn-outline-secondary webhook-copy-btn" data-target="pathao_webhook_token" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-none bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-truck-fast"></i> Steadfast Courier</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <input type="text" class="form-control" name="steadfast_api_base_url" value="{{ $information->steadfast_api_base_url }}" placeholder="API URL">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="steadfast_api_key" value="{{ $information->steadfast_api_key }}" placeholder="API Key">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="steadfast_secret_key" value="{{ $information->steadfast_secret_key }}" placeholder="Secret Key">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1"><i class="mdi mdi-key-variant"></i> Webhook Bearer Token <span class="text-muted">(Steadfast callback secret — set the SAME value in Steadfast portal Webhook settings)</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="steadfast_webhook_token" name="steadfast_webhook_token" value="{{ $information->steadfast_webhook_token ?? '' }}" placeholder="e.g. Tk98_xYz_SteadFast_2026">
                                        <button type="button" class="btn btn-dark webhook-generate-btn" data-target="steadfast_webhook_token"><i class="mdi mdi-key-plus"></i> Generate</button>
                                        <button type="button" class="btn btn-outline-secondary webhook-copy-btn" data-target="steadfast_webhook_token" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-none bg-light mt-3 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-warning mb-3"><i class="mdi mdi-bee"></i> Carrybee Courier</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">API Base URL</label>
                                    <input type="text" class="form-control" name="carrybee_api_base_url" value="{{ $information->carrybee_api_base_url ?? '' }}" placeholder="Example: https://api.carrybee.com.bd/api/v1/">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Store ID</label>
                                    <input type="text" class="form-control" name="carrybee_store_id" value="{{ $information->carrybee_store_id ?? '' }}" placeholder="Enter Store ID">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" class="form-control" name="carrybee_client_id" value="{{ $information->carrybee_client_id ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Client Secret</label>
                                    <input type="text" class="form-control" name="carrybee_client_secret" value="{{ $information->carrybee_client_secret ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Client Context</label>
                                    <input type="text" class="form-control" name="carrybee_client_context" value="{{ $information->carrybee_client_context ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">API Token (Optional)</label>
                                    <input type="text" class="form-control" name="carrybee_api_token" value="{{ $information->carrybee_api_token ?? '' }}" placeholder="Leave blank if not required">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1"><i class="mdi mdi-key-variant"></i> Webhook Bearer Token <span class="text-muted">(Carrybee callback secret)</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="carrybee_webhook_token" name="carrybee_webhook_token" value="{{ $information->carrybee_webhook_token ?? '' }}" placeholder="Your custom webhook secret — same value goes in Carrybee panel">
                                        <button type="button" class="btn btn-dark webhook-generate-btn" data-target="carrybee_webhook_token"><i class="mdi mdi-key-plus"></i> Generate</button>
                                        <button type="button" class="btn btn-outline-secondary webhook-copy-btn" data-target="carrybee_webhook_token" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="accordion-item" style="border-color: #6f42c1;">
            <h2 class="accordion-header" id="headingManyDial">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManyDial" aria-expanded="false">
                    <span class="header-icon" style="background: rgba(111, 66, 193, 0.1); color: #6f42c1;"><i class="mdi mdi-phone-in-talk"></i></span> 
                    ManyDial Auto Calling
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseManyDial" class="accordion-collapse collapse" aria-labelledby="headingManyDial" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="alert alert-soft-primary mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>ManyDial Auto Calling Config:</strong> কনফার্মেশন কল অটোমেট করার জন্য API Key ও Caller ID দিন।
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="form-check form-switch me-4">
                                    <input type="hidden" name="manydial_status" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="manydialSwitch" name="manydial_status" value="1" {{ ($information->manydial_status ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="manydialSwitch" style="cursor: pointer;">
                                        {{ ($information->manydial_status ?? 0) == 1 ? 'ON' : 'OFF' }}
                                    </label>
                                </div>
                                <a href="https://manydial.com/" target="_blank" class="btn btn-sm btn-primary text-white fw-bold">
                                    <i class="mdi mdi-web me-1"></i> Visit ManyDial
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ManyDial API Key</label>
                            <input type="text" class="form-control" name="manydial_api_key" value="{{ $information->manydial_api_key ?? '' }}" placeholder="Enter Secret Key">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ManyDial Caller ID (Phone)</label>
                            <input type="text" class="form-control" name="manydial_caller_id" value="{{ $information->manydial_caller_id ?? '' }}" placeholder="+88096XXXXXXXX">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingNotification">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotification" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-bell-ring-outline"></i></span> 
                    Notification, SMS & SMTP Setup
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseNotification" class="accordion-collapse collapse" aria-labelledby="headingNotification" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="form-group row mb-4 p-3 border rounded bg-light">
                        <label class="col-sm-3 col-form-label font-weight-bold text-dark">
                            OTP Verification System
                            <small class="d-block text-muted">চেকআউটের সময় ফোন ভেরিফিকেশন</small>
                        </label>
                        <div class="col-sm-9 d-flex align-items-center">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="otp_system" value="0">
                                <input type="checkbox" class="custom-control-input" id="otpSystemSwitch" name="otp_system" value="1" {{ (isset($information->otp_system) && $information->otp_system == 1) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="otpSystemSwitch" style="cursor: pointer;">
                                    {{ (isset($information->otp_system) && $information->otp_system == 1) ? 'চালু আছে (ON)' : 'বন্ধ আছে (OFF)' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-4 p-3 border rounded bg-light">
                        <label class="col-sm-3 col-form-label font-weight-bold text-dark">
                            Order Notification
                            <small class="d-block text-muted">নতুন অর্ডারের ইমেইল ও SMS অ্যালার্ট</small>
                        </label>
                        <div class="col-sm-9 d-flex align-items-center">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="notification_active" value="0">
                                <input type="checkbox" class="custom-control-input" id="notificationActiveSwitch" name="notification_active" value="1" {{ (isset($information->notification_active) && $information->notification_active == 1) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="notificationActiveSwitch" style="cursor: pointer;">
                                    {{ (isset($information->notification_active) && $information->notification_active == 1) ? 'চালু আছে (ON)' : 'বন্ধ আছে (OFF)' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-soft-primary mb-3">
                        <strong>Admin Notification:</strong> নতুন অর্ডার আসলে এই নাম্বারে SMS ও ইমেইল যাবে।
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Admin Phone (SMS)</label>
                            <input type="text" class="form-control" name="admin_phone" value="{{ $information->admin_phone }}" placeholder="Example: 01700000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Email</label>
                            <input type="text" class="form-control" name="admin_email" value="{{ $information->admin_email }}" placeholder="admin@example.com">
                        </div>
                    </div>

                    <div class="alert alert-soft-warning mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>BulkSMSBD Config:</strong> SMS পাঠানোর জন্য API Key ও Sender ID দিন।
                            </div>
                            <a href="https://bulksmsbd.com/" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold">
                                <i class="mdi mdi-web me-1"></i> Visit BulkSMSBD.com
                            </a>
                            </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">API Key</label>
                            <input type="text" class="form-control" name="sms_api_key" value="{{ $information->sms_api_key }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sender ID</label>
                            <input type="text" class="form-control" name="sms_sender_id" value="{{ $information->sms_sender_id }}">
                        </div>
                    </div>

                    <div class="alert alert-soft-info mb-3">
                        <strong>General SMS Message Templates:</strong> আপনি চাইলে মেসেজ কাস্টমাইজ করতে পারেন। 
                        <br><small>Placeholder ব্যবহার করুন: <code>{order_id}</code>, <code>{amount}</code>, <code>{status}</code></small>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">New Order SMS (To Admin)</label>
                            <textarea class="form-control" name="sms_new_order_admin" rows="2" placeholder="New Order Received! ID: #{order_id}, Amount: {amount} Tk.">{{ $information->sms_new_order_admin }}</textarea>
                            <div class="form-text">Available: <code>{order_id}</code>, <code>{amount}</code></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Default Order Status SMS (Fallback)</label>
                            <textarea class="form-control" name="sms_status_update" rows="2" placeholder="Dear Customer, your order #{order_id} is now {status}.">{{ $information->sms_status_update }}</textarea>
                            <div class="form-text">Used if no specific status template is set. Available: <code>{order_id}</code>, <code>{status}</code></div>
                        </div>
                    </div>

                    <div class="alert alert-soft-secondary mb-3 mt-4">
                        <strong>Status-Specific SMS Templates (To Customer):</strong> 
                        <br><small>প্রতিটি স্ট্যাটাসের জন্য আলাদা মেসেজ সেট করুন এবং ডানপাশের সুইচ অন/অফ করে SMS পাঠানো কন্ট্রোল করুন。</small>
                    </div>

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-warning mb-0">Pending Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_pending_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_pending_active" value="1" {{ ($information->sms_pending_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_pending" rows="2" placeholder="Order #{order_id} is Pending.">{{ $information->sms_pending }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-secondary mb-0">Incomplete Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_incomplete_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_incomplete_active" value="1" {{ ($information->sms_incomplete_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_incomplete" rows="2" placeholder="Order #{order_id} is Incomplete.">{{ $information->sms_incomplete ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0" style="color: #20c997;">Confirmed Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_confirmed_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_confirmed_active" value="1" {{ ($information->sms_confirmed_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_confirmed" rows="2" placeholder="Order #{order_id} is Confirmed.">{{ $information->sms_confirmed ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-info mb-0">Processing Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_processing_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_processing_active" value="1" {{ ($information->sms_processing_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_processing" rows="2" placeholder="Order #{order_id} is Processing.">{{ $information->sms_processing }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-secondary mb-0">On Hold Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_on_hold_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_on_hold_active" value="1" {{ ($information->sms_on_hold_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_on_hold" rows="2" placeholder="Order #{order_id} On Hold.">{{ $information->sms_on_hold }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-info mb-0">Scheduled Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_scheduled_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_scheduled_active" value="1" {{ ($information->sms_scheduled_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_scheduled" rows="2" placeholder="Order #{order_id} has been Scheduled.">{{ $information->sms_scheduled ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-primary mb-0">Courier / Shipped Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_courier_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_courier_active" value="1" {{ ($information->sms_courier_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_courier" rows="2" placeholder="Order #{order_id} is with Courier.">{{ $information->sms_courier }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-primary mb-0">Courier Complete Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_courier_complete_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_courier_complete_active" value="1" {{ ($information->sms_courier_complete_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_courier_complete" rows="2" placeholder="Order #{order_id} is now Courier Complete.">{{ $information->sms_courier_complete ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-success mb-0">Delivered Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_delivered_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_delivered_active" value="1" {{ ($information->sms_delivered_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_delivered" rows="2" placeholder="Order #{order_id} is Delivered.">{{ $information->sms_delivered ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-success mb-0">Completed Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_complete_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_complete_active" value="1" {{ ($information->sms_complete_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_complete" rows="2" placeholder="Order #{order_id} Completed. Thanks!">{{ $information->sms_complete }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-danger mb-0">Cancelled Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_cancell_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_cancell_active" value="1" {{ ($information->sms_cancell_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_cancell" rows="2" placeholder="Order #{order_id} Cancelled.">{{ $information->sms_cancell }}</textarea>
                        </div>
                    </div>

                    <div class="alert alert-soft-success mb-3 mt-4">
                        <strong>SMTP Settings:</strong> ইমেইল পাঠানোর কনফিগারেশন।
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mail Host</label>
                            <input type="text" class="form-control" name="smtp_host" value="{{ $information->smtp_host }}" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mail Port</label>
                            <input type="text" class="form-control" name="smtp_port" value="{{ $information->smtp_port }}" placeholder="587">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mail Username</label>
                            <input type="text" class="form-control" name="smtp_user" value="{{ $information->smtp_user }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mail Password</label>
                            <input type="password" class="form-control" name="smtp_pass" value="{{ $information->smtp_pass }}">
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="accordion-item" style="border-color: #fd7e14;">
            <h2 class="accordion-header" id="headingReturn">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReturn" aria-expanded="false">
                    <span class="header-icon" style="background: rgba(253, 126, 20, 0.1); color: #fd7e14;"><i class="mdi mdi-keyboard-return"></i></span> 
                    Return & Missing SMS
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseReturn" class="accordion-collapse collapse" aria-labelledby="headingReturn" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="alert alert-soft-warning mb-3 mt-2">
                        <strong>Return & Missing Status SMS Templates:</strong> 
                        <br><small>রিটার্ন ও মিসিং পার্সেলের জন্য কাস্টমারকে SMS পাঠানোর সেটিং। Placeholder ব্যবহার করুন: <code>{order_id}</code></small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0" style="color: #fd7e14;">Returning Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_returning_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_returning_active" value="1" {{ ($information->sms_returning_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_returning" rows="2" placeholder="Your parcel for Order #{order_id} is returning.">{{ $information->sms_returning ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-dark mb-0">Return Received Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_return_received_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_return_received_active" value="1" {{ ($information->sms_return_received_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_return_received" rows="2" placeholder="We have received the returned parcel.">{{ $information->sms_return_received ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0" style="color: #6f42c1;">Return Missing Status SMS</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="sms_return_missing_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="sms_return_missing_active" value="1" {{ ($information->sms_return_missing_active ?? 0) == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <textarea class="form-control" name="sms_return_missing" rows="2" placeholder="Return parcel for Order #{order_id} is missing.">{{ $information->sms_return_missing ?? '' }}</textarea>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="sticky-actions">
        <div class="sticky-text text-muted">
            <i class="mdi mdi-information-outline"></i> Ensure all API keys are valid before saving.
        </div>
        <button type="submit" class="btn-save">
            Save Configuration
        </button>
    </div>

</form>

@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    function setupPreview(inputId, imgId) {
        document.getElementById(inputId).addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) { document.getElementById(imgId).src = e.target.result; }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupPreview('site_logo', 'preview_logo');
        setupPreview('footer_logo', 'preview_footer_logo');
        setupPreview('fav_icon', 'preview_favicon');
    });

    function generateWebhookToken(len) {
        len = len || 32;
        var arr = new Uint8Array(len);
        window.crypto.getRandomValues(arr);
        return Array.from(arr, function(b) { return ('0' + (b & 0xff).toString(16)).slice(-2); }).join('');
    }

    document.querySelectorAll('.webhook-generate-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = btn.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (input) {
                input.value = generateWebhookToken(32);
            }
        });
    });

    document.querySelectorAll('.webhook-copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = btn.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (input && input.value) {
                navigator.clipboard.writeText(input.value).then(function() {
                    btn.innerHTML = '<i class="mdi mdi-check"></i>';
                    setTimeout(function() { btn.innerHTML = '<i class="mdi mdi-content-copy"></i>'; }, 1500);
                });
            }
        });
    });
</script>
@endpush
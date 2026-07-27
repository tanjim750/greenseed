@extends('backend.app')
@section('content')

<style>
    /* =========================================
       🚀 THEME DETACHED LAYOUT GAP FIX (ফাঁকা জায়গা কমানোর জন্য)
       ========================================= */
    body[data-layout="detached"] .container-fluid,
    body[data-layout="detached"] .wrapper,
    .content-page, 
    .content, 
    .main-content {
        padding-left: 5px !important;
        padding-right: 5px !important;
        max-width: 100% !important;
    }

    @media (max-width: 768px) {
        body[data-layout="detached"] .container-fluid,
        body[data-layout="detached"] .wrapper,
        .content-page, 
        .content, 
        .main-content {
            padding-left: 2px !important;
            padding-right: 2px !important;
        }
    }

    /* =========================================
       🚀 PREMIUM 2-COLUMN UI + FULL DATA TABLE
       ========================================= */
    :root {
        --bg-body: #f8fafc; --bg-card: #ffffff; --text-main: #1e293b;
        --text-muted: #64748b; --border-color: #e2e8f0; --primary: #2563eb;
        --danger: #ef4444; --success: #10b981; --radius: 12px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }
    
    .row {
        margin-left: -5px !important;
        margin-right: -5px !important;
    }

    .col-12, .col-lg-7, .col-lg-5 {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }

    body { background-color: var(--bg-body); font-size: 13px; color: var(--text-main); }

    /* 🚀 ADMIN THEME FIX FOR STICKY POSITION */
    body, .wrapper, .content-page, .content, .main-content {
        overflow-y: visible !important;
        overflow-x: clip !important;
    }

    .page-title-box { padding: 15px 0; display: flex; align-items: center; justify-content: space-between; }
    .page-title { font-weight: 800; font-size: 20px; color: var(--text-main); margin: 0; }
    .card { border: 1px solid var(--border-color); border-radius: var(--radius); box-shadow: var(--shadow-sm); background: var(--bg-card); margin-bottom: 20px; }
    .card-body { padding: 15px 20px; }

    /* =========================================
       🌟 STICKY ACTION BAR (COMPACT FOR LAPTOP)
       ========================================= */
    .action-bar-wrapper {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 70px !important; 
        z-index: 1040 !important;
        background: var(--bg-body);
        padding-bottom: 12px;
    }
    
    .action-bar { 
        display: flex; 
        flex-wrap: wrap; 
        gap: 6px; 
        align-items: stretch; 
        background: var(--bg-card);
        padding: 8px 12px;
        border-radius: var(--radius);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: 1px solid var(--border-color);
        width: 100%;
    }

    /* 🔥 ACTION BUTTON BORDER FIX (LIKE STATUS CHIPS) 🔥 */
    .action-btn {
        border: 1px solid var(--border-color) !important;
    }

    .btn { 
        border-radius: 6px; 
        font-weight: 700; 
        font-size: 13px; 
        padding: 4px 10px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        gap: 4px; 
        box-shadow: var(--shadow-sm); 
        white-space: nowrap; 
        transition: 0.2s; 
    }
    .btn:hover { transform: translateY(-1px); }

    /* =========================================
       📊 PREMIUM DASHBOARD STATUS WIDGETS
       ========================================= */
    .status-dashboard { margin-bottom: 12px; }
    
    .status-chip {
        cursor: pointer; 
        padding: 6px; 
        background: #fff;
        border: 1px solid var(--border-color); 
        border-radius: 6px; 
        display: flex; align-items: center; justify-content: space-between;
        gap: 4px; 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        box-sizing: border-box !important;
    }
    
    .status-chip input { display: none; }
    .status-chip .chip-icon { font-size: 16px !important; flex-shrink: 0; } 
    .status-chip .chip-text { 
        font-weight: 700; 
        font-size: 12px; 
        color: #475569; 
        transition: 0.2s; 
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    } 
    .status-chip .count-badge { 
        background: #f1f5f9; color: #334155; padding: 2px 5px; border-radius: 4px; 
        font-size: 10px; font-weight: 800; transition: 0.2s; flex-shrink: 0;
    }
    
    .status-chip:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,0.04); border-color: #cbd5e1; }
    .status-chip.is-active { background: linear-gradient(135deg, var(--primary), #3b82f6); border-color: transparent; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2); transform: translateY(-1px); }
    .status-chip.is-active .chip-text, .status-chip.is-active .chip-icon { color: #ffffff !important; }
    .status-chip.is-active .count-badge { background: rgba(255, 255, 255, 0.25); color: #ffffff; }

    /* Search & Date Filter Bar */
    .search-filter-bar { background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 12px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
    .search-filter-bar .form-control { border-radius: 8px; border: 1px solid #cbd5e1; padding: 6px 12px; font-size: 12px; }
    .search-filter-bar .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }

    /* TWO COLUMN LAYOUT */
    .dashboard-grid { display: flex; gap: 20px; align-items: flex-start; }
    .main-list-area { flex: 1; min-width: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 15px; overflow: hidden; }
    .table-responsive { overflow-x: auto; width: 100%; }
    .table-responsive::-webkit-scrollbar { height: 8px; }
    .table-responsive::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

    /* 📊 ULTRA-PREMIUM FIXED RIGHT PANEL */
    .right-details-panel { 
        position: fixed !important; top: 0 !important; right: -500px !important; 
        width: 100% !important; max-width: 420px !important; height: 100vh !important; 
        z-index: 999999 !important; background: #fff !important; 
        box-shadow: -15px 0 50px rgba(0,0,0,0.15) !important; 
        transition: right 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important; 
        display: none !important; flex-direction: column !important; padding: 0 !important;
        border-radius: 25px 0 0 25px !important; border-left: 1px solid var(--border-color) !important;
    }
    .right-details-panel.show { right: 0 !important; display: flex !important; }
    .drawer-header { padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; border-radius: 25px 0 0 0; }
    .drawer-body { padding: 15px 20px; overflow-y: auto; flex: 1; scrollbar-width: none; -ms-overflow-style: none; }
    .drawer-body::-webkit-scrollbar { display: none; }
    .panel-backdrop { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 999998 !important; display: none; opacity: 0; transition: 0.3s; }
    .panel-backdrop.show { display: block; opacity: 1; }

    /* Table Styling */
    .order-table { width: 100%; border-collapse: collapse; min-width: 950px; }
    .order-table thead th { background: #f8fafc; color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700; padding: 10px 8px; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    .order-table tbody tr { background: #fff; transition: 0.2s; cursor: pointer; border-bottom: 1px solid var(--border-color); }
    .order-table tbody tr:hover { background: #f1f5f9; }
    .order-table tbody tr.active-row { background: #eff6ff; border-left: 3px solid var(--primary); }
    .order-table tbody td { padding: 10px 8px; vertical-align: middle; white-space: normal; font-size: 12px; }

    .cell-stack { display: flex; flex-direction: column; gap: 2px; }
    .line-strong { font-weight: 700; color: var(--text-main); font-size: 12px; }
    .line-muted { color: var(--text-muted); font-size: 11px; font-weight: 500; }
    .badge-soft { padding: 4px 8px; border-radius: 20px; font-weight: 700; font-size: 10px; text-transform: uppercase; display: inline-block; text-align: center; }
    .action-icon { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; background: #f8fafc; color: var(--text-muted); border: 1px solid var(--border-color); transition: 0.2s; cursor: pointer; }
    .action-icon i { font-size: 15px; }
    .action-icon:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .product-tag { background: #f1f5f9; padding: 3px 6px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 600; display: inline-block; margin-bottom: 2px; }

    /* Bulk Bar */
    .bulk-bar { 
        position: fixed; left: 50%; bottom: 20px; transform: translateX(-50%); 
        z-index: 9999; display: none; gap: 10px; padding: 10px 20px; 
        background: #ffffff; border: 2px solid #1e293b; border-radius: 50px; 
        color: #1e293b; align-items: center; box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
    }
    .bulk-bar .count { font-weight: 800; font-size: 14px; }

    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    /* 🚀 FRAUD CHECK MODAL Z-INDEX FIX */
    .modal { z-index: 999999 !important; }
    .modal-backdrop { z-index: 999998 !important; }

    /* =========================================
       💻 DESKTOP FIXES (7 Column Grid - OVERLAP FIX)
       ========================================= */
    @media (min-width: 993px) {
        .action-bar > a.btn, 
        .action-bar > button.btn, 
        .action-bar > div.d-flex {
            flex: 1 1 0 !important; 
            justify-content: center !important;
            padding: 5px 2px !important;
            min-width: 0 !important;
            overflow: hidden;
        }

        .status-chip-row {
            display: grid !important;
            width: 100% !important;
            grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
            gap: 6px !important;
        }
        .status-chip {
            max-width: 100% !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
        }
    }

    /* =========================================
       💻 LAPTOP VIEW FIX (100% ZOOM ADJUSTMENT)
       ========================================= */
    @media (max-width: 1400px) {
        .status-chip { padding: 4px 5px !important; }
        .status-chip .chip-text { font-size: 11px !important; }
        .status-chip .chip-icon { font-size: 14px !important; }
        
        .action-bar .btn { padding: 4px 6px; font-size: 11px; }
        .action-bar .btn i { font-size: 14px; }
        
        .order-table tbody td { padding: 6px 4px; font-size: 11px; }
        .cell-stack .line-strong { font-size: 11px; }
    }

    /* =========================================
       🚀 RESPONSIVE FIXES (MOBILE & TABLET)
       ========================================= */
    @media (max-width: 992px) { 
        .dashboard-grid { flex-direction: column; } 
        .main-list-area { width: 100%; overflow: hidden; }
        .action-bar > a.btn, .action-bar > button.btn {
            flex: 0 0 auto !important;
        }
    }
    
    @media (max-width: 768px) {
        .action-bar-wrapper {
            position: relative !important; 
            top: auto !important;
            z-index: 99 !important; 
            padding-bottom: 5px !important;
        }

        .bulk-bar { z-index: 1020 !important; }

        .right-details-panel { max-width: 100% !important; right: -100% !important; border-radius: 0 !important; }
        .right-details-panel.show { right: 0 !important; }
        
        .action-bar { flex-wrap: nowrap !important; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 12px; }
        
        .status-chip-row { 
            display: flex !important;
            flex-wrap: nowrap !important; 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch; 
            padding-bottom: 8px; 
            gap: 10px; 
        }
        .status-chip { 
            flex: 0 0 auto !important; 
            width: max-content !important; 
            max-width: none !important; 
            padding: 8px 12px !important; 
        }
        .status-chip .chip-text { font-size: 12px; }

        .table-responsive { width: 100%; overflow-x: auto; }
        .order-table { min-width: 900px; }
        .bulk-bar { width: 95%; justify-content: center; flex-wrap: wrap; text-align: center; }

        .search-filter-bar .row { flex-direction: column !important; align-items: stretch !important; gap: 12px !important; }
        .search-filter-bar .col-lg-5, .search-filter-bar .col-lg-7 { width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .search-filter-bar .d-flex.flex-wrap { width: 100% !important; justify-content: center !important; }

        .search-filter-bar .bg-light.rounded-3 { 
            width: 100% !important; 
            justify-content: center !important; 
            padding: 6px !important; 
            gap: 5px !important;
            flex-wrap: nowrap !important;
        }
        .search-filter-bar input[type="date"] { 
            width: 46% !important; 
            flex: none !important; 
            font-size: 11px !important; 
            padding: 4px !important; 
            text-align: center; 
        }

        a i, button i, label i, .action-icon i { pointer-events: none !important; }
        a, button, .action-icon, .status-chip { cursor: pointer !important; }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Order Management</h4>
        </div>
    </div>
</div>

{{-- 🌟 STICKY ACTIONS BAR 🌟 --}}
<div class="action-bar-wrapper">
    <div class="action-bar">
        @can('product.create')
            <a href="{{ route('admin.orders.create')}}" class="btn bg-white text-dark action-btn" title="New Order"><i class="mdi mdi-basket"></i> <span class="d-none d-xl-inline">New Order</span></a>
        @endcan
        <a class="btn_modal btn bg-white text-dark action-btn" href="{{ route('admin.assignUser')}}" title="Assign User"><i class="mdi mdi-account-plus"></i> <span class="d-none d-xl-inline">Assign</span></a>
        <a class="btn_modal btn bg-white text-dark action-btn" href="{{ route('admin.orderStatusUpdateMulti')}}" title="Change Status"><i class="mdi mdi-swap-horizontal"></i> <span class="d-none d-xl-inline">Status</span></a>

        @can('order.delete')
            <a class="multi_order_delete btn bg-white text-dark action-btn" href="{{ route('admin.deleteAllOrder')}}" title="Delete Order"><i class="mdi mdi-trash-can-outline text-danger"></i> <span class="d-none d-xl-inline">Delete</span></a>
        @endcan

        {{-- 🔥 PRINT LIMIT BOX (NO INTERNAL BORDER, SEAMLESS DESIGN) 🔥 --}}
        <div class="d-flex align-items-center bg-white action-btn rounded shadow-sm" style="gap: 2px;">
            <input type="number" id="bulk_print_limit" class="form-control border-0 text-center px-1" placeholder="Qty" style="width: 40px; height: 26px; font-size: 13px; font-weight: bold; background: transparent; box-shadow: none;" min="1" value="50" title="Print Quantity limit">
            <button type="button" class="btn bg-white text-dark m-0 px-2 border-0" id="btn_auto_limit_print" style="height: 26px; font-size: 17px; box-shadow: none;" title="Auto Print Limit">
                <i class="mdi mdi-printer"></i>
            </button>
            <a class="multi_order_print d-none" href="{{ route('admin.orderList')}}"></a>
        </div>

        <a class="send_to_redx btn bg-white text-dark action-btn" href="{{ route('admin.createRedxParcel')}}" title="Send to RedX"><i class="mdi mdi-truck-fast text-danger"></i> <span class="d-none d-xl-inline">Redx</span></a>
        <a class="send_to_pathao btn bg-white text-dark action-btn" href="{{ route('admin.createPathaoParcel')}}" title="Send to Pathao"><i class="mdi mdi-motorbike text-danger"></i> <span class="d-none d-xl-inline">Pathao</span></a>
        <a class="send_to_steadfast btn bg-white text-dark action-btn" href="{{ route('admin.createSteadfastParcel')}}" title="Send to Steadfast"><i class="mdi mdi-truck-delivery text-success"></i> <span class="d-none d-xl-inline">Steadfast</span></a>
        
        <a class="send_to_carrybee btn bg-white text-dark action-btn" href="{{ route('admin.createCarrybeeParcel')}}" title="Send to Carrybee"><i class="mdi mdi-bee text-warning"></i> <span class="d-none d-xl-inline">Carrybee</span></a>
        
        <button class="btn bg-white text-dark action-btn" id="btn_courier_status" href="{{ route('admin.updateCourierStatus') }}" title="Sync Courier Status"><i class="mdi mdi-refresh text-primary"></i> <span class="d-none d-xl-inline">Sync Status</span></button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body pb-2">
        <form id="filter_form">
            <div class="status-dashboard">
                <div class="status-chip-row">
                    @foreach(getOrderStatus() as $key=>$value)
                        @php
                            $icon = 'mdi-package-variant';
                            $iconColor = 'text-secondary';
                            $lbl = strtolower($value);
                            
                            if(str_contains($lbl, 'pending')) { $icon = 'mdi-clock-outline'; $iconColor = 'text-warning'; }
                            elseif(str_contains($lbl, 'confirm')) { $icon = 'mdi-check-decagram'; $iconColor = 'text-primary'; }
                            elseif(str_contains($lbl, 'process')) { $icon = 'mdi-cached'; $iconColor = 'text-info'; }
                            elseif(str_contains($lbl, 'ship') || str_contains($lbl, 'courier')) { $icon = 'mdi-truck-delivery'; $iconColor = 'text-dark'; }
                            elseif(str_contains($lbl, 'deliver') || str_contains($lbl, 'complete')) { $icon = 'mdi-check-circle'; $iconColor = 'text-success'; }
                            elseif(str_contains($lbl, 'cancel') || str_contains($lbl, 'return') || str_contains($lbl, 'missing') || str_contains($lbl, 'incomplete')) { $icon = 'mdi-close-circle'; $iconColor = 'text-danger'; }
                            elseif(str_contains($lbl, 'hold')) { $icon = 'mdi-pause-circle'; $iconColor = 'text-warning'; }
                        @endphp
                        
                        <label class="status-chip" data-status="{{$key}}">
                            <input type="radio" class="order_sts" name="status" value="{{$key}}"/>
                            <div class="d-flex align-items-center gap-2" style="overflow: hidden;">
                                <i class="mdi {{$icon}} {{$iconColor}} chip-icon"></i>
                                <span class="chip-text" title="{{$value}}">{{$value}}</span>
                            </div>
                            {{-- 🔥 DYNAMIC COUNTS IMPLEMENTED HERE 🔥 --}}
                            <span class="count-badge shadow-sm">
                                {{ $counts[$key] ?? 0 }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="search-filter-bar">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-5 col-md-12">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify text-muted" style="font-size:18px;"></i></span>
                            <input type="search" class="form-control bg-light border-start-0" placeholder="Search by ID, Phone, Name..." name="q" autocomplete="off">
                            <button type="button" class="btn btn-primary px-4 fw-bold m-0" id="submit_search">Search</button>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                            <div class="d-flex align-items-center bg-light rounded-3 p-1 border shadow-sm">
                                <span class="px-3 fw-bold text-muted d-none d-md-block" style="font-size:12px;">DATE</span>
                                <input type="date" class="form-control bg-white border-0 fw-bold text-dark" name="start_date" id="start_date" style="width: 140px;">
                                <span class="px-2 text-muted fw-bold">-</span>
                                <input type="date" class="form-control bg-white border-0 fw-bold text-dark" name="end_date" id="end_date" style="width: 140px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-grid">
    <div class="main-list-area" id="rcvd_order">
        
        @include('backend.orders.received_order')

    </div>

    <div class="right-details-panel" id="rightDetailsPanel">
        <div class="drawer-header">
            <h5 class="m-0 fw-bold"><i class="mdi mdi-text-box-check-outline text-primary me-2"></i>Order Summary</h5>
            <button class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; padding: 0;" onclick="closeRightPanel()">
                <i class="mdi mdi-close text-danger" style="font-size: 16px;"></i>
            </button>
        </div>
        <div class="drawer-body position-relative">
            <div id="rp-loader" class="text-center text-primary" style="display:none; margin-top: 40%;">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2 text-muted fw-bold">Loading Data...</p>
            </div>
            <div id="rp-content" style="display: none;"></div>
        </div>
    </div>

</div>

{{-- MODALS --}}
<div class="modal fade" id="common_modal" tabindex="-1" aria-hidden="true"></div>

<div class="modal fade orderFraudModal" id="fraudModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark">Fraud Check Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 orderFraud bg-white">Loading...</div>
        </div>
    </div>
</div>

<div class="modal fade orderHistoryModal" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark">Customer Order History: <span id="historyPhone" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 orderHistoryBody bg-white">Loading...</div>
        </div>
    </div>
</div>

<div class="modal fade orderActivityLogModal" id="activityLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark">Order Activity Log: <span id="logOrderId" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 activityLogBody bg-white">Loading...</div>
        </div>
    </div>
</div>

<div class="bulk-bar" id="bulkBar">
    <span class="count" id="bulkCount">0 Selected</span>
    <button type="button" class="btn btn-sm btn-info text-white" id="bb-assign"><i class="mdi mdi-account-plus"></i> Assign</button>
    <button type="button" class="btn btn-sm btn-secondary text-dark border border-dark" id="bb-status"><i class="mdi mdi-swap-horizontal"></i> Status</button>
    
    <div class="dropup d-inline-block">
        <button type="button" class="btn btn-sm btn-warning text-dark dropdown-toggle border-0" data-bs-toggle="dropdown">
            <i class="mdi mdi-truck-delivery"></i> Courier
        </button>
        <ul class="dropdown-menu shadow mb-2" style="border-radius:10px;">
            <li><a class="dropdown-item fw-bold" href="#" id="bb-redx">Redx</a></li>
            <li><a class="dropdown-item fw-bold" href="#" id="bb-pathao">Pathao</a></li>
            <li><a class="dropdown-item fw-bold" href="#" id="bb-steadfast">Steadfast</a></li>
            <li><a class="dropdown-item fw-bold" href="#" id="bb-carrybee">Carrybee</a></li>
        </ul>
    </div>
    <button type="button" class="btn btn-sm btn-success text-white" id="bb-print"><i class="mdi mdi-printer"></i> Print Selected</button>
</div>

@endsection

@push('js')
<script>
$(function(){

    $(document).off('click', '.btn_modal').on('click', '.btn_modal', function(e){
        e.preventDefault();
        e.stopImmediatePropagation(); 

        var url = $(this).attr('href');
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                $('#common_modal').html(res).modal('show');
            },
            error: function(err) {
                if(typeof toastr !== 'undefined') toastr.error('Failed to load modal data!');
            }
        });
    });

    if ($('#rightDetailsPanel').length) {
        $('#rightDetailsPanel').appendTo('body');
    }

    window.showOrderDetails = function(row, id) {
        $('.order-row').removeClass('active-row');
        $(row).addClass('active-row');

        if(!$('#drawerBackdrop').length) {
            $('body').append('<div class="panel-backdrop" id="drawerBackdrop" onclick="closeRightPanel()"></div>');
        }
        
        $('#rightDetailsPanel').css('display', 'flex').addClass('show'); 
        $('#drawerBackdrop').addClass('show');
        
        $('#rp-content').hide();
        $('#rp-loader').show();

        $.get(`/admin/orders-details-ajax/${id}`, function(res) {
            $('#rp-loader').hide();
            $('#rp-content').html(res.html).fadeIn(200);
        }).fail(function() {
            $('#rp-loader').hide();
            $('#rp-content').html('<div class="text-danger text-center mt-5">Failed to load data!</div>').show();
        });
    }

    window.closeRightPanel = function() {
        $('#rightDetailsPanel').removeClass('show');
        $('#drawerBackdrop').removeClass('show');
        $('.order-row').removeClass('active-row');
        
        setTimeout(function() { 
            $('#rightDetailsPanel').css('display', 'none'); 
        }, 400);
    };

    window.updateSts = function(id, status) {
        if(confirm('Are you sure you want to mark this order as ' + status + '?')) {
            $.ajax({
                url: "{{ route('admin.multuOrderStatusUpdate') }}",
                type: "GET",
                data: { order_ids: [id], status: status },
                success: function(res) {
                    if(res.status) {
                        if(typeof toastr !== 'undefined') toastr.success('Order marked as ' + status);
                        smartReload();
                        closeRightPanel();
                    } else {
                        if(typeof toastr !== 'undefined') toastr.error(res.msg || 'Error occurred!');
                    }
                },
                error: function() {
                    if(typeof toastr !== 'undefined') toastr.error('Server Error!');
                }
            });
        }
    };

    $(document).on('click', '.address-edit-btn', function(e) {
        e.preventDefault();
        $('#address-view-mode').hide();
        $('.address-edit-btn').hide();
        $('#address-edit-mode').show();
    });

    $(document).on('click', '.address-cancel-btn', function(e) {
        e.preventDefault();
        $('#address-view-mode').show();
        $('.address-edit-btn').show();
        $('#address-edit-mode').hide();
    });

    $(document).on('click', '.address-save-btn', function(e) {
        e.preventDefault();
        let btn = $(this);
        let orderId = btn.data('id');
        let newAddress = $('#new_shipping_address').val();
        
        if(newAddress.trim() === '') {
            if(typeof toastr !== 'undefined') toastr.warning('Address cannot be empty!');
            return;
        }

        btn.html('Saving...').prop('disabled', true); 

        $.ajax({
            url: "{{ route('admin.orders.updateAddressAjax') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", id: orderId, shipping_address: newAddress },
            success: function(res) {
                btn.html('Save').prop('disabled', false);
                if(res.status) {
                    if(typeof toastr !== 'undefined') toastr.success(res.msg);
                    $('#address-view-mode').text(newAddress).show(); 
                    $('.address-edit-btn').show();
                    $('#address-edit-mode').hide();
                } else {
                    if(typeof toastr !== 'undefined') toastr.error(res.msg || 'Failed to update!');
                }
            },
            error: function() {
                btn.html('Save').prop('disabled', false);
                if(typeof toastr !== 'undefined') toastr.error('Server Error!');
            }
        });
    });

    $(document).on('click', '.order-row', function(e) {
        if ($(e.target).closest('a, button, input, select, label, .badge-soft, textarea, .action-icon, .view-history-btn').length) {
            return;
        }
        let id = $(this).data('id');
        window.showOrderDetails(this, id);
    });

    window.refreshBulk = function(){
        const cnt = $('.order_checkbox:checked:visible').length;
        $('#bulkCount').text(cnt + ' Selected');
        if(cnt > 0){ 
            $('#bulkBar').fadeIn(150); 
        } else { 
            $('#bulkBar').fadeOut(150); 
        }
    }

    $(document).on('change', '.check_all', function() {
        let isChecked = $(this).is(":checked");
        $(this).closest('table').find('.order_checkbox:visible').prop('checked', isChecked);
        window.refreshBulk();
    });

    $(document).on('change', '.order_checkbox', function() {
        let $table = $(this).closest('table');
        let total = $table.find('.order_checkbox:visible').length;
        let checked = $table.find('.order_checkbox:checked:visible').length;
        
        if(total > 0 && total === checked) {
            $table.find('.check_all').prop('checked', true);
        } else {
            $table.find('.check_all').prop('checked', false);
        }
        window.refreshBulk();
    });

    function syncNavbarActiveState(statusValue) {
        let targetStatus = statusValue === undefined || statusValue === null ? '' : statusValue;
        $('#menuOrders .sub-link').removeClass('active');
        $('#menuOrders .sub-link').each(function() {
            let href = $(this).attr('href');
            if(href) {
                let url = new URL(href, window.location.origin);
                let linkStatus = url.searchParams.get('status') || '';
                if (linkStatus === targetStatus) { $(this).addClass('active'); }
            }
        });
    }

    function smartReload() {
        getOrderList(); 
        $('.modal').modal('hide'); 
    }

    function syncActiveChip(){
        const $checked = $(".order_sts:checked");
        $('.status-chip').removeClass('is-active');
        if($checked.length) $checked.closest('.status-chip').addClass('is-active');
    }

    $("select[name='redx_status'], select[name='courier_type']").on('change', getOrderList);
    $("#start_date, #end_date").on('change', getOrderList);
    
    $(document).on('change', '.order_sts', function(){ 
        syncActiveChip(); 
        getOrderList(); 
    });

    function getOrderList(){
        const statusValue = $("input[name='status']:checked").val();
        const redx_status = $("select[name='redx_status']").val();
        const courier_type = $("select[name='courier_type']").val();
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();

        $.ajax({
            type: 'GET',
            url: "{{ route('admin.status_wise_order') }}",
            data: {statusValue, redx_status, courier_type, start_date, end_date},
            success: function(res){
                if(res.success === true){ 
                    $('#rcvd_order').html(res.view); 
                    
                    $('.check_all').prop('checked', false);
                    window.refreshBulk();
                    
                    window.currentAjaxUrl = this.url;
                    
                    if(res.counts){
                        $.each(res.counts, function(status, count){
                            $('.status-chip[data-status="'+status+'"] .count-badge').text(count);
                        });
                    }
                    syncNavbarActiveState(statusValue);
                }
            }
        });
    }

    $('#submit_search').on('click', function(){
        const searchValue = $("input[name='q']").val();
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        const statusValue = $("input[name='status']:checked").val();

        $.ajax({
            type: 'GET',
            url: "{{ route('admin.searchOrder') }}",
            data: {searchValue, start_date, end_date, statusValue},
            success: function(res){
                if(res.success === true){ 
                    $('#rcvd_order').html(res.view); 
                    
                    $('.check_all').prop('checked', false);
                    window.refreshBulk();
                    
                    window.currentAjaxUrl = this.url;
                    
                    if(res.counts){
                        $.each(res.counts, function(status, count){
                            $('.status-chip[data-status="'+status+'"] .count-badge').text(count);
                        });
                    }
                    syncNavbarActiveState(statusValue);
                }
            }
        });
    });

    syncActiveChip(); 

    $(document).on('click', '.btn-fraud', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        $('.orderFraudModal .orderFraud').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted fw-bold">Checking fraud data...</div></div>');
        $('.orderFraudModal').modal('show');

        $.ajax({
            url: url, type: 'GET',
            success: function (response) { $('.orderFraudModal .orderFraud').html(response); },
            error: function() { $('.orderFraudModal .orderFraud').html('<div class="text-danger text-center py-4 fw-bold">Error fetching fraud data.</div>'); }
        });
    });

    $(document).on('click', '.view-history-btn', function (e) {
        e.preventDefault();
        const mobile = $(this).data('mobile');
        $('#historyPhone').text(mobile);
        $('#historyModal').modal('show');
        $('.orderHistoryBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted fw-bold">Fetching history...</div></div>');

        $.get("{{ route('admin.orders.customerHistory') }}", {mobile: mobile}, function (response) {
            $('.orderHistoryBody').html(response.html);
        }).fail(function() {
            $('.orderHistoryBody').html('<div class="text-danger text-center py-4 fw-bold">Failed to load history.</div>');
        });
    });

    $(document).on('click', '.view-activity-log', function (e) {
        e.preventDefault();
        const orderId = $(this).data('id');
        $('#logOrderId').text('#' + orderId);
        $('#activityLogModal').modal('show');
        $('.activityLogBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted fw-bold">Fetching logs...</div></div>');

        let logUrl = "{{ route('admin.order.history', ':id') }}".replace(':id', orderId);
        $.ajax({
            url: logUrl, type: 'GET',
            success: function (response) { $('.activityLogBody').html(response.html); },
            error: function() { $('.activityLogBody').html('<div class="text-danger text-center py-4 fw-bold">Error fetching logs.</div>'); }
        });
    });

    $(document).on('submit', 'form#order_status_update_form', function(e){
        e.preventDefault();
        const url = $(this).attr('action');
        const status = $('#multi_status').val();
        const order_ids = $('.order_checkbox:checked:visible').map(function(){ return $(this).val(); }).get();
        if(!order_ids.length){ toastr.error('Please Select An Order First !'); return; }
        $.get(url, {status,order_ids}, function(res){
            if(res.status){ toastr.success(res.msg); smartReload(); }
            else{ toastr.error(res.msg); }
        });
    });

    $(document).on('submit', 'form#order_assign_form', function(e){
        e.preventDefault();
        const url = $(this).attr('action');
        const formData = $(this).serialize(); 
        const btn = $(this).find('button[type="submit"]');
        
        $.ajax({
            type: 'POST',  url: url, data: formData,
            beforeSend: function(){ btn.html('<i class="mdi mdi-spin mdi-loading"></i> Processing...').prop('disabled', true); },
            success: function(res){
                if(res.status){ toastr.success(res.msg); smartReload(); } 
                else { toastr.error(res.msg); btn.text('Transfer Now').prop('disabled', false); }
            },
            error: function(){ toastr.error('Something went wrong!'); btn.text('Transfer Now').prop('disabled', false); }
        });
    });

    $(document).on('click', 'a.multi_order_delete', function(e){
        e.preventDefault();
        const url = $(this).attr('href');
        const order_ids = $('.order_checkbox:checked:visible').map(function(){ return $(this).val(); }).get();
        if(!order_ids.length){ toastr.error('Please Select An Order First !'); return; }
        $.get(url, {order_ids}, function(res){
            if(res.status){ toastr.success(res.msg); smartReload(); }
            else{ toastr.error(res.msg); }
        });
    });

    $(document).on('click', 'a.multi_order_print', function(e){
        e.preventDefault();
        const url = $(this).attr('href');
        const order_ids = $('.order_checkbox:checked:visible').map(function(){ return $(this).val(); }).get();
        if(!order_ids.length){ toastr.error('Please Select Atleast One Order!'); return; }
        $.get(url, {order_ids}, function(res){
            if(res.status){
                const w = window.open("", "_blank");
                w.document.write(res.view);
                smartReload(); 
            }else{ toastr.error(res.msg); }
        });
    });

    $('#btn_auto_limit_print').on('click', function(e){
        e.preventDefault();
        let limit = parseInt($('#bulk_print_limit').val());
        
        if(isNaN(limit) || limit <= 0){
            toastr.error('Please enter a valid quantity limit!');
            return;
        }

        $('.order_checkbox').prop('checked', false);
        
        let availableBoxes = $('.order_checkbox:visible');
        if(availableBoxes.length === 0){
            toastr.warning('No orders found on this page to print!');
            return;
        }

        let selected = 0;
        availableBoxes.each(function(){
            if(selected < limit){
                $(this).prop('checked', true);
                selected++;
            }
        });

        window.refreshBulk();

        if(selected > 0){
            toastr.info(`Selected ${selected} orders. Generating Print...`);
            $('.multi_order_print').first().trigger('click');
        }
    });

    $(document).on('click', '#btn_courier_status', function(e){
        e.preventDefault();
        const url = $(this).attr('href');
        const link = $(this);
        const order_ids = $('.order_checkbox:checked:visible').map(function(){ return $(this).val(); }).get();
        if(!order_ids.length){ toastr.error('Please Select Atleast One Order!'); return; }
        if(confirm('Are you sure?')){
            $.ajax({
                type:'GET', url, data:{order_ids},
                beforeSend: function(){ link.addClass('disable-click').text('Please wait...'); },
                success:function(res){
                    link.removeClass('disable-click').html('<i class="mdi mdi-refresh me-1"></i> Courier Status');
                    if(res.status){ toastr.success(res.msg); smartReload(); }
                    else{ toastr.error(`Invoice No. : ${res.invoice} something went wrong!`); }
                }
            });
        }
    });

    function sendToCourier(link, label){
        const url = link.attr('href');
        const order_ids = $('.order_checkbox:checked:visible').map(function(){ return $(this).val(); }).get();
        if(!order_ids.length){ toastr.error('Please Select Atleast One Order!'); return; }
        $.ajax({
            type:'GET', url, data:{order_ids},
            beforeSend: function(){ link.addClass('disable-click').text('Please wait...'); },
            success:function(res){
                link.removeClass('disable-click').text(label);
                if(res.status){ toastr.success(res.msg); smartReload(); }
                else{ toastr.error(`Invoice :${res.invoice} something went wrong!`); }
            }
        });
    }
    
    $(document).on('click', 'a.send_to_redx', function(e){ e.preventDefault(); sendToCourier($(this),'Redx'); });
    $(document).on('click', 'a.send_to_pathao', function(e){ e.preventDefault(); sendToCourier($(this),'Pathao'); });
    $(document).on('click', 'a.send_to_steadfast', function(e){ e.preventDefault(); sendToCourier($(this),'Steadfast'); });

    $('#bb-redx').on('click', function(e){ e.preventDefault(); $('.send_to_redx').first().trigger('click'); });
    $('#bb-pathao').on('click', function(e){ e.preventDefault(); $('.send_to_pathao').first().trigger('click'); });
    $('#bb-steadfast').on('click', function(e){ e.preventDefault(); $('.send_to_steadfast').first().trigger('click'); });

    $(document).on('click', 'a.send_to_carrybee', function(e){ e.preventDefault(); sendToCourier($(this),'Carrybee'); });
    $('#bb-carrybee').on('click', function(e){ e.preventDefault(); $('.send_to_carrybee').first().trigger('click'); });

    $('#bb-assign').on('click', function(){ $('.btn_modal[href="{{ route("admin.assignUser") }}"]').trigger('click'); });
    $('#bb-status').on('click', function(){ $('.btn_modal[href="{{ route("admin.orderStatusUpdateMulti") }}"]').trigger('click'); });
    $('#bb-print').on('click', function(){ $('.multi_order_print').first().trigger('click'); });
    $('#bb-delete').on('click', function(){ $('.multi_order_delete').trigger('click'); });

    $(document).on('click', '.ajax-pagination a, .pagination a', function(e) {
        e.preventDefault(); 
        
        let pageUrl = $(this).attr('href'); 

        $.ajax({
            type: 'GET',
            url: pageUrl,
            success: function(res) {
                if(res.success === true) { 
                    $('#rcvd_order').html(res.view); 
                    
                    $('.check_all').prop('checked', false);
                    window.refreshBulk();
                    
                    if(res.counts){
                        $.each(res.counts, function(status, count){
                            $('.status-chip[data-status="'+status+'"] .count-badge').text(count);
                        });
                    }
                } else if(res.html) {
                    $('#rcvd_order').html(res.html); 
                } else {
                    $('#rcvd_order').html(res); 
                }
            },
            error: function() {
                if(typeof toastr !== 'undefined') toastr.error('Failed to load next page!');
            }
        });
    });

});
</script>
@endpush
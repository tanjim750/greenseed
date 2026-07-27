<style>
    /* মডার্ন গ্রিড/কার্ড লেআউট */
    .performance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }
    
    .staff-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: all 0.2s ease-in-out;
    }
    .staff-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }

    /* টপ পারফর্মার স্পেশাল স্টাইল */
    .top-card {
        border: 2px solid #f59e0b;
        background: #fffbeb;
    }
    .badge-top {
        background: #f59e0b;
        color: #ffffff !important;
        font-size: 11px;
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 12px;
        display: inline-block;
        margin-bottom: 8px;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
    }

    /* কার্ড হেডার */
    .staff-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    .staff-name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .staff-total {
        font-size: 13px;
        font-weight: 800;
        color: #2563eb;
        background: #eff6ff;
        padding: 5px 10px;
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        white-space: nowrap;
    }

    /* KPI পারফরম্যান্স */
    .kpi-row {
        display: flex;
        justify-content: space-between;
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 8px;
        margin-bottom: 12px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #f1f5f9;
    }

    /* স্ট্যাটাস গ্রিড */
    .status-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 12px;
        color: #475569;
        font-weight: 600;
    }
    
    .top-card .status-item {
        background: #fffbeb;
        border-color: #fde68a;
    }

    /* ✅ FIX: স্ট্যাটাস ব্যাজে !important দিয়ে সাদা কালার ফিক্স করা হলো */
    .status-count {
        font-size: 11.5px;
        padding: 3px 8px;
        border-radius: 4px;
        color: #ffffff !important; /* লেখা যেন অবশ্যই সাদা থাকে */
        font-weight: bold;
    }
    .empty-count {
        color: #94a3b8;
        font-weight: normal;
    }
    
    /* কাস্টম কালার ব্যাজ */
    .bg-custom-success { background-color: #10b981 !important; }
    .bg-custom-danger { background-color: #ef4444 !important; }
    .bg-custom-primary { background-color: #3b82f6 !important; }
    .bg-custom-secondary { background-color: #64748b !important; }
</style>

@if($items->count() > 0)
    <div class="performance-grid">
        @foreach($items as $index => $item)
            @php
                // টপ পারফর্মার লজিক
                $isTopPerformer = ($index === 0 && $items->currentPage() == 1 && $item->total_orders > 0);
                
                // KPI ক্যালকুলেশন
                $total = $item->total_orders ?: 1; 
                $successRate = round((($item->kpi_delivered ?? 0) / $total) * 100, 1);
                $failedRate = round((($item->kpi_failed ?? 0) / $total) * 100, 1);
            @endphp
            
            <div class="staff-card {{ $isTopPerformer ? 'top-card' : '' }}">
                
                @if($isTopPerformer)
                    <div class="badge-top">
                        <i class="mdi mdi-trophy"></i> Top Performer
                    </div>
                @endif
                
                <div class="staff-header">
                    <div class="staff-name">
                        {{ $item->assign_user_name ?? 'Unassigned / Admin' }} <br>
                        <small class="text-muted fw-normal" style="font-size: 11.5px;">{{ $item->last_name ?? '' }}</small>
                    </div>
                    <div class="staff-total">
                        Total: {{ $item->total_orders }}
                    </div>
                </div>

                {{-- প্রফেশনাল পারফরম্যান্স রেট --}}
                <div class="kpi-row">
                    <span class="text-success"><i class="mdi mdi-check-circle"></i> Success: {{ $successRate }}%</span>
                    <span class="text-danger"><i class="mdi mdi-close-circle"></i> Failed: {{ $failedRate }}%</span>
                </div>

                {{-- ডাইনামিক স্ট্যাটাস ডাটা --}}
                <div class="status-grid">
                    @foreach(getOrderStatus() as $key => $label)
                        @php 
                            $colName = 'status_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($key)); 
                            $count = $item->{$colName} ?? 0;
                            
                            // ✅ FIX: থিমের বুটস্ট্র্যাপ ক্লাসের উপর নির্ভর না করে কাস্টম ক্লাস ব্যবহার করা হয়েছে
                            $bgClass = 'bg-custom-secondary';
                            $lowerKey = strtolower($key);
                            
                            if (in_array($lowerKey, ['delivered', 'completed'])) {
                                $bgClass = 'bg-custom-success';
                            } elseif (in_array($lowerKey, ['cancelled', 'returning', 'return missing', 'return received'])) {
                                $bgClass = 'bg-custom-danger';
                            } elseif (in_array($lowerKey, ['processing', 'shipped', 'courier', 'confirmed'])) {
                                $bgClass = 'bg-custom-primary';
                            }
                        @endphp
                        
                        <div class="status-item">
                            <span>{{ trim(str_ireplace('order', '', $label)) }}</span>
                            @if($count > 0)
                                <span class="status-count {{ $bgClass }}">{{ $count }}</span>
                            @else
                                <span class="empty-count">-</span>
                            @endif
                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach
    </div>
@else
    <div class="text-center text-muted py-5" style="background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
        <i class="mdi mdi-inbox-outline" style="font-size: 50px; color: #cbd5e1;"></i>
        <p class="mt-2 mb-0 fs-5">No data found</p>
    </div>
@endif

{{-- Pagination --}}
@if($items->hasPages())
    <div class="mt-4 d-flex justify-content-end">
        {!! $items->appends(Request::all())->links() !!}
    </div>
@endif
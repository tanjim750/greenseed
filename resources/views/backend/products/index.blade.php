@extends('backend.app')
@section('content')

<style>
  :root{
    --bc-border:#e5e7eb;
    --bc-soft:#fafafa;
    --bc-dark:#111827;
    --bc-muted:#6b7280;
    --success:#16a34a;
    --danger:#ef4444;
  }

  th, td, h4, .pr_list, .form-label { color:#000 !important; }

  /* Sticky toolbar */
  .toolbar-sticky{
    position: sticky; top: 0; z-index: 6; background:#fff;
    padding:.5rem 0; border-bottom:1px solid #f1f1f1;
  }

  /* ✅ Excel-like full grid (desktop/table view) */
  table.table{
    border-collapse:collapse !important;
    border-spacing:0 !important;
    background:#fff;
  }
  table.table thead th,
  table.table tbody td{
    border:1px solid var(--bc-border) !important;
    vertical-align:middle;
  }
  table.table thead th{
    background:#f8fafc !important;
    font-weight:800;
    white-space:nowrap;
  }

  /* ✅ Action icons boxed + inline */
  .td-action{ white-space:nowrap; }
  .action-icons{
    display:flex;
    align-items:center;
    gap:10px;
  }
  .action-icon{
    display:inline-flex !important;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    border:1px solid var(--bc-border);
    border-radius:8px;
    background:#fff;
    color:#111827;
    text-decoration:none;
  }
  .action-icon i{ font-size:18px; line-height:1; }
  .action-icon:hover{ background:#f3f4f6; }
  .action-icon.delete{ color:var(--danger) !important; }
  .action-icon.delete:hover{ background:#fee2e2; }

  /* Hover row soft */
  table.table.table-hover tbody tr:hover{
    background:#f9fafb;
  }

  .bulk-info{
    font-size:.9rem; color:#555;
  }

  /* Mobile card table */
  @media (max-width:576px){
    .table-responsive{ border:0; }
    table.table thead{ display:none; }
    table.table tbody tr{
      display:block; margin-bottom:10px; border:1px solid #eee; border-radius:10px; padding:10px;
    }
    table.table tbody td{
      display:flex; justify-content:space-between; gap:10px;
      border:0 !important; padding:.25rem 0 !important;
      font-size:13px !important;
    }
    table.table tbody td::before{
      content: attr(data-label);
      font-weight:600; color:#111;
    }
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active pr_list">Product List</li>
        </ol>
      </div>
      <h4 class="page-title">Product List</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 p-1">
    <div class="card">
      <div class="card-body">

        {{-- Toolbar / Filters --}}
        <div class="row mb-2 toolbar-sticky">
          <div class="col-md-8">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <a class="btn btn-sm btn-info recomm_update" href="{{ route('admin.recommendedUpdate')}}?is_recommended=1">Active (Home)</a>
              <a class="btn btn-sm btn-danger recomm_update" href="{{ route('admin.recommendedUpdate')}}?is_recommended=0">De-active (Home)</a>
              <a class="btn btn-sm btn-info show_update" href="{{ route('admin.showUpdate')}}?status=1">Show</a>
              <a class="btn btn-sm btn-danger show_update" href="{{ route('admin.showUpdate')}}?status=0">Hide</a>

              <span class="bulk-info ms-2">
                Selected: <strong id="bulkCount">0</strong>
              </span>
            </div>
          </div>

          <div class="col-md-4 text-xl-end mt-xl-0 mt-2">
            @can('product.create')
              <a href="{{ route('admin.products.create')}}" class="btn btn-danger mb-2 me-2">
                <i class="mdi mdi-basket me-1"></i> Add Product
              </a>
            @endcan
            <a href="{{ route('admin.productExport') }}" class="btn btn-light mb-2" style="color:#000;">Export</a>
          </div>

          <div class="col-12 mt-2">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.cat_wise_product') }}">
              <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control select2" id="category">
                  <option value="">{{ __('Select Category') }}</option>
                  @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ $category->id == $cat_id ? 'selected' : '' }}>{{ $category->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="search" class="form-control" name="q" placeholder="Search..." value="{{ $q ?? '' }}">
              </div>
              <div class="col-md-2">
                <label class="form-label d-block">&nbsp;</label>
                <button class="btn btn-primary w-100">Filter</button>
              </div>
            </form>
          </div>
        </div>

        {{-- Table --}}
        <div class="col-md-12 col-sm-12 p-1">
          <div class="table-responsive">
            <table class="table table-centered mb-0 table-hover">
              <thead class="table-light">
                <tr>
                  <th style="width:10%">Action</th>
                  <th>
                    <div class="form-check mb-0">
                      <input type="checkbox" class="form-check-input check_all" id="check_all">
                    </div>
                  </th>
                  <th style="width:12%">Product</th>
                  <th style="width:8%">Sku</th>
                  <th>Image</th>
                  <th>Type</th>
                  <th>Category</th>
                  <th>Sell Price</th>
                  <th>Stock</th>
                  <th>Visibility</th>
                  <th style="width:12%;">Priority</th>
                  <th>Recommended</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $item)
                  <tr>
                    {{-- ✅ FIXED: Action icons inline + boxed --}}
                    <td data-label="Action" class="td-action">
                      <div class="action-icons">
                        @can('product.edit')
                          <a href="{{ route('admin.products.edit',[$item->id])}}" class="action-icon" title="Edit">
                            <i class="mdi mdi-square-edit-outline"></i>
                          </a>
                        @endcan
                        @can('product.delete')
                          <a href="{{ route('admin.products.destroy',[$item->id])}}" class="delete action-icon" title="Delete">
                            <i class="mdi mdi-delete"></i>
                          </a>
                        @endcan
                      </div>
                    </td>

                    <td data-label="Select">
                      <input type="checkbox" class="checkbox" value="{{ $item->id}}">
                    </td>
                    <td data-label="Product">{{ $item->name }}</td>
                    <td data-label="Sku">{{ $item->sku }}</td>
                    <td data-label="Image">
                      <img src="{{ getImage('thumb_products',$item->image)}}" class="rounded-circle avatar-xs" alt="img">
                    </td>
                    <td data-label="Type">{{ $item->type }}</td>
                    <td data-label="Category">{{ $item->category? $item->category->name : '' }}</td>
                    <td data-label="Sell Price">{{ number_format($item->sell_price,2) }}</td>
                    <td data-label="Stock">{{ $item->stock_quantity ?? 0 }}</td>
                    <td data-label="Visibility">{{ $item->status=='1' ? 'Show' : 'Hide' }}</td>
                    <td data-label="Priority">
                      <input type="number" min="0" class="priority-input form-control form-control-sm"
                             data-product-id="{{ $item->id }}"
                             value="{{ $item->priority }}"
                             style="max-width:80px"/>
                    </td>

                    {{-- ✅ Recommended: শুধু টেক্সট / ব্যাজ, নো টগল --}}
                    <td data-label="Recommended">
                      @if($item->is_recommended == '1')
                        <span class="badge bg-success">Yes</span>
                      @else
                        <span class="badge bg-secondary">No</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          <div class="mt-2">
            {!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
(function(){
  // Bulk select
  const checkAll = document.querySelector('.check_all');
  const bulkCount = document.getElementById('bulkCount');

  function updateBulkCount(){
    const count = document.querySelectorAll('.checkbox:checked').length;
    if(bulkCount) bulkCount.textContent = count;
  }
  checkAll?.addEventListener('change', function(){
    document.querySelectorAll('.checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkCount();
  });
  document.addEventListener('change', e=>{
    if(e.target.classList?.contains('checkbox')) updateBulkCount();
  });

  // Confirm delete
  document.addEventListener('click', function(e){
    const el = e.target.closest('a.delete');
    if(!el) return;
    if(!confirm('Delete this product?')) e.preventDefault();
  });

  // Priority debounced save
  let timer;
  function savePriority(productId, value){
    $.ajax({
      url: '/admin/update-priority/' + productId,
      type: 'POST',
      data: { priority: value, _token: '{{ csrf_token() }}' },
      success: function(){
        if (window.toastr) toastr.success('Priority updated');
      },
      error: function(){
        if (window.toastr) toastr.error('Failed to update priority');
      }
    });
  }

  $(document).on('input', '.priority-input', function(){
    const productId = $(this).data('product-id');
    const val = $(this).val();
    clearTimeout(timer);
    timer = setTimeout(()=> savePriority(productId, val), 400);
  });

  // Bulk actions (recommended/show)
  function getSelectedIds(){
    return Array.from(document.querySelectorAll('.checkbox:checked')).map(cb=>cb.value);
  }

  $(document).on('click', 'a.recomm_update, a.show_update', function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    const product_ids = getSelectedIds();
    if(product_ids.length === 0){
      return window.toastr ? toastr.error('Please select product(s) first!') : alert('Please select product(s) first!');
    }
    $.ajax({
      type:'GET', url,
      data:{ 'product_ids[]': product_ids },
      beforeSend(){ $('body').css('cursor','wait'); },
      complete(){ $('body').css('cursor','default'); },
      success:function(res){
        if(res.status===true){
          if(window.toastr) toastr.success(res.msg);
          location.reload();
        }else{
          if(window.toastr) toastr.error(res.msg || 'Failed');
        }
      },
      error:function(){ if(window.toastr) toastr.error('Request failed'); }
    });
  });

})();
</script>
@endpush

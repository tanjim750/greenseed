<style>
    .cart-content-wrap {
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 100vh;
    }
    .cart-header {
        flex-shrink: 0;
    }
    .cart-body {
        flex-grow: 1;
        overflow-y: auto;
        padding-bottom: 10px;
    }
    .cart-footer {
        flex-shrink: 0;
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 15px 20px;
        border-top: 1px solid #f1f1f1;
        z-index: 999;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
    }
    .cart-footer .axil-btn {
        margin: 0;
    }
    @media (max-width: 767px) {
        .cart-footer {
            padding-bottom: 85px !important;
        }
    }
</style>

<div class="cart-content-wrap">
    <div class="cart-header">
        <h2 class="header-title">Cart review</h2>
        <button class="cart-close sidebar-close"><i class="fas fa-times"></i></button>
    </div>
    <div class="cart-body">
        <ul class="cart-item-list">
            @php
                $total=0;
            @endphp
            @if($cart)
            
            @foreach($cart as $key=>$item)
                @php
                $total +=$item['price']*$item['quantity'];
                @endphp
            <li class="cart-item">
                <div class="item-img">
                    <a href="{{ route('front.products.show',[$item['product_id']])}}">
                      <img src="{{ getImage('products', $item['image'])}}" alt="Product Image">
                    </a>
                    <form method="POST" action="{{ route('front.carts.destroy',[$key])}}" class="ajax-cart-remove-form">
                        <input type="hidden" name="segment" value="{{ $segm ?? '' }}">
                        @csrf
                        @method('DELETE')
                        <button class="close-btn ajax-remove-btn" type="button"><i class="fas fa-times"></i></button> 
                    </form>
                </div>
                <div class="item-content">
                    <h3 class="item-title"><a href="{{ route('front.products.show',[$item['product_id']])}}">{{ $item['name']}}</a></h3>
                    <div class="item-price"><span class="currency-symbol"></span> {{ priceFormate($item['price'])}}</div>
                    <div class="pro-qty item-quantity" data-segment="{{ $segm ?? '' }}" data-href="{{ route('front.carts.edit',[$key])}}">
                        <span class="dec qtybtn">-</span>
                        <input type="number" class="quantity-input" value="{{ $item['quantity']}}">
                        <span class="inc qtybtn">+</span>
                    </div>
                </div>
            </li>
            @endforeach
            @else
            <li class="cart-item">
                <div class="alert alert-warning"> Your Cart Is Empty !!</div>
            </li>
            @endif
        </ul>
    </div>
    <div class="cart-footer">
        <div class="">
            <a href="{{ route('front.checkouts.index')}}" style="color: #fff; max-width: 100%; width: 100% !important; text-align: center;" class="axil-btn checkout-btn main-bg w-100 d-block">Checkout</a>
        </div>
    </div>
</div>

<script>
    $(document).off('click', '.ajax-remove-btn').on('click', '.ajax-remove-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        let btn = $(this);
        let form = btn.closest('form');
        let url = form.attr('action');

        btn.html('<i class="fas fa-spinner fa-spin"></i>').css('pointer-events', 'none');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res && (res.view || res.html)) {
                    $('#cart-dropdown').html(res.view || res.html);
                } else {
                    form.closest('.cart-item').fadeOut(200, function() { $(this).remove(); });
                }
                
                if (res && res.item !== undefined) $('.cart-count').text(res.item);
                if (res && res.amount) $('.cart-amount').text('৳ ' + res.amount);
                
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.msg || 'Item removed successfully');
                }
            },
            error: function(err) {
                form.closest('.cart-item').fadeOut(200, function() { $(this).remove(); });
            }
        });
    });
</script>
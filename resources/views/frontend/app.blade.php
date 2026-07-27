<!DOCTYPE html>
<html lang="en">
@include('frontend.partials.head')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

<body class="sticky-header" style="border: 1px solid darkgray; box-shadow: 0 0 12px rgb(0 0 0 / 42%);">

@php
    $gtm_id = null;
    $information = \App\Models\Information::first();
    if(isset($information) && !empty($information->tracking_code)) {
        // Find GTM ID dynamically from the tracking_code script
        if(preg_match('/GTM-[A-Z0-9]+/', $information->tracking_code, $matches)) {
            $gtm_id = $matches[0];
        }
    }
@endphp

@if($gtm_id)
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm_id }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
@endif
<a href="#top" class="back-to-top main-bg" id="backto-top">
    <i class="fas fa-arrow-up"></i>
</a>

@include('frontend.partials.header')

@yield('content')

<div class="modal fade" id="quick-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content" style="border-radius:16px; overflow:hidden;">

            <div class="modal-header" style="gap:10px;">
                <h5 class="modal-title"
                    style="font-family:'Hind Siliguri',sans-serif; font-weight:900; margin:0; flex:1; text-align:center;">
                    Quick View
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="quickViewBody">
                <div class="text-center py-5">Loading...</div>
            </div>
        </div>
    </div>
</div>

<style>
    #quick-view-modal{ z-index:20000 !important; }
    .modal-backdrop{ z-index:19990 !important; }

    #quickViewBody{
        padding: 12px !important;
        max-height: calc(100vh - 160px);
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        background: #f5f6fa;
    }

    #quickViewBody *{ max-width: 100%; box-sizing: border-box; }
    #quickViewBody img{ height:auto !important; max-width:100% !important; }
    #quickViewBody table{
        width:100% !important;
        display:block;
        overflow-x:auto;
    }
</style>

@include('frontend.partials.footer')

<script>
(function () {
    if(window.__QV_LOADER__) return;
    window.__QV_LOADER__ = true;

    let popupXhr = null;

    function setTitle(txt){
        const t = document.querySelector('#quick-view-modal .modal-title');
        if(t) t.textContent = txt || 'Quick View';
    }

    function setLoading(){
        setTitle('Quick View');
        var body = document.getElementById('quickViewBody');
        if(body) body.innerHTML = '<div class="text-center py-5">Loading...</div>';
    }

    function setHtml(html){
        var body = document.getElementById('quickViewBody');
        if(body) body.innerHTML = html;

        setTitle('Quick View');

        setTimeout(() => window.dispatchEvent(new Event('resize')), 60);

        var mb = document.getElementById('quickViewBody');
        if(mb) mb.scrollTop = 0;

        setTimeout(() => {
            if(window.__QV_APPLY_STOCK__) window.__QV_APPLY_STOCK__();
        }, 20);

        setTimeout(() => {
            if(window.__QV_INIT_POPUP__) window.__QV_INIT_POPUP__();
        }, 10);
    }

    function setError(){
        setTitle('Quick View');
        var body = document.getElementById('quickViewBody');
        if(body) body.innerHTML = '<div class="alert alert-danger m-0">Something went wrong!</div>';
    }

    function openModal(){
        var el = document.getElementById('quick-view-modal');
        if(!el) return;

        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }

        if (window.jQuery && $('#quick-view-modal').modal) {
            $('#quick-view-modal').modal('show');
            return;
        }

        el.classList.add('show');
        el.style.display = 'block';
    }

    function loadPopupUrl(url){
        if(!url) return;

        setLoading();
        openModal();

        if (popupXhr && popupXhr.readyState !== 4) {
            try { popupXhr.abort(); } catch(e){}
        }

        if (window.jQuery && window.$ && $.ajax) {
            popupXhr = $.ajax({
                url: url,
                type: "GET",
                success: function (html) { setHtml(html); },
                error: function () { setError(); }
            });
            return;
        }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => setHtml(html))
            .catch(() => setError());
    }

    function loadPopupById(id){
        if(!id) return;
        const url = "{{ url('/product-popup') }}/" + id;
        loadPopupUrl(url);
    }

    document.addEventListener('click', function(e){
        const el = e.target.closest('.openProductPopup');
        if(!el) return;
        e.preventDefault();
        loadPopupById(el.getAttribute('data-id'));
    }, true);

    document.addEventListener('click', function(e){
        const el = e.target.closest('.js-quickview-open');
        if(!el) return;

        e.preventDefault();
        e.stopPropagation();

        const url = el.getAttribute('data-popup-url');
        if(url) { loadPopupUrl(url); return; }

        loadPopupById(el.getAttribute('data-id'));
    }, true);

})();
</script>

<script>
(function(){
  if(window.__POPUP_CART_FIX__) return;
  window.__POPUP_CART_FIX__ = true;

  function hasJQ(){ return (window.jQuery && window.$ && typeof $.ajax === 'function'); }

  function toast(type,msg){
    if(window.toastr){ toastr.clear(); toastr[type](msg); }
    else alert(msg);
  }

  function getBox(){
    return document.querySelector('#quickViewBody .pmodal[data-popup="1"]');
  }

  function parseVarMap(box){
    try{
      const el = box.querySelector('#pmodalVarJson');
      return el ? JSON.parse(el.textContent || '{}') : {};
    }catch(e){ return {}; }
  }

  function curr(box){
    return box.getAttribute('data-currency') || 'BDT';
  }

  function moneyText(c, val){
    val = parseFloat(val || 0);
    if(isNaN(val)) val = 0;
    if(c === 'BDT') return '৳ ' + Math.round(val);
    if(c === 'Dollar') return '$ ' + (Math.round(val*100)/100).toFixed(2);
    if(c === 'Euro') return '€ ' + (Math.round(val*100)/100).toFixed(2);
    return String(val);
  }

  function resolve(varMap, sizeId, colorId){
    const key = String(sizeId) + '|' + String(colorId);
    return varMap && varMap[key] ? varMap[key] : null;
  }

  function setWaiting(box){
    box.__ACTIVE_VAR__ = null;

    const label = box.querySelector('#pmodal_variant_label');
    if(label) label.textContent = 'Size এবং Color সিলেক্ট করুন';

    box.querySelectorAll('.js-popup-cart, .js-popup-order').forEach(b => b.disabled = true);

    const badge = box.querySelector('#pmodal_stock_badge');
    if(badge){
      badge.classList.remove('green','red');
      badge.classList.add('red');
      badge.textContent = '0 Items left';
    }
  }

  function setInvalid(box){
    box.__ACTIVE_VAR__ = null;

    const sizeTxt  = box.querySelector('#pmodalSizeOptions .pmodal-size-opt.active')?.textContent.trim() || '';
    const colorTxt = box.querySelector('#pmodalColorOptions .pmodal-color-opt.active')?.textContent.trim() || '';

    const label = box.querySelector('#pmodal_variant_label');
    if(label) label.textContent = (sizeTxt + ' - ' + colorTxt + ' (Not available)').trim();

    const badge = box.querySelector('#pmodal_stock_badge');
    if(badge){
      badge.classList.remove('green','red');
      badge.classList.add('red');
      badge.textContent = '0 Items left';
    }

    box.querySelectorAll('.js-popup-cart, .js-popup-order').forEach(b => b.disabled = true);

    toast('warning','❌ এই Size-Color কম্বিনেশনটি নেই, অন্যটি সিলেক্ট করুন');
  }

  function setDiscountUI(box, c, raw, price){
    const oldEl  = box.querySelector('#pmodal_old_price');
    const discEl = box.querySelector('#pmodal_disc_badge');

    raw = parseFloat(raw || 0);
    price = parseFloat(price || 0);

    if(raw > price && raw > 0 && price > 0){
      if(oldEl){ oldEl.style.display=''; oldEl.textContent = moneyText(c, raw); }
      if(discEl){
        const percent = Math.round(((raw - price)/raw)*100);
        discEl.style.display='';
        discEl.textContent = percent + '% Off';
      }
    }else{
      if(oldEl){ oldEl.style.display='none'; oldEl.textContent=''; }
      if(discEl){ discEl.style.display='none'; discEl.textContent=''; }
    }
  }

  function applyVar(box, v){
    box.__ACTIVE_VAR__ = v;

    const c = curr(box);

    const labelTxt = (v.size || '') + (v.color && v.color !== 'Default' ? (' - ' + v.color) : '');
    const label = box.querySelector('#pmodal_variant_label');
    if(label) label.textContent = labelTxt;

    const finalEl = box.querySelector('#pmodal_final_price');
    if(finalEl) finalEl.textContent = moneyText(c, v.price);

    setDiscountUI(box, c, v.raw, v.price);

    const vid = box.querySelector('#pmodal_variation_id'); if(vid) vid.value = v.id;

    const vn  = box.querySelector('#pmodal_variant_name'); if(vn) vn.value = labelTxt;

    const sv  = box.querySelector('#pmodal_size_value'); if(sv) sv.value = labelTxt;
    const sv1 = box.querySelector('#pmodal_size_value1'); if(sv1) sv1.value = labelTxt;

    const pv  = box.querySelector('#pmodal_price_val'); if(pv) pv.value = v.price;
    const pv1 = box.querySelector('#pmodal_price_val1'); if(pv1) pv1.value = v.price;

    const stock = parseInt(v.stock || 0);

    const badge = box.querySelector('#pmodal_stock_badge');
    if(badge){
      badge.classList.remove('green','red');
      badge.classList.add(stock > 0 ? 'green' : 'red');
      badge.textContent = (stock > 0 ? stock : 0) + ' Items left';
    }

    box.querySelectorAll('.js-popup-cart, .js-popup-order').forEach(b => b.disabled = (stock <= 0));
  }

  window.__QV_INIT_POPUP__ = function(){
    const box = getBox();
    if(!box) return;

    const varMap = parseVarMap(box);
    const keys = Object.keys(varMap || {});

    if(!keys.length){
      return;
    }

    let defSize  = parseInt(box.getAttribute('data-def-size') || 0);
    let defColor = parseInt(box.getAttribute('data-def-color') || 0);

    let v = null;

    if(defSize || defColor){
      v = resolve(varMap, defSize, defColor);
    }

    if(!v){
      v = varMap[keys[0]];
      defSize  = v.size_id;
      defColor = v.color_id;
    }

    box.querySelectorAll('#pmodalSizeOptions .pmodal-size-opt').forEach(x=>x.classList.remove('active'));
    box.querySelectorAll('#pmodalColorOptions .pmodal-color-opt').forEach(x=>x.classList.remove('active'));

    const sBtn = box.querySelector('#pmodalSizeOptions .pmodal-size-opt[data-size-id="'+defSize+'"]');
    const cBtn = box.querySelector('#pmodalColorOptions .pmodal-color-opt[data-color-id="'+defColor+'"]');

    if(sBtn) sBtn.classList.add('active');
    if(cBtn) cBtn.classList.add('active');

    applyVar(box, v);
  };

  document.addEventListener('click', function(e){
    const th = e.target.closest('#pmodalThumbs .pmodal-thumb');
    if(!th) return;

    const box = getBox();
    if(!box) return;

    const wrap = th.closest('#pmodalThumbs');
    const main = box.querySelector('#pmodalMainImg');
    if(!wrap || !main) return;

    wrap.querySelectorAll('.pmodal-thumb').forEach(x=>x.classList.remove('active'));
    th.classList.add('active');

    const src = th.getAttribute('data-src');
    if(src) main.src = src;
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const btn = e.target.closest('#pmodalSizeOptions .pmodal-size-opt');
    if(!btn) return;

    box.querySelectorAll('#pmodalSizeOptions .pmodal-size-opt').forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');

    const sizeId = parseInt(btn.getAttribute('data-size-id') || 0);
    const colorEl = box.querySelector('#pmodalColorOptions .pmodal-color-opt.active');
    if(!colorEl){ setWaiting(box); return; }

    const colorId = parseInt(colorEl.getAttribute('data-color-id') || 0);
    const v = resolve(parseVarMap(box), sizeId, colorId);
    if(v) applyVar(box, v); else setInvalid(box);
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const btn = e.target.closest('#pmodalColorOptions .pmodal-color-opt');
    if(!btn) return;

    box.querySelectorAll('#pmodalColorOptions .pmodal-color-opt').forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');

    const colorId = parseInt(btn.getAttribute('data-color-id') || 0);
    const sizeEl  = box.querySelector('#pmodalSizeOptions .pmodal-size-opt.active');
    if(!sizeEl){ setWaiting(box); return; }

    const sizeId  = parseInt(sizeEl.getAttribute('data-size-id') || 0);
    const v = resolve(parseVarMap(box), sizeId, colorId);
    if(v) applyVar(box, v); else setInvalid(box);
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const plus = e.target.closest('.pmodal .quantity .plus');
    const minus = e.target.closest('.pmodal .quantity .minus');
    if(!plus && !minus) return;

    const input = box.querySelector('.quantity input[name="quantity"]');
    if(!input) return;

    let v = Math.max(1, parseInt(input.value || '1') || 1);
    if(plus) v++;
    if(minus) v = Math.max(1, v-1);
    input.value = v;
  }, true);

  document.addEventListener('click', function(e){
    const box = getBox();
    if(!box) return;

    const cartBtn  = e.target.closest('.js-popup-cart');
    const orderBtn = e.target.closest('.js-popup-order');
    if(!cartBtn && !orderBtn) return;

    if(!box.__ACTIVE_VAR__){
      toast('warning','দয়া করে Size এবং Color সিলেক্ট করুন!');
      return;
    }

    if(parseInt(box.__ACTIVE_VAR__.stock || 0) <= 0){
      toast('warning','❌ এই ভ্যারিয়েশনটি স্টকে নেই');
      return;
    }

    const at = box.querySelector('#popup_action_type');
    if(at) at.value = cartBtn ? 'cart' : 'order';

    const form = box.querySelector('#cart_submit_popup');
    if(form) form.dispatchEvent(new Event('submit', {cancelable:true, bubbles:true}));
  }, true);

  document.addEventListener('submit', function(e){
    const form = e.target;
    if(!form || form.id !== 'cart_submit_popup') return;

    const box = getBox();
    if(!box) return;

    if(!box.__ACTIVE_VAR__){
      e.preventDefault();
      toast('warning','দয়া করে Size এবং Color সিলেক্ট করুন!');
      return;
    }

    const actionType = box.querySelector('#popup_action_type')?.value || 'cart';

    const qtyInput = box.querySelector('.quantity input[name="quantity"]');
    const q = Math.max(1, parseInt(qtyInput?.value || '1') || 1);
    if(qtyInput) qtyInput.value = q;

    if(!hasJQ()){
      return;
    }

    e.preventDefault();

    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
      success: function(res){
        if(res && res.success){
          toast('success', res.msg || 'Added');

          // ✅ DataLayer Trigger for Add to Cart
          window.dataLayer = window.dataLayer || [];
          dataLayer.push({
            event: "add_to_cart",
            ecommerce: {
                currency: "BDT",
                value: parseFloat(box.__ACTIVE_VAR__.price),
                items: [{
                    item_id: box.__ACTIVE_VAR__.id,
                    item_name: box.__ACTIVE_VAR__.name || box.querySelector('#pmodal_variant_name')?.value || 'Product',
                    price: parseFloat(box.__ACTIVE_VAR__.price),
                    quantity: q
                }]
            }
          });

          if(res.view) $('#cart_section').html(res.view);
          if(res.item !== undefined) $('.cart-count').text(res.item);
          if(res.amount !== undefined && res.amount !== null) $('.cart-amount').text('৳ '+res.amount);

          if(actionType === 'order'){
            window.location.href = (res.url ? res.url : "{{ url('/checkout') }}");
            return;
          }

          const btn = document.querySelector('.cart-dropdown-btn');
          if(btn) btn.click();
        }else{
          toast('error', (res && res.msg) ? res.msg : 'Something went wrong!');
        }
      },
      error: function(){ toast('error','Something went wrong!'); }
    });
  }, true);

})();
</script>

@stack('js')

</body>
</html>
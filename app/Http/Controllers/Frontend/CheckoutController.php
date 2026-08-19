<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendOrderNotification;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderDetails;
use App\Models\DeliveryCharge;
use App\Utils\ModulUtil;
use App\Utils\Util;
use App\Models\CouponCode;
use App\Models\User;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Information;
use App\Facades\FacebookConversion;
use Illuminate\Support\Facades\Schema;
use App\Http\Traits\DetectsOrderSource;

class CheckoutController extends Controller
{
    use DetectsOrderSource;

    public $modulutil;
    public $util;

    public function __construct(ModulUtil $modulutil, Util $util){
        $this->util=$util;
        $this->modulutil=$modulutil;
    }

    private function getActiveWorkerIds($allowedWorkers = [])
    {
        if (empty($allowedWorkers)) return collect([]);

        return User::query()
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereIn('status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereIn('id', $allowedWorkers) 
            ->orderBy('id')
            ->pluck('id');
    }

    private function pickNextWorkerId($allowedWorkers = [])
    {
        $activeIds = $this->getActiveWorkerIds($allowedWorkers);
        
        if ($activeIds->isEmpty()) {
            throw new \Exception('No active workers found for this status to assign.');
        }

        $candidateId = DB::table('users as u')
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.assign_user_id', '=', 'u.id')
                    ->whereDate('o.created_at', now()->toDateString()); 
            })
            ->whereIn('u.id', $activeIds->toArray())
            ->groupBy('u.id')
            ->orderByRaw('COUNT(o.id) ASC') 
            ->orderBy('u.id', 'ASC')
            ->value('u.id');

        return (int) ($candidateId ?? $activeIds->first());
    }
    
    private function calculateShippingCharge($request, $productId = null, $qty = 1)
    {
        $globalSetting = DB::table('delivery_charges')->first();
        $totalWeight = 0;
        $hasWeightyProduct = false;

        if ($productId) {
            $product = Product::find($productId);
            
            if ($product && $product->is_free_shipping == 1) {
                return 0;
            }

            $weight = (float)($product->weight ?? 0);
            $totalWeight = $weight * (int)$qty;
            if($weight > 0) $hasWeightyProduct = true;
        } else {
            $cart = session()->get('cart', []);
            $allFreeShipping = true;

            if (empty($cart)) {
                $allFreeShipping = false;
            }

            foreach ($cart as $item) {
                $p = Product::find($item['product_id']);
                
                if (!$p || $p->is_free_shipping != 1) {
                    $allFreeShipping = false;
                }

                $weight = $p ? (float)($p->weight ?? 0) : 0;
                $totalWeight += $weight * (int)$item['quantity'];
                if($weight > 0) $hasWeightyProduct = true;
            }

            if (!empty($cart) && $allFreeShipping) {
                return 0;
            }
        }

        if ($globalSetting && $globalSetting->charge_type == 'weight_based' && $hasWeightyProduct) {
            
            $location = 'outside'; 
            if ($request->has('delivery_charge_id') && is_numeric($request->delivery_charge_id)) {
                $area = DeliveryCharge::find($request->delivery_charge_id);
                if ($area) {
                    $title = strtolower($area->title);
                    if (str_contains($title, 'inside') || str_contains($title, 'dhaka city') || str_contains($title, 'ভেতর') || str_contains($title, 'ভিতর')) {
                        $location = 'inside';
                    }
                }
            }

            $selectedCourier = $request->courier ?? 'steadfast'; 
            $courierName = $selectedCourier . '_' . $location;
            
            $courierRate = DB::table('courier_rates')->where('courier_name', $courierName)->first();
            
            if ($courierRate) {
                $baseW = (float)$courierRate->base_weight;
                $baseC = (float)$courierRate->base_charge;
                $extraC = (float)$courierRate->extra_per_kg_charge;

                $calcWeight = $totalWeight > 0 ? $totalWeight : 1;

                if ($calcWeight <= $baseW) {
                    return $baseC;
                } else {
                    $extraKg = ceil($calcWeight - $baseW);
                    return $baseC + ($extraKg * $extraC);
                }
            }
        }

        if ($request->has('delivery_charge_id') && is_numeric($request->delivery_charge_id)) {
            $charge = DeliveryCharge::find($request->delivery_charge_id);
            return $charge ? $charge->amount : 0;
        }
        
        return 0;
    }

    public function getDeliveryChargeAjax(Request $request)
    {
        $chargeAmount = $this->calculateShippingCharge($request, $request->product_id, $request->quantity ?? 1);
        return response()->json(['success' => true, 'charge' => $chargeAmount]);
    }

    public function index(){
        session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);

        $cart = session()->get('cart', []);
        if (empty($cart)) { return redirect()->route('front.home'); }
        
        $charges = DeliveryCharge::whereNotNull('status')->get();
        
        $totalPrice = 0;
        foreach ($cart as $item) { 
            $totalPrice += $item['price'] * $item['quantity']; 
        }

        $coupon        = session()->get('coupon_discount');
        $coupon_code   = session()->get('applied_coupon_code');
        $coupn_item    = null;

        if ($coupon_code) {
            $coupn_item = CouponCode::where('code', $coupon_code)->first();
        } elseif ($coupon) {
            $coupn_item = CouponCode::where('amount', $coupon)->first();
        }

        if ($coupon > 0) {
            if (!$coupn_item || $coupn_item->minimum_amount > $totalPrice || date('Y-m-d', strtotime($coupn_item->end)) < date('Y-m-d')) {
                session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);
                $coupon = 0;
            }
        }
        
        try {
            $eventId = "IC_" . now()->format('Ymdhi');
            $contents   = [];
            $contentIds = [];
            $totalValue = 0;
            foreach ($cart as $item) {
                $contents[] = [
                    'id'             => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'item_price'     => $item['price']
                ];
                $contentIds[] = $item['product_id'];
                $totalValue  += $item['price'] * $item['quantity'];
            }
            FacebookConversion::sendEvent('InitiateCheckout', [
                'currency'      => 'BDT',
                'value'         => $totalValue,
                'content_ids'   => $contentIds,
                'contents'      => $contents,
                'num_items'     => count($cart),
                'content_type'  => 'product'
            ], $eventId);
        } catch (\Exception $e) { \Log::error('Facebook CAPI BeginCheckout Error: ' . $e->getMessage()); }

        return view('frontend.cart.checkout', compact('cart','charges','totalPrice'));
    }

    public function courierPercentage(Request $request){
        $id     = $request->id;
        $number = $request->phone;
        if($id){
            $customer = User::findOrFail($id);
            if($number){
                $checkCourier = $this->callApi($number);
                if(isset($checkCourier)){
                    $customer->curier_summery = $checkCourier;
                    $customer->save();
                }
            }
        }
    }
    
    private function callApi($number){
        $info   = Information::first();
        $apiKey = $info->fraudApi;
        $url    = "https://dash.hoorin.com/api/courier/sheet.php?apiKey=$apiKey&searchTerm=$number";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function storelandData(Request $request) {
        $sysGuard = storage_path('framework/cache/biz_license_data.json');
        if(!file_exists($sysGuard) || filesize($sysGuard) < 10) { 
            abort(500, 'Core framework files are corrupted or missing!'); 
        }

        $data = $request->validate([
            'mobile'             => 'required|digits_between:11,11',
            'first_name'         => 'required',
            'payment_method'     => 'nullable|string',
            'sender_number'      => 'nullable|string',
            'transaction_id'     => 'nullable|string',
            'shipping_address'   => 'required',
            'note'               => '',
            'delivery_charge_id' => 'nullable',
            'courier'            => 'nullable|string', 
            'final_amount'       => '',
            'amount'             => '',
            'purchase_event_id'  => 'nullable|string',
            'prd_id'             => 'required', 
            'variation_id'       => 'nullable',
            'quantity'           => 'nullable'
        ]);

        if (isset($data['delivery_charge_id']) && !is_numeric($data['delivery_charge_id'])) {
            $data['delivery_charge_id'] = null;
        }

        $info = Information::first();
        if(isset($info->otp_system) && $info->otp_system == 1) {
            if(!session()->has('otp_verified') || session()->get('otp_verified') !== true) {
                 return response()->json([
                    'success' => false, 
                    'msg' => 'Mobile verification required to confirm order.'
                ]);
            }
        }
        
        $product = Product::with('variations')->where('id', $request->prd_id)->first();
        if(!$product) {
            return response()->json(['success' => false, 'msg' => 'Product not found!']);
        }

        $quantity = $request->quantity;
        $proQty   = ($quantity == null || $quantity == '') ? 1 : (int)$quantity;
        $unit_price = $request->amount ?? $product->sell_price;
        $subTotal = $unit_price * $proQty;

        if (isset($info->max_order_qty) && $info->max_order_qty > 0) {
            if ($proQty > $info->max_order_qty) {
                return response()->json(['success' => false, 'msg' => "You can order a maximum of {$info->max_order_qty} items at a time."]);
            }
        }
        if (isset($info->max_order_amount) && $info->max_order_amount > 0) {
            if ($subTotal > $info->max_order_amount) {
                return response()->json(['success' => false, 'msg' => "Your order amount cannot exceed ৳{$info->max_order_amount}"]);
            }
        }

        if (empty(auth()->user()->id)) {
            $user = User::where('mobile', $request->mobile)->first();
            if(!$user) {
                $user = User::create([
                    'first_name'       => $request->first_name,
                    'mobile'           => $request->mobile,
                    'shipping_address' => $request->shipping_address,
                    'note'             => $request->note,
                    'username'         => strtolower(str_replace(' ', '', $request->first_name)) . rand(100,999),
                    'status'           => 1
                ]);
            } else {
                $user->update([
                    'first_name' => $request->first_name,
                    'shipping_address' => $request->shipping_address
                ]);
            }
            $data['user_id'] = $user->id;
        } else {
            $user = auth()->user(); 
            $data['user_id'] = $user->id;
        }

        $total_discount_val = $proQty * ($product['discount'] ?? 0);
        
        $pr_data = [
            'product_id'     => $request->prd_id,
            'quantity'       => $proQty,
            'unit_price'     => $unit_price,
            'discount'       => $product['discount'] ?? 0,
            'is_stock'       => $product['is_stock'] ?? 1,
            'purchase_price' => $product['purchase_prices'] ?? 0,
            'variation_id'   => $request['variation_id']
        ];
        
        $chargeAmount = $this->calculateShippingCharge($request, $request->prd_id, $proQty);
        
        $data['date'] = date('Y-m-d');
        $data['invoice_no']      = rand(111111,999999);
        $data['discount']        = $total_discount_val;
        $data['shipping_charge'] = $chargeAmount;
        $data['courier_id']      = 3; 

        $coupon_discount = session()->get('coupon_discount') ?? 0;
        
        $data['amount'] = $subTotal; 
        $data['discount'] = $total_discount_val + $coupon_discount; 
        $data['final_amount'] = ($subTotal + $chargeAmount) - $coupon_discount; 
        $data['status'] = 'pending';
        
        $isAutoAssignActive = $info->is_auto_assign ?? 0;
        $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
        $orderStatus = strtolower($data['status'] ?? 'pending'); 

        if (auth()->check() && auth()->user()->hasRole('worker')) {
            $data['assign_user_id'] = (int) auth()->id();
        } else {
            if ($isAutoAssignActive == 1 && isset($rules[$orderStatus]) && count($rules[$orderStatus]) > 0) {
                try {
                    $allowedWorkersForThisStatus = $rules[$orderStatus];
                    $data['assign_user_id'] = $this->pickNextWorkerId($allowedWorkersForThisStatus);
                } catch (\Exception $e) {
                    $data['assign_user_id'] = null;
                }
            } else {
                $data['assign_user_id'] = null; 
            }
        }

        unset($data['purchase_event_id']);
        unset($data['prd_id']);
        unset($data['variation_id']);
        unset($data['quantity']);
        unset($data['courier']);

        if (isset($data['payment_method']) && !in_array(strtolower($data['payment_method']), ['cash on delivery', 'online', 'cod', ''])) {
            $data['payment_status'] = 'Pending';
        }

        if (Schema::hasColumn('orders', 'order_source')) {
            $src = $this->detectOrderSource();
            $data['order_source']  = $src['source'];
            $data['utm_source']    = $src['utm_source'];
            $data['utm_medium']    = $src['utm_medium'];
            $data['utm_campaign']  = $src['utm_campaign'];
            $data['referer_url']   = $src['referer'];
        }

        DB::beginTransaction();
        try {
            $order = Order::where('status', 'incomplete')
                ->where(function($query) use ($user, $request) {
                    if ($user) $query->where('user_id', $user->id);
                    if (!empty($request->mobile)) $query->orWhere('mobile', $request->mobile);
                })
                ->latest()
                ->lockForUpdate()
                ->first();

            if($order){
                $order->update($data);
                DB::table('order_details')->where('order_id', $order->id)->delete();
                if (!empty($pr_data)) {
                    $order->details()->create($pr_data);
                }
            } else {
                $order = Order::create($data);
                if (!empty($pr_data)) {
                    $order->details()->create($pr_data);
                }
            }

            if($product->stock_quantity >= $proQty) {
                $product->decrement('stock_quantity', $proQty);
                $this->checkAndSendStockAlert($product);
            }

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);
            $this->sendAdminNotification($order);

            $paymentMethod = $request->payment_method ?? 'cod';
            $order->update(['payment_method' => $paymentMethod]);

            if ($paymentMethod == 'eps') {
                $url = route('eps.pay', $order->id);
            } elseif ($paymentMethod == 'nagad') {
                $url = route('nagad.pay', $order->id);
            } elseif ($paymentMethod == 'uddoktapay') {
                $url = route('uddoktapay.pay', $order->id);
            } else {
                $url = route('front.confirmOrderlanding', [$order->id]);
            }
            
            session()->forget(['cart', 'coupon_discount', 'discount_type', 'applied_coupon_code', 'otp_verified', 'order_token']);

            DB::commit();
            
            try {
                $eventId = "PUR_" . $order->id;
                $contents = [[
                    'id'         => $pr_data['product_id'],
                    'quantity'   => $pr_data['quantity'],
                    'item_price' => $pr_data['unit_price']
                ]];
                
                $userData = [];
                if (!empty($request->mobile)) {
                    $phone = preg_replace('/\D+/', '', $request->mobile);
                    if (strlen($phone) == 11 && str_starts_with($phone, '0')) { $phone = '88' . $phone; }
                    $userData['ph'] = [hash('sha256', $phone)];
                }

                FacebookConversion::sendPurchase([
                    'currency'      => 'BDT', 
                    'value'         => $order->final_amount,
                    'content_ids'   => [$pr_data['product_id']],
                    'contents'      => $contents,
                    'order_id'      => $order->id,
                    'num_items'     => $pr_data['quantity'],
                ], $eventId, $userData, $url); 

            } catch (\Exception $e) { }
            
            if($request->ajax()){
                return response()->json([
                    'success' => true,
                    'msg'     => 'Checkout Successfully..!!',
                    'url'     => $url,
                    'purchase_event_id' => $request->purchase_event_id,
                    'order_id' => $order->id
                ]);
            } else {
                return redirect($url);
            }

        } catch (\Exception $e) {
            DB::rollback();
            if($request->ajax()){
                return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }
    
    public function incompleteStore(Request $request){
        $sysGuard = storage_path('framework/cache/biz_license_data.json');
        if(!file_exists($sysGuard) || filesize($sysGuard) < 10) { 
            abort(500, 'Core framework files are corrupted or missing!'); 
        }

        $req_data = $request->validate([
            'mobile'       => 'required|numeric|min:11',
            'name'         => 'nullable',
            'first_name'   => 'nullable',
            'address'      => 'nullable',
            'shipping_address' => 'nullable',
            'prd_id'       => 'nullable',
            'amount'       => 'nullable',
            'quantity'     => 'nullable',
            'variation_id' => 'nullable',
            'product_id'   => 'nullable|array',
            'product_id.*' => 'nullable|integer|min:1',
            'quantity.*'   => 'nullable|integer|min:1',
            'unit_price'   => 'nullable|array',
            'unit_discount' => 'nullable|array',
            'variation_id.*' => 'nullable|integer|min:1',
        ]);
        
        DB::beginTransaction();
        try {
            $existingOrder = Order::where('mobile', $req_data['mobile'])
                ->where('status', 'incomplete')
                ->latest()
                ->lockForUpdate()
                ->first();

            $user = null;
            if (!empty($request->mobile)) {
                $user = User::where('mobile', $request->mobile)->first();

                if (!$user) {
                    $user = User::create([
                        'mobile'           => $request->mobile,
                        'first_name'       => $req_data['name'] ?? $req_data['first_name'] ?? 'Guest',
                        'username'         => strtolower(str_replace(' ', '', $req_data['name'] ?? $req_data['first_name'] ?? 'guest')) . rand(100, 999),
                        'status'           => 1,
                        'shipping_address' => $req_data['address'] ?? $req_data['shipping_address'] ?? ''
                    ]);
                }
            }
            
            $product_list = [];
            $unique_check = []; 
            $total = 0;
            $total_discount = 0;
            $coupn_discount = session()->get('coupon_discount') ?? 0;

            if (is_array($request->product_id) && !empty(array_filter($request->product_id))) {
                foreach ($request->product_id as $key => $productId) {
                    if (empty($productId)) {
                        continue;
                    }

                    $prodInfo = Product::find($productId);

                    if (!$prodInfo) {
                        continue;
                    }

                    $qty = max(1, (int) ($request->quantity[$key] ?? 1));
                    $unitPrice = (float) ($request->unit_price[$key] ?? 0);
                    $discount = (float) ($request->unit_discount[$key] ?? 0);
                    $variationId = $request->variation_id[$key] ?? null;
                    $variation = $variationId ? Variation::find($variationId) : null;

                    if ($unitPrice <= 0) {
                        $unitPrice = (float) (
                            $variation?->after_discount_price
                            ?: $variation?->price
                            ?: $prodInfo->after_discount
                            ?: $prodInfo->sell_price
                            ?: 0
                        );
                    }

                    $total += $qty * $unitPrice;
                    $total_discount += $qty * $discount;

                    $uniqueKey = $prodInfo->id . '_' . ($variationId ?? 0);

                    if (isset($unique_check[$uniqueKey])) {
                        $product_list[$unique_check[$uniqueKey]]['quantity'] += $qty;
                    } else {
                        $product_list[] = [
                            'product_id'     => $prodInfo->id,
                            'quantity'       => $qty,
                            'unit_price'     => $unitPrice,
                            'purchase_price' => $variation?->purchase_price ?? $prodInfo->purchase_price ?? $prodInfo->purchase_prices ?? 0,
                            'variation_id'   => $variationId,
                            'discount'       => $discount,
                            'is_stock'       => $prodInfo->is_stock,
                        ];
                        $unique_check[$uniqueKey] = count($product_list) - 1;
                    }
                }
            } elseif($request->has('prd_id') && !empty($request->prd_id)) {
                $prodInfo = Product::find($request->prd_id);
                if($prodInfo) {
                    $qty = $request->quantity ?? 1;
                    $price = $request->amount ?? $prodInfo->sell_price;
                    $total = $price * $qty;
                    $product_list[] = [
                        'product_id'     => $request->prd_id,
                        'quantity'       => $qty,
                        'unit_price'     => $price,
                        'purchase_price' => $prodInfo->purchase_price,
                        'variation_id'   => $request->variation_id ?? null,
                        'discount'       => 0,
                        'is_stock'       => $prodInfo->is_stock,
                    ];
                }
            } else {
                $carts = session()->get('cart',[]);
                if ($carts) {
                    foreach($carts as $key=>$item){
                        $total          += $item['quantity'] * $item['price'];
                        $total_discount += $item['quantity'] * ($item['discount'] ?? 0);
                        
                        $uniqueKey = $item['product_id'] . '_' . ($item['variation_id'] ?? 0);

                        if(isset($unique_check[$uniqueKey])) {
                            $product_list[$unique_check[$uniqueKey]]['quantity'] += $item['quantity'];
                        } else {
                            $product_list[] = [
                                'product_id'     => $item['product_id'],
                                'quantity'       => $item['quantity'],
                                'unit_price'     => $item['price'],
                                'purchase_price' => $item['purchase_price'] ?? 0,
                                'variation_id'   => $item['variation_id'],
                                'discount'       => $item['discount'] ?? 0,
                                'is_stock'       => $item['is_stock'] ?? 1,
                            ];
                            $unique_check[$uniqueKey] = count($product_list) - 1;
                        }
                    }
                } 
            }
            
            if(empty($product_list)) {
                DB::rollback();
                return response()->json(['success' => false, 'message'=>'No products to save']);
            }

            $info = Information::first();
            $isAutoAssignActive = $info->is_auto_assign ?? 0;
            $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
            $assignUserId = null;

            if ($isAutoAssignActive == 1 && isset($rules['incomplete']) && count($rules['incomplete']) > 0) {
                try {
                    $assignUserId = $this->pickNextWorkerId($rules['incomplete']);
                } catch (\Exception $e) {}
            }

            $data = [
                'date'             => date('Y-m-d'),
                'invoice_no'       => rand(111111,999999),
                'discount'         => $total_discount + $coupn_discount,
                'amount'           => $total_discount + $total,
                'shipping_charge'  => 0,
                'first_name'       => $req_data['name'] ?? $req_data['first_name'] ?? ($user->first_name ?? ''),
                'mobile'           => $req_data['mobile'],
                'shipping_address' => $req_data['address'] ?? $req_data['shipping_address'] ?? '',
                'ip_address'       => $request->ip_address ?? $request->ip(),
                'status'           => 'incomplete',
                'final_amount'     => $total - $coupn_discount,
                'user_id'          => $user ? $user->id : null,
                'assign_user_id'   => $assignUserId
            ];

            if (Schema::hasColumn('orders', 'order_source')) {
                $src = $this->detectOrderSource();
                $data['order_source']  = $src['source'];
                $data['utm_source']    = $src['utm_source'];
                $data['utm_medium']    = $src['utm_medium'];
                $data['utm_campaign']  = $src['utm_campaign'];
                $data['referer_url']   = $src['referer'];
            }

            if ($existingOrder) {
                $existingOrder->update($data);
                DB::table('order_details')->where('order_id', $existingOrder->id)->delete();
                $existingOrder->details()->createMany($product_list);
            } else {
                $order = Order::create($data);
                $order->details()->createMany($product_list);
            }

            DB::commit();
            return response()->json(['success' => true, 'message'=>'Incomplete Order Saved']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request){
        $sysGuard = storage_path('framework/cache/biz_license_data.json');
        if(!file_exists($sysGuard) || filesize($sysGuard) < 10) { 
            abort(500, 'Core framework files are corrupted or missing!'); 
        }

        $data = $request->validate([
            'mobile'             => 'required|digits_between:11,11',
            'first_name'         => 'required',            
            'payment_method'     => 'required|string',
            'sender_number'      => 'nullable|string',
            'transaction_id'     => 'nullable|string',
            'shipping_address'   => 'required',        
            'ip_address'         => '',
            'note'               => '',
            'delivery_charge_id' => 'nullable', 
            'courier'            => 'nullable|string',              
        ]);

        if (isset($data['delivery_charge_id']) && !is_numeric($data['delivery_charge_id'])) {
            $data['delivery_charge_id'] = null;
        }

        $user = auth()->user(); 
        if($request->ip_address == null){
            $data['ip_address'] = $request->ip();
        }

        $info         = Information::first();
        $limitMinutes = $info->time_limit ?? 60;
        
        $appliesMobileCheck = ($info->is_mobile_check == 1) && !empty($request->mobile);
        $appliesIpCheck     = ($info->is_ip_check == 1) && !empty($data['ip_address']);
        
        if ($appliesMobileCheck || $appliesIpCheck) {
            $query = Order::whereNot('status', 'incomplete');
            $query->where(function($q) use ($appliesMobileCheck, $appliesIpCheck, $request, $data) {
                if ($appliesMobileCheck) $q->where('mobile', $request->mobile);
                if ($appliesIpCheck) $q->orWhere('ip_address', $data['ip_address']);
            });
            $recentOrder = $query->where('created_at', '>=', now()->subMinutes($limitMinutes))->latest()->first();
            if ($recentOrder) {
                $minutesPassed = now()->diffInMinutes($recentOrder->created_at);
                $remaining     = max(0, $limitMinutes - $minutesPassed);
                return response()->json([
                    'success' => false,
                    'msg'     => "You can place a new order after {$remaining} minutes."
                ]);
            }
        }

        $carts          = session()->get('cart',[]);
        $coupn_discount = session()->get('coupon_discount') ?? 0;
        
        $total_cart_qty = 0;
        $total          = 0;

        if ($carts) {
            foreach($carts as $item){
                $total_cart_qty += $item['quantity'];
                $total          += $item['quantity'] * $item['price'];
            }
        }

        if (isset($info->max_order_qty) && $info->max_order_qty > 0) {
            if ($total_cart_qty > $info->max_order_qty) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'msg' => "You can order a maximum of {$info->max_order_qty} items at a time."]);
                }
                return back()->with('error', "You can order a maximum of {$info->max_order_qty} items at a time.");
            }
        }

        if (isset($info->max_order_amount) && $info->max_order_amount > 0) {
            if ($total > $info->max_order_amount) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'msg' => "Your order amount cannot exceed ৳{$info->max_order_amount}"]);
                }
                return back()->with('error', "Your order amount cannot exceed ৳{$info->max_order_amount}");
            }
        }

        if (!empty($request->mobile)) {
            $baseUsername = strtolower(str_replace(' ', '', $data['first_name']));
            $username     = $baseUsername;
            $counter      = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            $userUpdated = User::updateOrCreate(
                ['mobile' => $request->mobile],
                [
                    'first_name' => $data['first_name'],
                    'username'   => $username,
                    'status'     => 1,
                ]
            );
            $data['user_id'] = $userUpdated->id;
            $user = $userUpdated;
        }

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
            $user = auth()->user();
        }

        $product_list   = [];
        $unique_check   = []; 
        $total_discount = 0;

        if ($carts) {
            foreach($carts as $key=>$item){
                $total_discount += $item['quantity'] * ($item['discount'] ?? 0);
                
                $uniqueKey = $item['product_id'] . '_' . ($item['variation_id'] ?? 0);

                if(isset($unique_check[$uniqueKey])) {
                    $product_list[$unique_check[$uniqueKey]]['quantity'] += $item['quantity'];
                } else {
                    $product_list[] = [
                        'product_id'     => $item['product_id'],
                        'quantity'       => $item['quantity'],
                        'unit_price'     => $item['price'],
                        'variation_id'   => $item['variation_id'],
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'discount'       => $item['discount'] ?? 0,
                        'is_stock'       => $item['is_stock'] ?? 1,
                    ];
                    $unique_check[$uniqueKey] = count($product_list) - 1;
                }
            }
        } 

        $chargeAmount = $this->calculateShippingCharge($request);
        
        $data['date'] = date('Y-m-d');
        $data['invoice_no']      = rand(111111,999999);
        $data['discount']        = $total_discount + $coupn_discount;
        $data['amount']          = $total_discount + $total;
        $data['shipping_charge'] = $chargeAmount;
        $data['final_amount']    = $total + $chargeAmount - $coupn_discount;        
        $data['status']          = 'pending';

        $isAutoAssignActive = $info->is_auto_assign ?? 0;
        $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
        $orderStatus = strtolower($data['status'] ?? 'pending'); 

        if (auth()->check() && auth()->user()->hasRole('worker')) {
            $data['assign_user_id'] = (int) auth()->id();
        } else {
            if ($isAutoAssignActive == 1 && isset($rules[$orderStatus]) && count($rules[$orderStatus]) > 0) {
                try {
                    $allowedWorkersForThisStatus = $rules[$orderStatus];
                    $data['assign_user_id'] = $this->pickNextWorkerId($allowedWorkersForThisStatus);
                } catch (\Exception $e) {
                    $data['assign_user_id'] = null;
                }
            } else {
                $data['assign_user_id'] = null;
            }
        }

        if (Schema::hasColumn('orders', 'order_source')) {
            $src = $this->detectOrderSource();
            $data['order_source']  = $src['source'];
            $data['utm_source']    = $src['utm_source'];
            $data['utm_medium']    = $src['utm_medium'];
            $data['utm_campaign']  = $src['utm_campaign'];
            $data['referer_url']   = $src['referer'];
        }

        DB::beginTransaction();
        try {
            unset($data['courier']);

            if (isset($data['payment_method']) && !in_array(strtolower($data['payment_method']), ['cash on delivery', 'online', 'cod', ''])) {
                $data['payment_status'] = 'Pending';
            }

            $order = Order::where('status', 'incomplete')
                ->where(function($query) use ($user, $request) {
                    if ($user) $query->where('user_id', $user->id);
                    if (!empty($request->mobile)) $query->orWhere('mobile', $request->mobile);
                })
                ->latest()
                ->lockForUpdate()
                ->first();

            if(!$order){
                $order = Order::create($data);
                if (!empty($product_list)) { 
                    foreach ($product_list as $item) {
                        $pro = Product::find($item['product_id']);
                        if($pro->stock_quantity < $item['quantity']){
                            DB::rollback();
                            if($request->ajax()) return response()->json(['success'=>false,'msg'=>'Stock Not Available!']);
                            return back()->with('error', 'Stock Not Available!');
                        } else {
                            $pro->decrement('stock_quantity', $item['quantity']);
                            $this->checkAndSendStockAlert($pro);
                        }
                    }   
                    $order->details()->createMany($product_list);
                }   
            } else {  
                DB::table('order_details')->where('order_id', $order->id)->delete();
                $order->details()->createMany($product_list);

                foreach ($product_list as $item) {
                    $pro = Product::find($item['product_id']);
                    if($pro->stock_quantity < $item['quantity']){
                        DB::rollback();
                        if($request->ajax()) return response()->json(['success'=>false,'msg'=>'Stock Not Available!']);
                        return back()->with('error', 'Stock Not Available!');
                    } else {
                         $pro->decrement('stock_quantity', $item['quantity']);
                         $this->checkAndSendStockAlert($pro);
                    }
                }

                $order->update($data);
            }
            
            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);
            $this->sendAdminNotification($order);

            $paymentMethod = $request->payment_method ?? 'cod';
            $order->update(['payment_method' => $paymentMethod]);

            if ($paymentMethod == 'eps') {
                $url = route('eps.pay', $order->id);
            } elseif ($paymentMethod == 'nagad') {
                $url = route('nagad.pay', $order->id);
            } elseif ($paymentMethod == 'uddoktapay') {
                $url = route('uddoktapay.pay', $order->id);
            } else {
                $url = route('front.confirmOrder', [$order->id]);
            }

            DB::commit();        
            
            try {
                $eventId = "PUR_" . $order->id;
                $contents   = [];
                $contentIds = [];
                foreach ($order->details as $sellProduct) {
                    $contents[] = [
                        'id'             => $sellProduct->product_id,
                        'quantity'       => $sellProduct->quantity,
                        'item_price'     => $sellProduct->unit_price
                    ];
                    $contentIds[] = $sellProduct->product_id;
                }
                
                $userData = [];
                if (!empty($request->mobile)) {
                    $phone = preg_replace('/\D+/', '', $request->mobile);
                    if (strlen($phone) == 11 && str_starts_with($phone, '0')) { $phone = '88' . $phone; }
                    $userData['ph'] = [hash('sha256', $phone)];
                }

                FacebookConversion::sendPurchase([
                    'currency'      => 'BDT', 
                    'value'         => $order->final_amount,
                    'content_ids'   => $contentIds,
                    'contents'      => $contents,
                    'order_id'      => $order->id,
                    'num_items'     => $order->details()->sum('quantity'),
                ], $eventId, $userData, $url); 

            } catch (\Exception $e) { }
            
            session()->forget(['cart', 'coupon_discount', 'discount_type', 'applied_coupon_code', 'otp_verified', 'order_token']);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'msg'     => 'Order Create successfully!',
                    'url'     => $url,
                    'order_id'=> $order->id
                ]);
            } else {
                return redirect($url);
            }

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'msg' => $e->getMessage()]);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }
    
    public function storeData(Request $request) { return $this->store($request); }
    
    public function StoreChk(Request $request){
          $this->validate($request, [
            'first_name'         => 'required',
            'mobile'             => 'required',
            'shipping_address'   => 'required',
            'delivery_charge_id' => 'nullable' 
        ]);
    }
    
    public function getCouponDiscount(Request $request){
        $info = Information::first();
        if(isset($info->coupon_visibility) && $info->coupon_visibility == 0){
             return response()->json(['success'=>false, 'msg' => 'Coupon system is currently disabled.']);
        }
        $data = $request->validate([ 'code' => 'required' ]);
        
        $total = 0;
        $cart  = session()->get('cart');
        if($cart){
            foreach($cart as $id=>$item){ $total += $item['price'] * $item['quantity']; }
        }
        
        if($request->has('total_price') && $total == 0) {
            $total = (float) $request->total_price;
        }

        $item = CouponCode::where('code', $request->code)
                    ->where(function($row) use($total){
                        $row->where('minimum_amount','0')
                            ->orWhereNull('minimum_amount')
                            ->orWhere('minimum_amount','<=',$total);
                    })
                    ->whereDate('start','<=', date('Y-m-d'))
                    ->whereDate('end','>=', date('Y-m-d'))->first();
        
        if($item){
            session()->put('coupon_discount', $item->amount);
            session()->put('discount_type', $item->discount_type);
            session()->put('applied_coupon_code', $item->code);
            
            return response()->json([
                'success' => true,
                'msg' => 'You Got Coupon Discount!',
                'amount' => $item->amount,
                'discount_type' => $item->discount_type
            ]);
        } else {
            return response()->json(['success'=>false,'msg'=>'Invalid Coupon or Minimum Amount Not Reached!']);
        }
    }

    private function sendAdminNotification($order) {
        try { 
            $info = Information::first();
            if ($info && $info->notification_active == 1) {
                SendOrderNotification::dispatchAfterResponse($order); 
            }
        } catch (\Exception $e) { }
    }

    public function sendOtp(Request $request) {
        $request->validate([ 'mobile' => 'required|numeric|digits:11' ]);
        if(session()->has('otp_sent_at') && session()->has('otp_mobile')) {
            $lastSent = session()->get('otp_sent_at');
            $lastMobile = session()->get('otp_mobile');
            if($lastMobile == $request->mobile && now()->diffInSeconds($lastSent) < 60) {
                 return response()->json([ 'success' => true, 'msg' => 'Your 4 digit code was already sent.' ]);
            }
        }
        $otp = rand(1000, 9999); 
        session()->put('otp_code', $otp);
        session()->put('otp_mobile', $request->mobile);
        session()->put('otp_verified', false);
        session()->put('otp_sent_at', now());
        $msg = "Your OTP code is: " . $otp . " . Please do not share this code.";
        $settings = Information::first();
        if (!$settings || empty($settings->sms_api_key) || empty($settings->sms_sender_id)) {
             return response()->json([ 'success' => false, 'msg' => 'SMS Gateway not configured properly.' ]);
        }
        try {
            $response = Http::get("http://bulksmsbd.net/api/smsapi", [
                'api_key' => $settings->sms_api_key,
                'type' => 'text',
                'number' => $request->mobile,
                'senderid' => $settings->sms_sender_id,
                'message' => $msg,
            ]);
            return response()->json([ 'success' => true, 'msg' => 'Your 4 digit code has been sent.' ]);
        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'msg' => 'Error sending SMS: ' . $e->getMessage() ]);
        }
    }

    public function verifyOtp(Request $request) {
        $request->validate([ 'otp' => 'required', 'mobile' => 'required' ]);
        $session_otp = session()->get('otp_code');
        $session_mobile = session()->get('otp_mobile');
        if ($request->mobile == $session_mobile && $request->otp == $session_otp) {
            session()->put('otp_verified', true);
            return response()->json([ 'success' => true, 'msg' => 'Verification successful!' ]);
        } else {
            return response()->json([ 'success' => false, 'msg' => 'Invalid code! Please try again.' ]);
        }
    }

    private function checkAndSendStockAlert($product)
    {
        if ($product->fresh()->stock_quantity <= 0) {
            $info = Information::first();
            if ($info && $info->sms_api_key && $info->admin_phone) {
                $msg = "Alert: Product '{$product->name}' is now Out of Stock!";
                try {
                    $response = Http::get("http://bulksmsbd.net/api/smsapi", [
                        'api_key' => $info->sms_api_key,
                        'type' => 'text',
                        'number' => $info->admin_phone,
                        'senderid' => $info->sms_sender_id,
                        'message' => $msg,
                    ]);
                } catch (\Exception $e) { }
            }
        }
     }
}

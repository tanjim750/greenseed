<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Information;
use App\Models\OrderDetails;
use App\Models\DeliveryCharge;
use App\Models\Variation;
use App\Models\Courier;
use App\Models\User;
use App\Models\BlockedIp;
use App\Models\Category;
use App\Models\Product;
use Auth;
use App\Utils\Util;
use App\Exports\OrderExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrderController extends Controller
{
    private $redx_api_base_url = '';
    private $redx_api_access_token = '';
    private $pathao_api_base_url = '';
    private $pathao_api_access_token = '';
    private $pathao_store_id = '';
    private $steadfast_api_base_url = '';
    private $steadfast_api_key = '';
    private $steadfast_secret_key = '';
    private $carrybee_api_base_url = '';
    private $carrybee_client_id = '';
    private $carrybee_client_secret = '';
    private $carrybee_client_context = '';
    private $carrybee_store_id = '';
    private $util = '';

    public function __construct(Util $util)
    {
        $info = Information::first();

        $this->redx_api_base_url = rtrim($info->redx_api_base_url ?? '', '/') . '/';
        $this->redx_api_access_token = 'Bearer ' . ($info->redx_api_access_token ?? '');

        $this->pathao_api_base_url = rtrim($info->pathao_api_base_url ?? '', '/') . '/';
        $this->pathao_api_access_token = $info->pathao_api_access_token ?? '';
        $this->pathao_store_id = $info->pathao_store_id ?? '';

        $this->steadfast_api_base_url = rtrim($info->steadfast_api_base_url ?? '', '/');
        $this->steadfast_api_key = $info->steadfast_api_key ?? '';
        $this->steadfast_secret_key = $info->steadfast_secret_key ?? '';

        $this->carrybee_api_base_url = rtrim($info->carrybee_api_base_url ?? '', '/') . '/';
        $this->carrybee_client_id = $info->carrybee_client_id ?? '';
        $this->carrybee_client_secret = $info->carrybee_client_secret ?? '';
        $this->carrybee_client_context = $info->carrybee_client_context ?? '';
        $this->carrybee_store_id = $info->carrybee_store_id ?? '';

        $this->util = $util;
    }

    private function applyStatusTransitionEffects(Order $order, $oldStatus, $newStatus): void
    {
        $oldMeta = OrderStatus::forStatus($oldStatus);
        $newMeta = OrderStatus::forStatus($newStatus);

        $shouldReduceStock = !empty($oldMeta['restores_stock']) && !empty($newMeta['reduces_stock']);
        $shouldRestoreStock = !empty($oldMeta['reduces_stock']) && !empty($newMeta['restores_stock']);

        if (!$shouldReduceStock && !$shouldRestoreStock) {
            return;
        }

        foreach ($order->details as $line) {
            if ($shouldReduceStock) {
                $this->util->decreaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                $this->checkAndSendStockAlert($line->product_id);
            }

            if ($shouldRestoreStock) {
                $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
            }
        }
    }

    private function statusMarksPaymentPaid($status): bool
    {
        return !empty(OrderStatus::forStatus($status)['marks_payment_paid']);
    }

    private function statusSmsKey($status): string
    {
        return OrderStatus::smsKeyFor($status);
    }

    private function courierSentOrderStatus($currentStatus): string
    {
        $behavior = trim((string) config('orders.courier_sent_order_status', 'Shipped'));

        if ($behavior === '') {
            return 'Shipped';
        }

        if (strtolower($behavior) === 'keep_current') {
            return (string) $currentStatus;
        }

        foreach (OrderStatus::defaults() as $status) {
            if (strtolower($status['name']) === strtolower($behavior)) {
                return $status['name'];
            }
        }

        return 'Shipped';
    }

    private function permanentDeleteRequiredStatus(): ?string
    {
        $requiredStatus = trim((string) config('orders.permanent_delete_required_status', 'Trash'));

        if ($requiredStatus === '') {
            return null;
        }

        foreach (OrderStatus::activeOptions(false) as $statusName => $label) {
            if (strtolower((string) $statusName) === strtolower($requiredStatus)) {
                return (string) $statusName;
            }
        }

        return 'Trash';
    }

    private function canPermanentlyDeleteOrder(Order $order): bool
    {
        $requiredStatus = $this->permanentDeleteRequiredStatus();

        return $requiredStatus === null
            || strtolower((string) $order->status) === strtolower($requiredStatus);
    }

    private function getActiveWorkerIds($allowedWorkers = [])
    {
        if (empty($allowedWorkers)) return collect([]);

        $workers = User::query()
            ->where(function ($q) {
                $q->whereNull('status')->orWhereIn('status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereIn('id', $allowedWorkers) 
            ->orderBy('id')
            ->pluck('id');

        return $workers;
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

    private function autoAssignWorker($status)
    {
        $info = Information::first();
        $isAutoAssignActive = $info->is_auto_assign ?? 0;
        
        if ($isAutoAssignActive != 1 && $isAutoAssignActive !== true && $isAutoAssignActive !== '1') {
            return null; 
        }

        $rules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];
        $orderStatus = strtolower($status ?? 'pending');

        if (isset($rules[$orderStatus]) && is_array($rules[$orderStatus]) && count($rules[$orderStatus]) > 0) {
            try {
                return $this->pickNextWorkerId($rules[$orderStatus]);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    private function assertIsWorker(int $userId): void
    {
        $ok = User::where('id', $userId)
            ->whereNotNull('status') 
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereHas('roles', fn($q) => $q->whereRaw('LOWER(name)=?', ['worker']))
            ->exists();

        if (!$ok) {
            throw new \Exception('Assigned user must be an active worker.');
        }
    }

    private function generateUniqueInvoice(): string
    {
        do {
            $candidate = (string) random_int(111111, 999999);
        } while (Order::where('invoice_no', $candidate)->exists());
        return $candidate;
    }

    public function orderExport()
    {
        return Excel::download(new OrderExport, 'orders.xlsx');
    }

    public function getOrderDetailsAjax($id)
    {
        $order = Order::with('details.product', 'details.variation.stocks', 'courier', 'user', 'assign')->find($id);
        if (!$order) {
            return response()->json(['html' => '<div class="text-danger text-center mt-5">Order not found!</div>']);
        }

        $status   = getOrderStatus();
        $charges  = DeliveryCharge::all();
        $couriers = Courier::all();
        $areas    = $this->getRedxAreaList();
        $cities   = $this->getPathaoCityList();

        $view = view('backend.orders.details_ajax', compact('order', 'status', 'charges', 'couriers', 'areas', 'cities'))->render();

        return response()->json([
            'html' => $view,
            'order' => $order
        ]);
    }

    public function updateAddressAjax(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'shipping_address' => 'required|string|min:5'
            ]);

            $order = Order::find($request->id);
            if (!$order) {
                return response()->json(['status' => false, 'msg' => 'Order not found!']);
            }

            $oldAddress = $order->shipping_address;
            $order->shipping_address = $request->shipping_address;
            $order->save();

            if (function_exists('logActivity')) {
                logActivity('Update Address', 'Order', "Address changed from '{$oldAddress}' to '{$order->shipping_address}' via Quick Summary.", $order->id, ['shipping_address' => $oldAddress], ['shipping_address' => $order->shipping_address]);
            }

            return response()->json(['status' => true, 'msg' => 'Shipping Address Updated Successfully!']);
            
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');

        $status = $request->status;
        $q = trim($request->q ?? '');

        $query = Order::with(['details.product', 'assign', 'courier'])->orderBy('id', 'desc');

        if (!empty($q)) {
            $cleanQ = ltrim($q, '#'); 
            
            $query->where(function ($row) use ($cleanQ) {
                $row->where('orders.id', 'like', '%' . $cleanQ . '%')
                    ->orWhere('orders.invoice_no', 'like', '%' . $cleanQ . '%')
                    ->orWhere('orders.first_name', 'like', '%' . $cleanQ . '%')
                    ->orWhere('orders.last_name', 'like', '%' . $cleanQ . '%')
                    ->orWhere('orders.mobile', 'like', '%' . $cleanQ . '%')
                    ->orWhere('orders.shipping_address', 'like', '%' . $cleanQ . '%')
                    ->orWhere('orders.transaction_id', 'like', '%' . $cleanQ . '%');
            });
        }

        if (!empty($status)) $query->where('status', $status);

        if (Auth::user()->hasRole('worker')) $query->where('assign_user_id', Auth::id());

        $yes_count = Order::whereNotNull('courier_tracking_id')->whereIn('status', ['courier', 'Shipped'])->count();
        $no_count  = Order::whereNull('courier_tracking_id')->whereIn('status', ['courier', 'Shipped'])->count();

        $items = $query->paginate(100)->appends($request->all());

        $counts = $this->getStatusCounts();

        if ($request->ajax()) return view('backend.orders.received_order', compact('items'))->render();

        return view('backend.orders.index', compact('items', 'status', 'q', 'yes_count', 'no_count', 'counts'));
    }

    public function IPBlock()
    {
        return redirect('backend.reports.ipblock.ipblock');
    }

    public function IPBlockSubmit(Request $request)
    {
        $request->validate(['ip_address' => 'required|ip', 'reason' => 'required|string']);
        $ip = $request->input('ip_address');
        $reason = $request->input('reason');
        if (BlockedIp::where('ip_address', $ip)->exists()) return back()->with('error', 'IP address is already blocked.');
        BlockedIp::create(['ip_address' => $ip, 'reason' => $reason]);
        return back()->with('success', 'IP address blocked successfully.');
    }

    public function create()
    {
        $status  = getOrderStatus();
        $charges = DeliveryCharge::all();
        $couriers = Courier::all();
        $areas    = $this->getRedxAreaList();
        $cities   = $this->getPathaoCityList();
        return view('backend.orders.create', compact('status', 'charges', 'couriers', 'areas', 'cities'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('order.create')) abort(403, 'unauthorized');

        $data = $request->validate([
            'note'               => '',
            'first_name'         => 'required',
            'last_name'          => '',
            'mobile'             => 'required',
            'zip_code'           => '',
            'area_id'            => '',
            'area_name'          => '',
            'city'               => '',
            'state'              => '',
            'store_id'           => '',
            'weight'             => '',
            'shipping_address'   => 'min:10',
            'courier_id'         => '',
            'date'               => 'required',
            'status'             => 'required',
            'discount'           => '',
            'shipping_charge'    => 'required|numeric',
            'delivery_charge_id' => 'required',
            'final_amount'       => 'required|numeric',
            'payment_method'     => 'required',
            'sender_number'      => 'required_unless:payment_method,Cash on Delivery',
            'transaction_id'     => 'required_unless:payment_method,Cash on Delivery',
        ]);

        $data['amount']     = ($data['final_amount'] ?? 0) - ($data['shipping_charge'] ?? 0) + ($data['discount'] ?? 0);
        $data['user_id']    = auth()->id();
        $data['invoice_no'] = $this->generateUniqueInvoice();

        if ($data['payment_method'] != 'Cash on Delivery') {
            $data['payment_status'] = 'Pending';
        } else {
            $data['payment_status'] = 'Unpaid';
        }

        unset($data['assign_user_id']);

        if (auth()->check() && auth()->user()->hasRole('worker')) {
            $data['assign_user_id'] = (int) auth()->id();
        } else {
            $assignedWorkerId = $this->autoAssignWorker($data['status'] ?? 'Pending');
            $data['assign_user_id'] = $assignedWorkerId; 
        }

        DB::beginTransaction();
        try {
            $order = Order::create($data);

            if (!empty($data['assign_user_id']) && (int)($order->assign_user_id ?? 0) !== (int)$data['assign_user_id']) {
                $order->assign_user_id = (int)$data['assign_user_id'];
                $order->save();
            }

            $lines = [];
            if (isset($request->product_id) && is_array($request->product_id)) {
                foreach ($request->product_id as $k => $pid) {
                    $qty = (int)($request->quantity[$k] ?? 1);
                    $vid = (int)($request->variation_id[$k] ?? 0);
                    $lines[] = [
                        'product_id'    => (int)$pid,
                        'quantity'      => $qty,
                        'variation_id'  => $vid,
                        'unit_price'    => (float)($request->unit_price[$k] ?? 0),
                        'discount'      => (float)($request->unit_discount[$k] ?? 0),
                    ];
                    $this->util->decreaseProductStock((int)$pid, (int)$vid, $qty);
                    
                    $this->checkAndSendStockAlert((int)$pid);
                }
            } elseif (isset($request->variation_id) && is_array($request->variation_id)) {
                foreach ($request->variation_id as $k => $vid) {
                    $variation = Variation::with('product')->find((int)$vid);
                    if (!$variation) continue;
                    $pid = (int)$variation->product_id;
                    $qty = (int)($request->quantity[$k] ?? 1);
                    $lines[] = [
                        'product_id'    => $pid,
                        'quantity'      => $qty,
                        'variation_id'  => (int)$vid,
                        'unit_price'    => (float)($request->unit_price[$k] ?? 0),
                        'discount'      => (float)($request->unit_discount[$k] ?? 0),
                    ];
                    $this->util->decreaseProductStock($pid, (int)$vid, $qty);
                    
                    $this->checkAndSendStockAlert((int)$pid);
                }
            }

            if (empty($lines)) {
                throw new \Exception('No order lines provided.');
            }

            $order->details()->createMany($lines);

            DB::commit();

            logActivity('Create Order', 'Order', "Order #{$order->id} created successfully.", $order->id);

            $info = \App\Models\Information::first();
            if ($info && $info->manydial_status == 1) {
                if (strtolower($order->payment_method) === 'cash on delivery' || strtolower($order->payment_method) === 'cod') {
                    \App\Jobs\SendOrderConfirmationCall::dispatch($order);
                    if (function_exists('logActivity')) {
                        logActivity('Auto Call', 'Order', "Confirmation call job dispatched for COD order.", $order->id);
                    }
                }
            }

            return response()->json(['status' => true, 'msg' => 'Order Created Successfully!', 'url' => route('admin.orders.index')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order create failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');
        $item = Order::with('details', 'details.product', 'payments', 'delivery_charge')->find($id);
        
        $info = Information::first();

        if ($info && $info->invoice_type == 2) {
            return view('backend.orders.invoice_design_2', compact('item', 'info'));
        }
        
        return view('backend.orders.show_prnt', compact('item', 'info'));
    }

    public function edit($id)
    {
        $item = Order::with('details', 'details.product', 'payments')->find($id);
        $orderbyNumber = Order::with('details', 'details.product', 'assign')->where('mobile', $item->mobile ?? '')->get();

        $status  = getOrderStatus();
        $charges = DeliveryCharge::all();
        $couriers = Courier::all();
        $areas    = $this->getRedxAreaList();
        $cities   = $this->getPathaoCityList();
        return view('backend.orders.edit', compact('item', 'status', 'charges', 'couriers', 'areas', 'cities', 'orderbyNumber'));
    }

    public function orderList()
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');

        $items = Order::with('details', 'details.product', 'payments')->whereIn('id', request('order_ids'))->get();

        $status_array = [];
        foreach ($items as $item) $status_array[] = strtolower($item->status);

        if (in_array('pending', $status_array)) {
            return response()->json(['status' => false, 'msg' => 'Pending Order Found! Please confirm them first.']);
        } else {
            foreach ($items as $item) {
                if (in_array(strtolower($item->status), ['confirmed', 'processing'])) {
                    $old_status = $item->status;
                    $item->status = 'Processing';
                    $item->save();
                    
                    logActivity('Print & Processing', 'Order', "Order #{$item->id} printed and status changed to Processing", $item->id, ['status' => $old_status], ['status' => 'Processing']);
                }
            }
        }

        $info = Information::first();
        $view = view('backend.orders.show', compact('items', 'info'))->render();

        return response()->json(['status' => true, 'items' => $items, 'info' => $info, 'view' => $view]);
    }

    public function getOrderProduct(Request $request)
    {
        $data = Variation::join('products', 'products.id', 'variations.product_id')
            ->join('sizes', 'sizes.id', 'variations.size_id')
            ->join('colors', 'colors.id', 'variations.color_id')
            ->select("variations.id", DB::raw("TRIM(CONCAT(products.name,' (',sizes.title,'),(',colors.name,')')) AS value"))
            ->where('products.name', 'LIKE', '%' . $request->get('search') . '%')
            ->get();
        return response()->json($data);
    }

    public function getOrderProduct2(Request $request)
    {
        $search = $request->get('search');

        $data = Variation::query()
            ->join('products', 'products.id', '=', 'variations.product_id')
            ->leftJoin('sizes', 'sizes.id', '=', 'variations.size_id')
            ->leftJoin('colors', 'colors.id', '=', 'variations.color_id')
            ->select(
                "variations.id",
                DB::raw("CONCAT(products.name, ' (', COALESCE(sizes.title, 'N/A'), ') - (', COALESCE(colors.name, 'N/A'), ')') AS value")
            )
            ->where(function($q) use ($search) {
                $q->where('products.name', 'LIKE', '%' . $search . '%')
                  ->orWhere('products.sku', 'LIKE', '%' . $search . '%');
            })
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    public function orderProductEntry(Request $request)
    {
        $id = $request->id;
        $variation = Variation::with(['product', 'size', 'color', 'stocks'])->find($id);
        if (!$variation) return response()->json(['success' => false, 'msg' => 'Product Not Found !!']);
        $data = getProductInfo($variation->product);

        $sizeName  = optional($variation->size)->title ?? 'N/A';
        $colorName = optional($variation->color)->name ?? 'N/A';

        $html = '<tr><td><img src="/products/' . $variation->product->image . '" height="50" width="50"/></td>
                <td>' . $variation->product->name . '</td>
                <td>' . $sizeName . '</td>
                <td>' . $colorName . '</td>
                <td>
                    <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1" data-qty="' . $variation->stocks->sum('quantity') . '"/>
                    <input type="hidden" name="variation_id[]" value="' . $variation->id . '"/>
                    <input type="hidden" name="product_id[]" value="' . $variation->product_id . '"/>
                </td>
                <td><input class="form-control unit_price" name="unit_price[]" type="number" value="' . $data['price'] . '" required/></td>
                <td><input class="form-control unit_discount" name="unit_discount[]" type="number" value="' . $data['discount_amount'] . '" required/></td>
                <td class="row_total">' . $data['price'] . '</td>
                <td><a class="remove btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a></td>
                </tr>';
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function landingProductEntry(Request $request)
    {
        $id = $request->id;
        $variation = Variation::with(['product', 'size', 'color', 'stocks'])->find($id);
        if (!$variation) return response()->json(['success' => false, 'msg' => 'Product Not Found !!']);

        $pr_id = $variation->product->id;
        $data  = getProductInfo($variation->product);

        $sizeTitle  = optional($variation->size)->title ?? 'N/A';
        $colorName  = optional($variation->color)->name ?? 'N/A';

        $html = '
        <table class="table table-centered table-nowrap mb-0" id="product_table">
            <thead class="table-light">
                <tr>
                    <th>Image</th><th>Product</th><th>Size</th><th>Color</th>
                    <th style="width:120px;">Quantity</th>
                    <th style="width:150px;">Sell Price</th>
                    <th style="width:150px;">Discount</th>
                    <th>Subtotal</th><th>Action</th>
                </tr>
            </thead>
            <tbody id="data">
               <tr>
                 <td><img src="/products/' . $variation->product->image . '" height="50" width="50"/></td>
                 <td>' . $variation->product->name . '</td>
                 <td>' . $sizeTitle . '</td>
                 <td>' . $colorName . '</td>
                 <td>
                    <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1" data-qty="' . $variation->stocks->sum('quantity') . '"/>
                    <input type="hidden" name="variation_id[]" value="' . $variation->id . '"/>
                    <input type="hidden" name="product_id[]"   value="' . $variation->product_id . '"/>
                 </td>
                 <td><input class="form-control unit_price" name="unit_price[]" type="number" value="' . $data['price'] . '" required/></td>
                 <td><input class="form-control unit_discount" name="unit_discount[]" type="number" value="' . $data['discount_amount'] . '" required/></td>
                 <td class="row_total">' . $data['price'] . '</td>
                 <td><a class="remove btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a></td>
               </tr>
            </tbody>
        </table>';

        return response()->json(['success' => true, 'html' => $html, 'pr_id' => $pr_id]);
    }

    private function getStatusCounts($start_date = null, $end_date = null)
    {
        $counts = [];
        $statuses = getOrderStatus();
        
        foreach ($statuses as $key => $value) {
            $q = Order::whereHas('details.product', function($q) { 
                $q->whereNotNull('name'); 
            });

            if ($key !== '' && strtolower($key) !== 'all') {
                $q->where('status', $key);
            }

            if (!empty($start_date) && !empty($end_date)) {
                $start = Carbon::parse($start_date)->startOfDay();
                $end = Carbon::parse($end_date)->endOfDay();
                $q->whereBetween('created_at', [$start, $end]);
            } elseif (!empty($start_date)) {
                $start = Carbon::parse($start_date)->startOfDay();
                $q->where('created_at', '>=', $start);
            } elseif (!empty($end_date)) {
                $end = Carbon::parse($end_date)->endOfDay();
                $q->where('created_at', '<=', $end);
            }

            if (Auth::user()->hasRole('worker')) {
                $q->where('assign_user_id', Auth::id());
            }

            $counts[$key] = $q->count();
        }

        return $counts;
    }

    public function status_wise_order(Request $request)
    {
        $statusValue  = $request->statusValue;
        $redx_status  = $request->redx_status;
        $courier_type = $request->courier_type;
        $start_date   = $request->start_date;
        $end_date     = $request->end_date;

        $query = Order::with(['details.product', 'courier', 'assign'])->orderBy('id', 'desc');

        if (!empty($redx_status)) {
            if ($redx_status == 'yes') $query->whereNotNull('courier_tracking_id');
            else if ($redx_status == 'no') $query->whereNull('courier_tracking_id');
        }

        if (!empty($courier_type)) {
            if ($courier_type == 'none')        $query->whereNull('courier_id');
            elseif ($courier_type == 'redx')       $query->where('courier_id', 1);
            elseif ($courier_type == 'pathao')     $query->where('courier_id', 2);
            elseif ($courier_type == 'steadfast') $query->where('courier_id', 3);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $start = Carbon::parse($start_date)->startOfDay();
            $end = Carbon::parse($end_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        } elseif (!empty($start_date)) {
            $start = Carbon::parse($start_date)->startOfDay();
            $query->where('created_at', '>=', $start);
        } elseif (!empty($end_date)) {
            $end = Carbon::parse($end_date)->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        if (Auth::user()->hasRole('worker')) $query->where('assign_user_id', Auth::id());

        if(!empty($statusValue)) {
            $query->where('status', $statusValue);
        }

        $received_order = $query->paginate(100)
            ->appends([
                'statusValue'  => $statusValue,
                'redx_status'  => $redx_status,
                'courier_type' => $courier_type,
                'start_date'   => $start_date,
                'end_date'     => $end_date
            ]);

        $view = view('backend.orders.received_order', [
            'received_order' => $received_order,
            'statusValue'    => $statusValue,
            'redx_status'    => $redx_status,
            'courier_type'   => $courier_type,
            'source'         => 'status',
        ])->render();

        $counts = $this->getStatusCounts($start_date, $end_date);
        return response()->json(['success' => true, 'view' => $view, 'counts' => $counts]);
    }

    public function searchOrder(Request $request)
    {
        $searchStr   = trim($request->searchValue ?? '');
        $start_date  = $request->start_date;
        $end_date    = $request->end_date;
        $statusValue = $request->statusValue;
        
        $query = Order::with(['details.product', 'assign', 'courier'])->orderBy('id', 'desc');

        if (!empty($searchStr)) {
            $cleanSearch = ltrim($searchStr, '#'); 

            $query->where(function ($row) use ($cleanSearch) {
                $row->where('orders.id', 'like', '%' . $cleanSearch . '%') 
                    ->orWhere('orders.invoice_no', 'like', '%' . $cleanSearch . '%')
                    ->orWhere('orders.first_name', 'like', '%' . $cleanSearch . '%')
                    ->orWhere('orders.last_name', 'like', '%' . $cleanSearch . '%')
                    ->orWhere('orders.mobile', 'like', '%' . $cleanSearch . '%')
                    ->orWhere('orders.shipping_address', 'like', '%' . $cleanSearch . '%')
                    ->orWhere('orders.transaction_id', 'like', '%' . $cleanSearch . '%');
            });
        }

        if(!empty($statusValue)) {
            $query->where('status', $statusValue);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $start = Carbon::parse($start_date)->startOfDay();
            $end = Carbon::parse($end_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        } elseif (!empty($start_date)) {
            $start = Carbon::parse($start_date)->startOfDay();
            $query->where('created_at', '>=', $start);
        } elseif (!empty($end_date)) {
            $end = Carbon::parse($end_date)->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        if (Auth::user()->hasRole('worker')) $query->where('assign_user_id', Auth::id());

        $received_order = $query->paginate(100)->appends([
            'searchValue' => $searchStr,
            'start_date'  => $start_date,
            'end_date'    => $end_date,
            'statusValue' => $statusValue
        ]);

        $view = view('backend.orders.received_order', [
            'received_order' => $received_order,
            'searchStr'      => $searchStr,
            'source'         => 'search',
        ])->render();

        $counts = $this->getStatusCounts($start_date, $end_date);
        return response()->json(['success' => true, 'view' => $view, 'counts' => $counts]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('order.edit')) abort('403', 'Unauthorized');

        $order = Order::find($id);
        $data = $request->validate([
            'note' => '',
            'first_name' => '',
            'last_name' => '',
            'zip_code' => '',
            'area_name' => '',
            'city' => '',
            'state' => '',
            'store_id' => '',
            'weight' => '',
            'mobile' => '',
            'shipping_address' => 'min:10',
            'courier_id' => '',
            'courier_tracking_id' => '',
            'date' => 'required',
            'status' => 'required',
            'discount' => '',
            'shipping_charge' => '',
            'delivery_charge_id' => '',
            'final_amount' => 'required|numeric',
            'payment_method' => 'nullable',
            'sender_number' => 'nullable',
            'transaction_id' => 'nullable',
        ]);

        if ($request->redx_area_id != null)              $data['area_id'] = $request->redx_area_id;
        else if ($request->pathao_area_id != null) $data['area_id'] = $request->pathao_area_id;
        else                                       $data['area_id'] = null;

        if (isset($data['status']) && $data['status'] !== $order->status) {
            $newAssignId = $this->autoAssignWorker($data['status']);
            if ($newAssignId) {
                $data['assign_user_id'] = $newAssignId;
            }
        }

        $data['amount'] = $data['final_amount'] - ($data['shipping_charge'] ?? 0) + ($data['discount'] ?? 0);

        DB::beginTransaction();

        try {
            $order->fill($data);
            $dirtyFields = $order->getDirty(); 
            
            $oldDataArray = [];
            $newDataArray = [];
            $changeLogs = [];

            foreach ($dirtyFields as $field => $newValue) {
                if (in_array($field, ['updated_at', 'amount'])) continue; 
                
                $oldValue = $order->getOriginal($field); 
                $fieldName = ucwords(str_replace('_', ' ', $field)); 
                
                $oldDataArray[$field] = $oldValue;
                $newDataArray[$field] = $newValue;

                $changeLogs[] = "{$fieldName}: '{$oldValue}' ➞ '{$newValue}'";
            }

            $order->save(); 

            if (isset($request->order_line_id)) {
                $delete_line = OrderDetails::where('order_id', $id)
                    ->whereNotIn('id', $request->order_line_id)->get();

                foreach ($delete_line as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    $line->delete();
                }
            } else {
                foreach ($order->details as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    $line->delete();
                }
            }

            $productsModified = false;
            if (isset($request->product_id)) {
                $productsModified = true;
                $dataLines = [];
                foreach ($request->product_id as $key => $product_id) {
                    if (!empty($request->order_line_id[$key])) {
                        $qty = (int)$request->quantity[$key];
                        $line_id = $request->order_line_id[$key];
                        $line = OrderDetails::find($line_id);
                        $this->util->updateProductStock($line->product_id, $line->variation_id, $qty, $line->quantity);
                        $line->quantity = $qty;
                        $line->unit_price = (float)$request->unit_price[$key];
                        $line->save();
                    } else {
                        $qty = (int)$request->quantity[$key];
                        $variation_id = (int)$request->variation_id[$key];
                        $dataLines[] = [
                            'product_id'    => (int)$product_id,
                            'quantity'      => $qty,
                            'variation_id' => $variation_id,
                            'unit_price'    => (float)$request->unit_price[$key],
                            'discount'      => (float)($request->unit_discount[$key] ?? 0),
                        ];
                        $this->util->decreaseProductStock((int)$product_id, $variation_id, $qty);
                        $this->checkAndSendStockAlert((int)$product_id);
                    }
                }
                if (!empty($dataLines)) $order->details()->createMany($dataLines);
            }

            DB::commit();

            $logMsg = "Order updated. ";
            if (count($changeLogs) > 0) {
                $logMsg .= "Changes: [ " . implode(', ', $changeLogs) . " ]. ";
            } else {
                $logMsg .= "No primary fields modified. ";
            }

            if ($productsModified) {
                $logMsg .= "(Product items were modified).";
            }

            if (isset($oldDataArray['status']) && isset($newDataArray['status'])) {
                if (trim(strtolower($oldDataArray['status'])) == 'delivered' && in_array(trim(strtolower($newDataArray['status'])), ['pending', 'processing'])) {
                    logActivity('Suspicious Alert', 'Order', "Alert: Delivered order moved backward to {$newDataArray['status']}", $order->id);
                }
            }

            logActivity('Update Order', 'Order', $logMsg, $order->id, $oldDataArray, $newDataArray);

            return response()->json(['status' => true, 'msg' => 'Order Updated Successfully!', 'url' => route('admin.orders.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            if (!auth()->user()->can('order.delete')) abort(403, 'unauthorized');

            $item = Order::find($id);

            if ($item->details()->count()) {
                foreach ($item->details as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                }
                $item->details()->delete();
            }

            if ($item->payments()->count()) $item->payments()->delete();

            $item->delete();

            DB::commit();

            logActivity('Delete Order', 'Order', "Order #{$id} moved to trash.", $id);

            return response()->json(['status' => true, 'msg' => 'Order Is Deleted!!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function trashed_orders(Request $request)
    {
        $status = $request->status;
        $q = $request->q;
        $query = Order::onlyTrashed()->with('details', 'details.product', 'payments');

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('invoice_no', 'Like', '%' . $q . '%');
            });
        }

        if (!empty($status)) {
            $query->where('status', 'Like', '%' . $status . '%');
        }

        if (Auth::user()->hasRole('worker')) {
            $query->where('assign_user_id', Auth::id());
        }

        $yes_count = Order::whereNotNull('courier_tracking_id')->whereIn('status', ['courier', 'Shipped'])->count();
        $no_count  = Order::whereNull('courier_tracking_id')->whereIn('status', ['courier', 'Shipped'])->count();
        
        $trashed_orders = $query->orderBy('id', 'desc')->paginate(100);

        return view('backend.orders.trashed_orders', compact('trashed_orders', 'status', 'q', 'yes_count', 'no_count'));
    }

    public function restore_order(Request $request)
    {
        $restore_order = Order::where('id', $request->id)->withTrashed()->first();
        $restore_order_details = OrderDetails::where('order_id', $restore_order->id)->get();

        foreach ($restore_order_details as $restore_details) {
            $this->util->increaseProductStock($restore_details->product_id, $restore_details->variation_id, $restore_details->quantity);
        }

        $restore_order->restore();

        logActivity('Restore Order', 'Order', "Order #{$request->id} restored.", $request->id);

        return response()->json(['success' => true, 'msg' => 'Order Is Restored !!']);
    }

    public function forceDel($id)
    {
        try {
            DB::beginTransaction();

            $del_orders = Order::where('id', $id)->withTrashed()->first();
            $del_order_details = OrderDetails::where('order_id', $id)->get();

            foreach ($del_order_details as $del_details) {
                $this->util->decreaseProductStock($del_details->product_id, $del_details->variation_id, $del_details->quantity);
                $del_details->delete();
            }

            $del_orders->forceDelete();

            DB::commit();

            logActivity('Force Delete', 'Order', "Order #{$id} deleted permanently.", $id);

            return response()->json(['status' => true, 'msg' => 'Order Is Deleted Permanently!!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function forceDelWithStatusRule($id)
    {
        try {
            if (!auth()->user()->can('order.delete')) abort(403, 'unauthorized');

            $order = Order::where('id', $id)->withTrashed()->firstOrFail();

            if (!$this->canPermanentlyDeleteOrder($order)) {
                $requiredStatus = $this->permanentDeleteRequiredStatus();

                return response()->json([
                    'status' => false,
                    'msg' => "Order must be in {$requiredStatus} status before permanent delete.",
                ]);
            }

            $details = OrderDetails::where('order_id', $id)->get(['product_id', 'variation_id', 'quantity']);
            $stockCompensationMultiplier = $order->trashed()
                ? 1
                : (!empty(OrderStatus::forStatus($order->status)['reduces_stock']) ? 2 : 1);

            $response = $this->forceDel($id);
            $payload = $response->getData(true);

            if (!empty($payload['status'])) {
                foreach ($details as $detail) {
                    $this->util->increaseProductStock(
                        $detail->product_id,
                        $detail->variation_id,
                        $detail->quantity * $stockCompensationMultiplier
                    );
                }
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function deleteAllOrder2()
    {
        try {
            if (!auth()->user()->can('order.delete')) abort(403, 'unauthorized');

            $orderIds = request('order_ids', []);
            if (empty($orderIds)) {
                return response()->json(['status' => false, 'msg' => 'Please Select An Order First !']);
            }

            $orders = Order::whereIn('id', $orderIds)->withTrashed()->get();
            if ($orders->isEmpty()) {
                return response()->json(['status' => false, 'msg' => 'No matching orders found.']);
            }

            $requiredStatus = $this->permanentDeleteRequiredStatus();
            $blockedOrders = $orders
                ->filter(fn ($order) => !$this->canPermanentlyDeleteOrder($order))
                ->pluck('invoice_no')
                ->filter()
                ->values();

            if ($blockedOrders->isNotEmpty()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Permanent delete requires ' . $requiredStatus . ' status. Blocked invoice(s): ' . $blockedOrders->implode(', '),
                ]);
            }

            $deleted = 0;
            foreach ($orders as $order) {
                $response = $this->forceDelWithStatusRule($order->id);
                $payload = $response->getData(true);

                if (empty($payload['status'])) {
                    return $response;
                }

                $deleted++;
            }

            return response()->json(['status' => true, 'msg' => "{$deleted} order(s) deleted permanently."]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function category_wise_order(Request $request)
    {
        $subCats = Category::where('parent_id', $request->category_id)->get();
        $products_data = Product::where('category_id', $request->category_id)->get();

        $products_id = [];
        foreach ($products_data as $data_p) $products_id[] = $data_p->id;

        $all_order_id = [];
        foreach ($products_id as $pr_id) {
            $details = OrderDetails::where('product_id', $pr_id)->first();
            if (isset($details->order_id)) $all_order_id[] = $details->order_id;
        }

        $items = [];
        foreach ($all_order_id as $order_id) {
            $items_order = Order::with('details', 'details.product', 'payments')->where('id', $order_id)->first();
            if ($items_order) $items[] = $items_order;
        }

        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;
        $all_category = Category::whereNull('parent_id')->get();
        $yes_count = Order::whereNotNull('courier_tracking_id')->whereIn('status', ['courier', 'Shipped'])->count();
        $no_count  = Order::whereNull('courier_tracking_id')->whereIn('status', ['courier', 'Shipped'])->count();

        return view('backend.orders.cat_wise_order', compact('items', 'yes_count', 'no_count', 'all_category', 'subCats', 'category_id', 'sub_category_id'));
    }

    public function orderStatus($id)
    {
        $item = Order::find($id);
        $status = getOrderStatus();
        return view('backend.orders.status_update', compact('item', 'status'));
    }

    public function orderStatusUPdate($id)
    {
        $item = Order::with('user', 'details')->find($id);
        
        $old_data = [
            'status' => $item->status,
            'payment_status' => $item->payment_status,
            'assign_user_id' => $item->assign_user_id
        ];

        $old_status = $item->status;
        $change_status = request('status');

        $this->applyStatusTransitionEffects($item, $old_status, $change_status);

        $item->status = request('status');

        if ($this->statusMarksPaymentPaid(request('status'))) {
            $item->payment_status = 'Paid';
        }

        $newAssignId = $this->autoAssignWorker($item->status);
        if ($newAssignId) {
            $item->assign_user_id = $newAssignId;
        }

        $item->save();

        $new_data = [
            'status' => $item->status,
            'payment_status' => $item->payment_status,
            'assign_user_id' => $item->assign_user_id
        ];

        if (trim(strtolower($old_status)) == 'delivered' && in_array(trim(strtolower(request('status'))), ['pending', 'processing'])) {
            logActivity('Suspicious Alert', 'Order', "Alert: A Delivered order was moved backward to " . request('status'), $item->id);
        }

        logActivity('Update Status', 'Order', "Order #{$item->id} status changed to {$item->status}", $item->id, $old_data, $new_data);

        $settings = Information::first();
        $status = $this->statusSmsKey(request('status'));
        
       $statusMap = [
    'pending'          => ['active' => 'sms_pending_active',          'template' => 'sms_pending'],
    'incomplete'       => ['active' => 'sms_incomplete_active',       'template' => 'sms_incomplete'],
    'on hold'          => ['active' => 'sms_on_hold_active',          'template' => 'sms_on_hold'],
    'scheduled'        => ['active' => 'sms_scheduled_active',        'template' => 'sms_scheduled'],
    'confirmed'        => ['active' => 'sms_confirmed_active',        'template' => 'sms_confirmed'],
    'cancelled'        => ['active' => 'sms_cancell_active',          'template' => 'sms_cancell'],
    'processing'       => ['active' => 'sms_processing_active',       'template' => 'sms_processing'],
    'courier complete' => ['active' => 'sms_courier_complete_active', 'template' => 'sms_courier_complete'],
    'shipped'          => ['active' => 'sms_courier_active',          'template' => 'sms_courier'],
    'delivered'        => ['active' => 'sms_delivered_active',        'template' => 'sms_delivered'],
    'returning'        => ['active' => 'sms_returning_active',        'template' => 'sms_returning'],
    'return received'  => ['active' => 'sms_return_received_active',  'template' => 'sms_return_received'],
    'return missing'   => ['active' => 'sms_return_missing_active',   'template' => 'sms_return_missing'],
];

        if ($settings && $settings->sms_api_key && $item->mobile && array_key_exists($status, $statusMap)) {
            
            $activeCol = $statusMap[$status]['active'];
            $tempCol   = $statusMap[$status]['template'];

            if ($settings->$activeCol == 1 && !empty($settings->$tempCol)) {
                
                $msg = str_replace(
                    ['{order_id}', '{amount}', '{status}'], 
                    [$item->id, $item->final_amount, ucfirst($status)], 
                    $settings->$tempCol
                );
                
                try {
                    Http::get("http://bulksmsbd.net/api/smsapi", [
                        'api_key'  => $settings->sms_api_key,
                        'type'     => 'text',
                        'number'   => $item->mobile,
                        'senderid' => $settings->sms_sender_id,
                        'message'  => $msg,
                    ]);
                } catch (\Exception $e) {
                    Log::error('SMS Sending Failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json(['status' => true, 'msg' => 'Order Status Updated!']);
    }

    public function assignUser()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->whereNotNull('name');
        })->whereNotNull('status')->get();
        
        return view('backend.orders.assign_user', compact('users'));
    }

    public function orderStatusUpdateMulti()
    {
        $status = getOrderStatus();
        return view('backend.orders.all_status_update', compact('status'));
    }

    public function orderStatusUpdateDirect(Request $request)
    {
        try {
            $order_ids = $request->order_ids;
            $status = $request->status; 

            if (empty($order_ids)) {
                return response()->json(['status' => false, 'msg' => 'Please Select At least One Order!']);
            }

            $oldOrders = Order::whereIn('id', $order_ids)->get(['id', 'status', 'payment_status']);

            $updateData = ['status' => $status];
            if ($this->statusMarksPaymentPaid($status)) {
                $updateData['payment_status'] = 'Paid';
            }
            
            Order::whereIn('id', $order_ids)->update($updateData);
            
            foreach ($oldOrders as $o) {
                $old_status = $o->status;
                
                if (trim(strtolower($old_status)) == 'delivered' && in_array(trim(strtolower($status)), ['pending', 'processing'])) {
                    logActivity('Suspicious Alert', 'Order', "Alert: Delivered order moved backward to {$status}", $o->id);
                }

                $old_data = ['status' => $o->status, 'payment_status' => $o->payment_status];
                $new_data = [
                    'status' => $status, 
                    'payment_status' => ($this->statusMarksPaymentPaid($status) ? 'Paid' : $o->payment_status)
                ];

                logActivity('Update Status', 'Order', "Status changed to {$status}", $o->id, $old_data, $new_data);
            }

            return response()->json(['status' => true, 'msg' => 'Order status successfully updated to ' . $status . '!']);
            
        } catch (\Exception $e) {
            Log::error('Order Direct Status Update Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'msg' => 'Error: Something went wrong!']);
        }
    }

    public function multuOrderStatusUpdate()
    {
        $change_status = request('status');

        foreach (request('order_ids') as $id) {
            $item = Order::with(['user', 'details'])->find($id);
            $old_status = $item->status;
            
            $old_data = ['status' => $item->status, 'payment_status' => $item->payment_status, 'assign_user_id' => $item->assign_user_id];

            $this->applyStatusTransitionEffects($item, $old_status, $change_status);

            $item->status = request('status');

            if ($this->statusMarksPaymentPaid(request('status'))) {
                $item->payment_status = 'Paid';
            }

            $newAssignId = $this->autoAssignWorker($item->status);
            if ($newAssignId) {
                $item->assign_user_id = $newAssignId;
            }

            $item->save();

            $new_data = ['status' => $item->status, 'payment_status' => $item->payment_status, 'assign_user_id' => $item->assign_user_id];

            if (trim(strtolower($old_status)) == 'delivered' && in_array(trim(strtolower($item->status)), ['pending', 'processing'])) {
                logActivity('Suspicious Alert', 'Order', "Alert: Delivered order moved backward to {$item->status}", $id);
            }

            logActivity('Update Status', 'Order', "Status changed to {$item->status}", $id, $old_data, $new_data);

            $settings = Information::first();
            $status = $this->statusSmsKey(request('status'));
            
            $statusMap = [
                'pending'          => ['active' => 'sms_pending_active',          'template' => 'sms_pending'],
                'incomplete'       => ['active' => 'sms_pending_active',          'template' => 'sms_pending'],
                'on hold'          => ['active' => 'sms_on_hold_active',          'template' => 'sms_on_hold'],
                'scheduled'        => ['active' => 'sms_pending_active',          'template' => 'sms_pending'],
                'confirmed'        => ['active' => 'sms_confirmed_active',        'template' => 'sms_confirmed'],
                'cancelled'        => ['active' => 'sms_cancell_active',          'template' => 'sms_cancell'],
                'processing'       => ['active' => 'sms_processing_active',       'template' => 'sms_processing'],
                'courier complete' => ['active' => 'sms_courier_active',          'template' => 'sms_courier'],
                'shipped'          => ['active' => 'sms_courier_active',          'template' => 'sms_courier'],
                'delivered'        => ['active' => 'sms_delivered_active',        'template' => 'sms_delivered'],
                'returning'        => ['active' => 'sms_returning_active',        'template' => 'sms_returning'],
                'return received'  => ['active' => 'sms_return_received_active',  'template' => 'sms_return_received'],
                'return missing'   => ['active' => 'sms_return_missing_active',   'template' => 'sms_return_missing'],
            ];

            if ($settings && $settings->sms_api_key && $item->mobile && array_key_exists($status, $statusMap)) {
                
                $activeCol = $statusMap[$status]['active'];
                $tempCol   = $statusMap[$status]['template'];

                if ($settings->$activeCol == 1 && !empty($settings->$tempCol)) {
                    
                    $msg = str_replace(
                        ['{order_id}', '{amount}', '{status}'], 
                        [$item->id, $item->final_amount, ucfirst($status)], 
                        $settings->$tempCol
                    );
                    
                    try {
                        Http::get("http://bulksmsbd.net/api/smsapi", [
                            'api_key'  => $settings->sms_api_key,
                            'type'     => 'text',
                            'number'   => $item->mobile,
                            'senderid' => $settings->sms_sender_id,
                            'message'  => $msg,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('SMS Sending Failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json(['status' => true, 'msg' => 'Order Status Updated!!']);
    }

    public function updateCourierStatus()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with('user', 'details')->find($id);
            if ($item->courier_id == NULL || $item->courier_id !== 3) {
                return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'This order only for Steadfast Courier']);
            } else if ($item->courier_tracking_code == NULL) {
                return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'Steadfast Courier Tracking Code Not Found!']);
            } else {
                $response = Http::withHeaders([
                    'Api-Key' => $this->steadfast_api_key,
                    'Secret-Key' => $this->steadfast_secret_key,
                    'Content-Type' => 'application/json'
                ])->get($this->steadfast_api_base_url . '/status_by_trackingcode/' . $item->courier_tracking_code);

                $status = $response->json();
                if ($status && ($status['status'] ?? '') == '200' && ($status['delivery_status'] ?? null)) {
                    
                    $courier_status = strtolower($status['delivery_status']);
                    $item->courier_status = $status['delivery_status'];
                    
                    $old_status = $item->status;
                    
                    if (in_array($courier_status, ['delivered', 'successful', 'success'])) {
                        $item->status = 'Delivered';
                    } 
                    elseif (in_array($courier_status, ['cancelled', 'returned', 'partial_delivered'])) {
                        $item->status = 'Returning';
                    }

                    $this->applyStatusTransitionEffects($item, $old_status, $item->status);

                    if ($this->statusMarksPaymentPaid($item->status)) {
                        $item->payment_status = 'Paid';
                    }

                    if (!$item->save()) return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'Something went wrong!']);
                    
                    logActivity('Courier Status Update', 'Order', "Courier status: {$item->courier_status}, Order Status: {$item->status}", $item->id, ['status' => $old_status], ['status' => $item->status]);
                } else {
                    return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'Something went wrong!']);
                }
            }
        }

        return response()->json(['status' => true, 'msg' => 'Courier & Order Status Updated Successfully!!']);
    }

    public function courierWebhook(Request $request)
    {
        Log::info('Courier Webhook Hit:', $request->all());

        if ($request->input('event') === 'webhook.integration') {
            return response()->json(['status' => true, 'message' => 'Carrybee Webhook Integrated Successfully!'], 200);
        }

        $tracking_code = $request->input('tracking_code') ?? $request->input('consignment_id') ?? $request->input('tracking_id') ?? $request->input('order_id');
        $delivery_status = strtolower($request->input('status') ?? $request->input('delivery_status') ?? $request->input('parcel_status') ?? $request->input('state'));

        if (!$tracking_code || !$delivery_status) {
            return response()->json(['status' => false, 'message' => 'Invalid Payload'], 400);
        }

        $order = Order::with('details')
            ->where('courier_tracking_code', $tracking_code)
            ->orWhere('courier_tracking_id', $tracking_code)
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $order->courier_status = $request->input('status') ?? $request->input('delivery_status') ?? $request->input('state');
        $old_status = $order->status;
        $new_status = $order->status; 

        if (in_array($delivery_status, ['picked_up', 'in_transit', 'dispatched', 'active', 'pickup_done', 'scanned', 'assigned', 'accepted'])) {
            $new_status = 'Shipped'; 
        } 
        elseif (in_array($delivery_status, ['delivered', 'successful', 'success'])) {
            $new_status = 'Delivered';
        } 
        elseif (in_array($delivery_status, ['cancelled', 'failed', 'rejected', 'canceled'])) {
            $new_status = 'Cancelled';
        }
        elseif (in_array($delivery_status, ['returned', 'partial_delivered', 'return_in_transit', 'returning'])) {
            $new_status = 'Returning';
        }

        if (strtolower($old_status) !== strtolower($new_status)) {
            $order->status = $new_status;

            $this->applyStatusTransitionEffects($order, $old_status, $new_status);

            if ($this->statusMarksPaymentPaid($new_status)) {
                $order->payment_status = 'Paid';
            }

            logActivity('Webhook', 'Order', "Auto updated to {$new_status} via Webhook. Tracking: {$tracking_code} | Courier Status: {$order->courier_status}", $order->id, ['status' => $old_status], ['status' => $new_status]);
            $order->save();
        } else {
            $order->save(); 
        }

        return response()->json(['status' => true, 'message' => 'Order updated successfully via Webhook']);
    }

    public function assignUserStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $assignTo = empty($request->assign_user_id) || $request->assign_user_id === 'null' ? null : (int) $request->assign_user_id;
            $fromUserId = $request->from_user_id; 
            
            $orderIds = $request->order_ids;
            if (is_string($orderIds)) {
                $orderIds = explode(',', $orderIds);
            }
            $orderIds = is_array($orderIds) ? array_filter($orderIds) : [];

            $assignQty = (int) $request->assign_qty;
            $assignStatus = $request->assign_status;

            $count = 0;
            $fetchedIds = [];

            if (count($orderIds) > 0) {
                $oldOrders = DB::table('orders')->whereIn('id', $orderIds)->get(['id', 'assign_user_id']);
                DB::table('orders')->whereIn('id', $orderIds)->update(['assign_user_id' => $assignTo]);
                $count = count($orderIds);
                $fetchedIds = $orderIds; 
            } 
            else {
                $query = DB::table('orders');
                
                if (auth()->check() && auth()->user()->hasRole('worker')) {
                    $myId = auth()->id();
                    $query->where(function($q) use ($myId) {
                        $q->whereNull('assign_user_id')->orWhere('assign_user_id', $myId);
                    });
                } else {
                    if (empty($fromUserId) || $fromUserId === 'unassigned') {
                        $query->whereNull('assign_user_id'); 
                    } elseif ($fromUserId === 'all') {
                    } else {
                        $query->where('assign_user_id', $fromUserId);
                    }
                }
                
                if (!empty($assignStatus)) {
                    $query->where('status', $assignStatus);
                }

                if ($assignQty > 0) {
                    $query->limit($assignQty);
                }

                $lockKey = 'assign_user_lock_' . auth()->id();
                if (Cache::has($lockKey)) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'msg' => 'Assign is in progress, please wait 2-3 seconds!']);
                }
                Cache::put($lockKey, true, 3);

                $fetchedIds = $query->orderBy('id', 'desc')->lockForUpdate()->pluck('id')->toArray();

                if (empty($fetchedIds)) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'msg' => 'No orders found!']);
                }

                $oldOrders = DB::table('orders')->whereIn('id', $fetchedIds)->get(['id', 'assign_user_id']);
                DB::table('orders')->whereIn('id', $fetchedIds)->update(['assign_user_id' => $assignTo]);
                $count = count($fetchedIds);
            }

            if (function_exists('logActivity')) {
                $assignToName = $assignTo ? "User ID: {$assignTo}" : "Unassigned";
                foreach ($oldOrders as $o) {
                    $old_data = ['assign_user_id' => $o->assign_user_id];
                    $new_data = ['assign_user_id' => $assignTo];
                    logActivity('Assign User', 'Order', "Assigned to {$assignToName}", $o->id, $old_data, $new_data);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => "Successfully reassigned {$count} order(s)!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function deleteAllOrder()
    {
        DB::beginTransaction();

        try {
            if (!auth()->user()->can('order.delete')) abort(403, 'unauthorized');

            $orders = DB::table('orders')->select('id')->whereIn('id', request('order_ids'))->get();

            foreach ($orders as $order) {
                $item = Order::find($order->id);

                if ($item->details()->count()) {
                    foreach ($item->details as $line) {
                        $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    }
                    $item->details()->delete();
                }

                if ($item->payments()->count()) $item->payments()->delete();

                logActivity('Delete Order', 'Order', "Order #{$item->id} deleted.", $item->id);

                $item->delete();
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Order Is Deleted!!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    private function getOrderTotalWeight($order)
    {
        $totalWeight = 0;
        foreach ($order->details as $detail) {
            $product = Product::find($detail->product_id);
            $weight = $product ? (float) ($product->weight ?? 1) : 1; 
            $totalWeight += $weight * $detail->quantity;
        }
        
        return $totalWeight > 0 ? $totalWeight : 1; 
    }

    private function markShipped(Order $order, int $courierId, array $extra = []): void
    {
        $order->courier_id = $order->courier_id ?: $courierId;
        $old_status = $order->status;
        $new_status = $this->courierSentOrderStatus($old_status);

        $this->applyStatusTransitionEffects($order, $old_status, $new_status);

        $order->status = $new_status;
        if ($this->statusMarksPaymentPaid($new_status)) {
            $order->payment_status = 'Paid';
        }

        foreach ($extra as $k => $v) {
            $order->{$k} = $v;
        }
        $order->save();
        
        logActivity('Courier Update', 'Order', "Sent to courier ID: {$courierId} and status changed to {$new_status}", $order->id, ['status' => $old_status], ['status' => $new_status]);
    }

    public function OrderSendToRedx()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with(['user', 'details'])->find($id);

            if (empty($item->courier_id)) $item->courier_id = 1; 
            if ($item->courier_id != 1) return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' is not assigned to Redx']);
            if (!empty($item->courier_tracking_id)) return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' already sent to Redx']);

            $status = $this->createRedxParcel($item);

            if (!empty($status['tracking_id'])) {
                $this->markShipped($item, 1, ['courier_tracking_id' => $status['tracking_id']]);
            } elseif (!empty($status['message'])) {
                logActivity('Courier API Error', 'Redx', "Failed to send. Error: " . $status['message'], $item->id);
                return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' ' . $status['message']]);
            } else {
                logActivity('Courier API Error', 'Redx', "Failed to send. Unknown response.", $item->id);
                return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' unknown Redx response']);
            }
        }
        return response()->json(['status' => true, 'msg' => 'Order(s) sent to Redx & status updated!', 'reload' => true]);
    }

    public function getRedxAreaList($by = null, $value = null)
    {
        try {
            $response = Http::withHeaders(['API-ACCESS-TOKEN' => $this->redx_api_access_token])->get($this->redx_api_base_url . 'areas');
            $json = $response->json();
            return $json['areas'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createRedxParcel($item)
    {
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name  ?? ($item->user->last_name  ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));
        
        $totalWeightInGram = $this->getOrderTotalWeight($item) * 1000; 

        $response = Http::withHeaders([
            'API-ACCESS-TOKEN' => $this->redx_api_access_token,
            'Content-Type'     => 'application/json'
        ])->post($this->redx_api_base_url . 'parcel', [
            "customer_name"          => $name,
            "customer_phone"         => $phone,
            "delivery_area"          => $item->area_name,
            "delivery_area_id"       => $item->area_id,
            "customer_address"       => $item->shipping_address,
            "merchant_invoice_id"    => $item->invoice_no,
            "cash_collection_amount" => $item->final_amount,
            "parcel_weight"          => (string) $totalWeightInGram, 
            "instruction"            => "",
            "value"                  => $item->final_amount,
            "pickup_store_id"        => 0,
            "parcel_details_json"    => []
        ]);

        return $response->json();
    }

    public function fetchAddressDetails(Request $request)
    {
        $address = strtolower($request->input('address'));
        $city = null;
        $zone = null;
        $area = null;

        $cities = $this->getPathaoCityList();
        $city = collect($cities)->first(function ($c) use ($address) {
            return str_contains($address, strtolower($c['city_name']));
        });

        if ($city) {
            $zonesResponse = $this->getPathaoZoneListByCity($city['city_id']);
            $zones = $zonesResponse->getData(true)['zones'] ?? [];
            $zone = collect($zones)->first(function ($z) use ($address) {
                return str_contains($address, strtolower($z['zone_name']));
            });

            if ($zone) {
                $areasResponse = $this->getPathaoAreaListByZone($zone['zone_id']);
                $areas = $areasResponse->getData(true)['areas'] ?? [];
                $area = collect($areas)->first(function ($a) use ($address) {
                    return str_contains($address, strtolower($a['area_name']));
                });
            }
        }

        return response()->json([
            'city_id' => $city['city_id'] ?? null,
            'zone_id' => $zone['zone_id'] ?? null,
            'area_id' => $area['area_id'] ?? null,
        ]);
    }

    public function getPathaoStoreList()
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return [];
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/stores');
            if ($response->failed()) {
                Log::error('Pathao Store API failed', ['response' => $response->body()]);
                return [];
            }
            return $response->json()['data']['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Pathao Store API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getPathaoCityList()
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return [];
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/countries/1/city-list');
            if ($response->failed()) {
                Log::error('Pathao City API failed', ['response' => $response->body()]);
                return [];
            }
            return $response->json()['data']['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Pathao City API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getPathaoZoneListByCity($city)
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return response()->json(['success' => true, 'zones' => []]);
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/cities/' . $city . '/zone-list');
            if ($response->failed()) {
                Log::error('Pathao Zone API failed', ['response' => $response->body()]);
                return response()->json(['success' => true, 'zones' => []]);
            }
            $zones = $response->json()['data']['data'] ?? [];
            return response()->json(['success' => true, 'zones' => $zones]);
        } catch (\Exception $e) {
            Log::error('Pathao Zone API exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => true, 'zones' => []]);
        }
    }

    public function getPathaoAreaListByZone($zone)
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return response()->json(['success' => true, 'areas' => []]);
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/zones/' . $zone . '/area-list');
            if ($response->failed()) {
                Log::error('Pathao Area API failed', ['response' => $response->body()]);
                return response()->json(['success' => true, 'areas' => []]);
            }
            $areas = $response->json()['data']['data'] ?? [];
            return response()->json(['success' => true, 'areas' => $areas]);
        } catch (\Exception $e) {
            Log::error('Pathao Area API exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => true, 'areas' => []]);
        }
    }

    public function OrderSendToPathao(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (request('order_ids') as $id) {
                $item = Order::with(['user', 'details'])->find($id);

                if (empty($item->courier_id)) $item->courier_id = 2; 
                if ($item->courier_id != 2) {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'msg' => 'Invoice: ' . $item->invoice_no . ' is not assigned to Pathao']);
                }
                if (!empty($item->courier_tracking_id)) {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'msg' => 'Invoice: ' . $item->invoice_no . ' already sent to Pathao']);
                }

                $status = $this->createPathaoParcel($item);

                if (!empty($status['data']['consignment_id'])) {
                    $this->markShipped($item, 2, [
                        'courier_status'      => $status['data']['order_status'] ?? null,
                        'courier_tracking_id' => $status['data']['consignment_id'],
                    ]);
                } elseif (!empty($status['errors'])) {
                    DB::rollBack();
                    logActivity('Courier API Error', 'Pathao', "Failed to send. Error: " . json_encode($status['errors']), $item->id);
                    return response()->json(['status' => 0, 'invoice' => $item->invoice_no, 'errors' => $status['errors']]);
                } else {
                    DB::rollBack();
                    logActivity('Courier API Error', 'Pathao', "Failed to send. Unknown response.", $item->id);
                    return response()->json(['status' => 0, 'msg' => 'Invoice: ' . $item->invoice_no . ' unknown Pathao response']);
                }
            }

            DB::commit();
            return response()->json(['status' => 1, 'msg' => 'Order(s) sent to Pathao & status updated!', 'reload' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }
    }

    public function createPathaoParcel($item)
    {
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name ?? ($item->user->last_name ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));

        $totalQty = $item->details->sum('quantity');
        if ($totalQty <= 0) $totalQty = 1;

        $weight = $this->getOrderTotalWeight($item); 

        $payload = [
            "store_id"          => (int)$this->pathao_store_id,
            "merchant_order_id" => (string)$item->invoice_no,
            "recipient_name"    => $name,
            "recipient_phone"   => $phone,
            "recipient_address" => $item->shipping_address,
            "city_id"           => (int)($item->city ?? 0),
            "zone_id"           => (int)($item->state ?? 0),
            "area_id"           => (int)($item->area_id ?? 0),
            "delivery_type"        => 48,
            "item_type"            => 2,
            "item_quantity"        => $totalQty,
            "item_weight"          => $weight,
            "amount_to_collect"    => (float)$item->final_amount,
            "special_instruction"=> $item->note ?? '',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->pathao_api_access_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($this->pathao_api_base_url . 'aladdin/api/v1/orders', $payload);

            if ($response->failed()) {
                Log::error('Pathao create order failed', [
                    'invoice' => $item->invoice_no,
                    'body'    => $response->body(),
                    'status'  => $response->status(),
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            return [
                'errors' => [
                    'exception' => $e->getMessage(),
                ],
            ];
        }
    }

    public function OrderSendToSteadfast()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with(['user', 'details'])->find($id);

            if (empty($item->courier_id)) $item->courier_id = 3; 
            if ($item->courier_id != 3) return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'This order is not assigned to Steadfast Courier']);
            if (!empty($item->courier_tracking_id)) return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'This order already sent to Steadfast Courier']);

            $status = $this->createSteadfastParcel($item);

            if (!empty($status['consignment']['consignment_id'])) {
                $this->markShipped($item, 3, [
                    'courier_tracking_id'   => $status['consignment']['consignment_id'],
                    'courier_tracking_code' => $status['consignment']['tracking_code'] ?? null,
                    'courier_status'        => $status['consignment']['status'] ?? null,
                ]);
            } else {
                $errorMsg = isset($status['errors']) ? json_encode($status['errors']) : 'Unknown Error';
                logActivity('Courier API Error', 'Steadfast', "Failed to send. Error: " . $errorMsg, $item->id);
                return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'Something went wrong!']);
            }
        }
        return response()->json(['status' => true, 'msg' => 'Order sent to Steadfast & status updated!', 'reload' => true]);
    }

    public function createSteadfastParcel($item)
    {
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name ?? ($item->user->last_name ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));

        $weight = $this->getOrderTotalWeight($item); 

        $response = Http::withHeaders([
            'Api-Key'     => $this->steadfast_api_key,
            'Secret-Key'  => $this->steadfast_secret_key,
            'Content-Type'=> 'application/json'
        ])->post($this->steadfast_api_base_url . '/create_order', [
            "invoice"           => $item->invoice_no,
            "recipient_name"    => $name,
            "recipient_phone"   => $phone,
            "recipient_address" => $item->shipping_address,
            "cod_amount"        => (int) $item->final_amount,
            "note"              => $item->note,
            "weight"            => $weight 
        ]);

        return $response->json();
    }
    
    public function OrderSendToCarrybee()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with(['user', 'details'])->find($id);

            if (empty($item->courier_id)) $item->courier_id = 4; 
            if ($item->courier_id != 4) return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'This order is not assigned to Carrybee']);
            if (!empty($item->courier_tracking_id)) return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'This order already sent to Carrybee']);

            $status = $this->createCarrybeeParcel($item);

            if (isset($status['error']) && $status['error'] === false && !empty($status['data']['order']['consignment_id'])) {
                $this->markShipped($item, 4, [
                    'courier_tracking_id'   => $status['data']['order']['consignment_id'],
                    'courier_status'        => 'Pending' 
                ]);
            } elseif (!empty($status['message']) || !empty($status['causes'])) {
                $errorMsg = $status['message'] ?? 'API Error';
                if (!empty($status['causes'])) {
                    $errorMsg .= ' - ' . json_encode($status['causes']);
                }
                logActivity('Courier API Error', 'Carrybee', "Failed to send. Error: " . $errorMsg, $item->id);
                return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => $errorMsg]);
            } else {
                $errorDetails = isset($status['errors']) ? json_encode($status['errors']) : 'No valid response from Carrybee.';
                logActivity('Courier API Error', 'Carrybee', "Failed to send. Error: " . $errorDetails, $item->id);
                return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'Something went wrong! ' . $errorDetails]);
            }
        }
        return response()->json(['status' => true, 'msg' => 'Order sent to Carrybee & status updated!', 'reload' => true]);
    }

    public function createCarrybeeParcel($item)
    {
        $info = Information::first();
        
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name ?? ($item->user->last_name ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));
        
        $weightInGrams = (int)($this->getOrderTotalWeight($item) * 1000);
        $totalQty = $item->details->sum('quantity') ?: 1;

        $baseUrl = rtrim($info->carrybee_api_base_url, '/');

        $payload = [
            "store_id"                  => (int) $this->carrybee_store_id,
            "merchant_order_id"         => (string) $item->invoice_no,
            "delivery_type"             => 1, 
            "product_type"              => 1, 
            "recipient_phone"           => (string) $phone,
            "recipient_name"            => (string) $name,
            "recipient_address"         => (string) $item->shipping_address,
            "special_instruction"       => (string) ($item->note ?? ''),
            "item_weight"               => $weightInGrams,
            "item_quantity"             => $totalQty,
            "collectable_amount"        => (int) $item->final_amount,
            "is_closed"                 => false
        ];

        if (!empty($item->city) && $item->city > 0) {
            $payload["city_id"] = (int) $item->city;
        }
        if (!empty($item->state) && $item->state > 0) {
            $payload["zone_id"] = (int) $item->state;
        }
        if (!empty($item->area_id) && $item->area_id > 0) {
            $payload["area_id"] = (int) $item->area_id;
        }

        $response = Http::withHeaders([
            'Client-ID'      => $info->carrybee_client_id,
            'Client-Secret'  => $info->carrybee_client_secret,
            'Client-Context' => $info->carrybee_client_context,
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json'
        ])->post($baseUrl . '/api/v2/orders', $payload);

        return $response->json();
    }

    public function viewAccessToken()
    {
        return view('backend.informations.generate-pathao-access-token');
    }

    public function generatePathaoAccessToken(Request $request)
    {
        $response = Http::withHeaders(['content-type' => 'application/json', 'accept' => 'application/json'])
            ->post($this->pathao_api_base_url . 'aladdin/api/v1/issue-token', [
                "client_id"     => $request->client_id,
                "client_secret" => $request->client_secret,
                "username"      => $request->client_email,
                "password"      => $request->client_password,
                "grant_type"    => "password"
            ]);

        $tokenData = $response->json();
        return view('backend.informations.generate-pathao-access-token-view', compact('tokenData'));
    }

    public function fraudulentCheck($mobileNo)
    {
        $info = Information::first();
        $dataList = Http::get('https://dash.hoorin.com/api/courier/search.php', [
            'apiKey' => $info->fraudApi,
            'searchTerm' => $mobileNo
        ]);
        $data = $dataList->json();
        return json_encode($data);
    }

    public function fraudOrderCheck($id)
    {
        $result = Order::select(['id', 'user_id', 'mobile'])->find($id); 
        
        if ($result) {
            $result->customerPhone = $result->mobile ?? ($result->user->mobile ?? '01782889864');
            
            $totalSummery = $this->courierSummery($result->customerPhone);

            $datas = $totalSummery['Summaries'] ?? [];
            $datas2 = $totalSummery['TotalSummary'] ?? [];

            $customer = $result->user;
            if ($customer && !empty($datas2)) {
                $customer->curier_summery = $datas2;
                $customer->save();
            }

            $datas2 = $datas2['Summaries'] ?? [];

            $result->total_parcels       = $datas2['Total Parcels'] ?? 0;
            $result->total_delivered = $datas2['Total Delivered'] ?? 0;
            $result->total_canceled  = $datas2['Total Canceled'] ?? 0;
            $result->total_ratio       = ($result->total_parcels > 0) ? round(($result->total_delivered / $result->total_parcels) * 100, 0) : 0;
            $result->purcelsdatas      = count($datas) > 0 ? $datas : null;
        }

        $view = view('backend.orders.fraudOrder', compact('result'));
        return response($view);
    }

    public function courierSummery($number)
    {
        $info = Information::first();
        $apiKey = $info->fraudApi;

        $url1 = "https://dash.hoorin.com/api/courier/search.php?apiKey=$apiKey&searchTerm=$number";
        $url2 = "https://dash.hoorin.com/api/courier/sheet.php?apiKey=$apiKey&searchTerm=$number";

        $response1 = $this->callApi($url1);
        $response2 = $this->callApi($url2);

        $summary = [
            'Summaries'    => $response1['Summaries'] ?? [],
            'TotalSummary' => $response2,
        ];

        return $summary;
    }

    private function callApi($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function checkAndSendStockAlert($product_id)
    {
        $product = Product::find($product_id);
        
        if ($product && $product->stock_quantity <= 0) {
            $info = Information::first();
            
            if ($info && $info->sms_api_key && $info->admin_phone) {
                $msg = "Alert: Product '{$product->name}' is now Out of Stock!";
                
                try {
                    Http::get("http://bulksmsbd.net/api/smsapi", [
                        'api_key' => $info->sms_api_key,
                        'type' => 'text',
                        'number' => $info->admin_phone, 
                        'senderid' => $info->sms_sender_id,
                        'message' => $msg,
                    ]);
                } catch (\Exception $e) {}
            }
        }
    }

    public function customerHistory(Request $request)
    {
        $mobile = $request->mobile;
        if (!$mobile) return response()->json(['html' => '<div class="text-center text-muted">No number provided.</div>']);

        $orderbyNumber = Order::with('details.product', 'assign')
            ->where('mobile', $mobile)
            ->orderBy('id', 'desc')
            ->get();

        $html = '<div class="table-responsive"><table class="table table-bordered table-striped mb-0">';
        $html .= '<thead class="bg-light"><tr><th>Order ID</th><th>Date</th><th>Products</th><th>Status</th><th>Amount</th><th>Assigned</th></tr></thead><tbody>';

        if($orderbyNumber->count() > 0) {
            foreach($orderbyNumber as $order) {
                $products = '';
                foreach($order->details as $l) {
                     $products .= '<span class="badge bg-secondary me-1" style="white-space:normal; text-align:left;">'.($l->product->name ?? 'Unavailable').'</span><br>';
                }

                $statusBadge = in_array(strtolower($order->status), ['cancelled', 'returning', 'cancell', 'return']) ? 'bg-danger' : 'bg-info';

                $html .= '<tr>';
                $html .= '<td><a href="'.route('admin.orders.edit', $order->id).'" target="_blank" class="fw-bold">#'.$order->id.'</a></td>';
                $html .= '<td>'.$order->created_at->format('d M, Y').'</td>';
                $html .= '<td>'.$products.'</td>';
                $html .= '<td><span class="badge '.$statusBadge.'">'.ucfirst($order->status).'</span></td>';
                $html .= '<td>৳ '.$order->final_amount.'</td>';
                $html .= '<td>'.($order->assign ? $order->assign->username : '—').'</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="6" class="text-center text-muted">No history found.</td></tr>';
        }
        $html .= '</tbody></table></div>';

        return response()->json(['html' => $html]);
     }

    public function autoAssignIndex()
    {
        $info = Information::first();
        $statusList = getOrderStatus();
        
        $workers = User::whereHas('roles', function($q) {
            $q->whereRaw('LOWER(name)=?', ['worker']);
        })->where(function ($q) {
            $q->whereNull('status')->orWhereIn('status', [1, '1', 'active', 'Active']);
        })->get();
        
        $savedRules = !empty($info->auto_assign_rules) ? json_decode($info->auto_assign_rules, true) : [];

        return view('backend.orders.auto_assign', compact('info', 'statusList', 'workers', 'savedRules'));
    }

    public function saveAutoAssignStatus(Request $request)
    {
        $info = Information::first();
        if ($info) {
            $info->auto_assign_rules = $request->has('rules') ? json_encode($request->rules) : null;
            $info->save();
            
            return back()->with('success', 'Status-wise Auto Assign Rules Updated Successfully!');
        }
        return back()->with('error', 'Something went wrong!');
    }

    public function toggleAutoAssign(Request $request)
    {
        $info = Information::first();
        if ($info) {
            $info->is_auto_assign = $request->is_active;
            $info->save();
            return response()->json(['success' => true, 'msg' => 'Auto Assign system is now ' . ($request->is_active ? 'ON' : 'OFF')]);
        }
        return response()->json(['success' => false], 400);
    }

    public function scanReturnIndex()
    {
        return view('backend.orders.scan_return');
    }

    public function scanReturnSubmit(Request $request)
    {
        try {
            $barcode = trim($request->barcode);
            if (empty($barcode)) {
                return response()->json(['status' => false, 'msg' => 'Please scan a valid barcode.']);
            }

            $order = Order::with('details')
                ->where('invoice_no', $barcode)
                ->orWhere('courier_tracking_id', $barcode)
                ->orWhere('courier_tracking_code', $barcode)
                ->orWhere('id', str_replace('#', '', $barcode))
                ->first();

            if (!$order) {
                return response()->json(['status' => false, 'msg' => 'Order not found for this barcode!']);
            }

            if (in_array(strtolower($order->status), ['return received', 'cancelled', 'returning', 'return', 'cancell'])) {
                return response()->json(['status' => false, 'msg' => 'Order #' . $order->invoice_no . ' is already marked as ' . $order->status . '! Stock is safe.']);
            }

            if ($order->details()->count()) {
                foreach ($order->details as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                }
            }

            $old_status = $order->status;
            $order->status = 'Return Received';
            $order->save();

            if (function_exists('logActivity')) {
                logActivity('Scan Return', 'Order', "Status changed from {$old_status} to Return Received via Scanner. Stock auto-increased.", $order->id, ['status' => $old_status], ['status' => 'Return Received']);
            }

            return response()->json([
                'status' => true, 
                'msg' => 'Success! Order #' . $order->invoice_no . ' marked as Return Received and Stock Added.',
                'invoice' => $order->invoice_no,
                'customer' => trim($order->first_name . ' ' . $order->last_name),
                'amount' => $order->final_amount
            ]);

        } catch (\Exception $e) {
            Log::error('Scanner Return Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'msg' => 'System error occurred!']);
        }
    }

    public function markMissingReturns(Request $request)
    {
        try {
            $invoices = $request->invoices;
            if (empty($invoices) || !is_array($invoices)) {
                return response()->json(['status' => false, 'msg' => 'No invoices provided.']);
            }

            $missingOrders = Order::where(function($q) use ($invoices) {
                $q->whereIn('invoice_no', $invoices)
                  ->orWhereIn('courier_tracking_id', $invoices)
                  ->orWhereIn('courier_tracking_code', $invoices)
                  ->orWhereIn('id', str_replace('#', '', $invoices));
            })->where('status', '!=', 'Return Received')->get();

            $count = 0;
            foreach($missingOrders as $order) {
                if(strtolower($order->status) != 'return missing') {
                    $old_status = $order->status;
                    
                    $order->status = 'Return Missing';
                    $order->save();
                    
                    if (function_exists('logActivity')) {
                        logActivity('Return Missing', 'Order', "Status changed from {$old_status} to Return Missing via Manifest Check.", $order->id, ['status' => $old_status], ['status' => 'Return Missing']);
                    }
                    $count++;
                }
            }

            return response()->json([
                'status' => true, 
                'msg' => "Missing check complete! {$count} parcels marked as Return Missing.",
                'missing_count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Missing Return Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'msg' => 'System error occurred!']);
        }
     }
}

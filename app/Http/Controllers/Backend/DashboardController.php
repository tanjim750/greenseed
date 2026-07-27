<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\User;
use App\Models\Expense;
use App\Models\ProductReview;
use App\Models\ProductStock;
use App\Models\Information;
use Auth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        if (!auth()->user()->can('dashboard.access')) {
            abort(403, 'unauthorized');
        }

        $status = $request->status;
        $q      = $request->q;

        $query = Order::whereHas('details.product', function ($q) {
            $q->whereNotNull('name');
        });

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('invoice_no', 'Like', '%'.$q.'%');
            });
        }

        if (!empty($status)) {
            $query->where('status', 'Like', '%'.$status.'%');
        }

        if (Auth::user()->hasRole('worker')) {
            $query->where('assign_user_id', Auth::id());
        }

        $items        = $query->latest()->take(20)->get();
        $statuses     = getOrderStatus();
        $total_stocks = Product::sum('stock_quantity');
        $isWorker     = Auth::user()->hasRole('worker');

        return view('backend.dashboard', compact('items', 'status', 'q', 'statuses', 'total_stocks', 'isWorker'));
    }

    /**
     * KPI JSON (Top Cards + Profit Analytics + Chart)
     */
    public function getDashboardData2(Request $request)
    {
        $user      = auth()->user();
        $userStart = optional($user->created_at)?->startOfDay() ?? now()->startOfDay();

        // Default to last 30 days if no date is provided
        $startDateUi = $request->filled('startDate') ? $request->startDate : now()->subDays(30)->toDateString();
        $endDateUi   = $request->filled('endDate')   ? $request->endDate   : now()->toDateString();

        $start = Carbon::parse($startDateUi)->startOfDay();
        $end   = Carbon::parse($endDateUi)->addDay()->startOfDay();

        if ($start->lt($userStart)) $start = $userStart->copy();
        if ($end->lte($start))      $end   = $start->copy()->addDay();

        $base = Order::query()
            ->where('orders.created_at', '>=', $start)
            ->where('orders.created_at', '<',  $end)
            ->when($user->hasRole('worker') && !$user->can('order.view_all'), function ($q) use ($user) {
                $q->where('orders.assign_user_id', $user->id);
            });

        $pendingStatuses = array_merge(['Pending', 'pending'], []);
        $deliveredStatuses = OrderStatus::namesForFlag('counts_as_delivered');
        $cancelledStatuses = OrderStatus::namesForFlag('counts_as_cancelled');
        $returnStatuses = OrderStatus::namesForFlag('counts_as_return');

        // 1. Basic Stats
        $total_orders      = (clone $base)->count();
        $pending_orders    = (clone $base)->whereIn('orders.status', $pendingStatuses)->count();
        $complete_orders   = (clone $base)->whereIn('orders.status', $deliveredStatuses)->count();

        // ✅ FIXED: Cancel & Return আলাদা (Return Missing যোগ করা হলো)
        $cancel_orders     = (clone $base)->whereIn('orders.status', $cancelledStatuses)->count();
        $return_orders     = (clone $base)->whereIn('orders.status', $returnStatuses)->count();
        $cancell_orders    = $cancel_orders + $return_orders;

        // শুধুমাত্র 'Incomplete' স্ট্যাটাসের অর্ডার কাউন্ট
        $incomplete_orders = (clone $base)->where('orders.status', 'Incomplete')->count();

        // 2. Sales & Costs Stats (Delivered Only)
        $deliveredOrders = clone $base;
        $deliveredOrders->whereIn('orders.status', $deliveredStatuses);

        $sell_amount     = (clone $deliveredOrders)->sum('orders.final_amount');
        $shipping_charge = (clone $deliveredOrders)->sum('orders.shipping_charge');
        $total_discount  = (clone $deliveredOrders)->sum('orders.discount');

        // NEW BOXES CALCULATIONS
        $total_sale_val       = $sell_amount;
        $total_courier_val    = $shipping_charge;
        $total_order_val      = $total_sale_val - $total_courier_val;

        // শুধুমাত্র 'Incomplete' স্ট্যাটাসের টাকার হিসাব (কুরিয়ার চার্জ বাদে)
        $incomplete_query     = (clone $base)->where('orders.status', 'Incomplete');
        $total_incomplete_val = $incomplete_query->sum('orders.final_amount') - $incomplete_query->sum('orders.shipping_charge');

        // ✅ FIXED: Return Value Calculation (Cancel বাদ, Return Missing যোগ)
        $total_return_val     = (clone $base)->whereIn('orders.status', $returnStatuses)
                                             ->sum('orders.final_amount');

        // 3. Purchase Cost of Sold Items
        $purchaseCostQuery = clone $deliveredOrders;
        $total_purchase_cost = $purchaseCostQuery
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(DB::raw('
                SUM(
                    IF(order_details.purchase_price IS NOT NULL AND order_details.purchase_price > 0,
                        order_details.purchase_price,
                        products.purchase_prices
                    ) * order_details.quantity
                ) as total_cost
            '))->value('total_cost') ?: 0;

        // 4. Other Expenses
        $totalExpense  = Expense::whereDate('date', '>=', $start->toDateString())
                            ->whereDate('date', '<',  $end->toDateString())
                            ->sum('amount');

        // 5. Net Profit & Gross Profit Calculation
        $product_revenue = $sell_amount - $shipping_charge;
        $grossProfit     = $product_revenue - $total_purchase_cost;
        $netProfit       = $product_revenue - $total_purchase_cost - $totalExpense + $total_discount;

        // 6. Stock Warning Logic & Other Global Stats
        $threshold = Information::orderBy('id', 'desc')->value('stock_warning_limit') ?? 5;
        $lowStockCount = ProductStock::where('quantity', '<=', $threshold)->count();
        $lowStockItems = ProductStock::with(['product', 'variation.size', 'variation.color'])
                            ->where('quantity', '<=', $threshold)
                            ->orderBy('quantity', 'asc')
                            ->limit(10)
                            ->get();

        $total_products  = Product::count();
        $total_employees = User::whereHas('roles', fn($q) => $q->where('name', 'worker'))->count();
        $total_stocks    = Product::sum('stock_quantity');

        // 7. Chart Data Logic (Daily Sales)
        $chartQuery = clone $deliveredOrders;
        $chartData = $chartQuery->select(
            DB::raw('DATE(orders.created_at) as date'),
            DB::raw('SUM(orders.final_amount) as total')
        )
        ->groupBy(DB::raw('DATE(orders.created_at)'))
        ->orderBy('date', 'ASC')
        ->get();

        $chart_dates  = $chartData->pluck('date');
        $chart_totals = $chartData->pluck('total');

        return response()->json([
            'success'              => true,
            'profit'               => $netProfit,
            'totalExpense'         => $totalExpense,
            'total_orders'         => $total_orders,
            'pending_orders'       => $pending_orders,
            'complete_orders'      => $complete_orders,
            'cancell_orders'       => $cancell_orders,
            'cancel_orders'        => $cancel_orders,
            'return_orders'        => $return_orders,
            'gross_profit'         => $grossProfit,
            'sourcing_cost'        => $total_purchase_cost,
            'incomplete_orders'    => $incomplete_orders,
            'sell_amount'          => $sell_amount,
            'purchase_cost'        => $total_purchase_cost,
            'low_stock_count'      => $lowStockCount,
            'low_stock_items'      => $lowStockItems,
            'chart_dates'          => $chart_dates,
            'chart_totals'         => $chart_totals,

            // Values
            'total_sale_val'       => $total_sale_val,
            'total_order_val'      => $total_order_val,
            'total_courier_val'    => $total_courier_val,
            'total_incomplete_val' => $total_incomplete_val,
            'total_return_val'     => $total_return_val,

            // Global/Other Stats
            'total_products'       => $total_products,
            'total_employees'      => $total_employees,
            'total_stocks'         => $total_stocks,
        ]);
    }

    public function index()
    {
        $s = request('q');
        $query = ProductReview::latest();

        if (!empty($s)) {
            $query->where(function ($row) use ($s) {
                $row->where('name', 'Like', '%'.$s.'%');
            });
        }

        $data = $query->paginate(30);
        return view('backend.review.index', compact('data'));
    }

    public function destroy($id)
    {
        ProductReview::destroy($id);
        return response()->json(['status' => true, 'msg' => 'Review has been deleted']);
    }

    public function getDashboardData(Request $request)
    {
        $workerCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'worker');
        })->count();

        $data['products']           = Product::count();
        $data['orders']             = Order::count();
        $data['users']              = $workerCount;

        $data['current_month_sell'] = Order::whereBetween('orders.created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('final_amount');
        $data['today_sell']         = Order::whereDate('orders.created_at', now()->toDateString())->sum('final_amount');
        $data['prev_month_sell']    = Order::whereBetween('orders.created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('final_amount');

        return view('backend.partials.dashboard_data', $data);
    }

    public function reviewAction(Request $request)
    {
        $ids = $request->ids ?? [];
        if (empty($ids)) { return response()->json(['status' => false, 'msg' => 'No reviews selected!']); }
        if ($request->has('delete')) {
            ProductReview::whereIn('id', $ids)->delete();
            return response()->json(['status' => true, 'msg' => 'Selected reviews deleted successfully!']);
        }
        if ($request->has('status')) {
            $status = $request->status == 1 ? 1 : 0;
            ProductReview::whereIn('id', $ids)->update(['status' => $status]);
            $msg = $status ? 'Selected reviews approved!' : 'Selected reviews rejected!';
            return response()->json(['status' => true, 'msg' => $msg]);
        }
        return response()->json(['status' => false, 'msg' => 'Invalid action!']);
    }
}

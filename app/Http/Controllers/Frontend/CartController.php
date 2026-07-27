<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Variation;
use App\Models\ProductStock; 
use App\Facades\FacebookConversion;
use App\Utils\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    protected $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    /**
     * ------------------------
     * Helper: price calculation
     * ------------------------
     * return: [finalPrice, discountPerUnit, basePrice]
     */
    private function calculatePrice(Product $product, ?Variation $variation): array
    {
        if ($variation) {
            $basePrice  = (float) ($variation->price ?? 0);
            
            // Fallback if variation has no price
            if ($basePrice <= 0) {
                $basePrice = (float) ($product->sell_price ?? 0);
            }

            $afterDisc  = (float) ($variation->after_discount_price ?? 0);
            $otherDisc  = (float) ($variation->discount_price ?? 0); // Just in case

            if ($afterDisc > 0 && $afterDisc < $basePrice) {
                $finalPrice = $afterDisc;
            } elseif ($otherDisc > 0 && $otherDisc < $basePrice) {
                $finalPrice = $otherDisc;
            } else {
                $finalPrice = $basePrice;
            }
        } else {
            $basePrice  = (float) ($product->sell_price ?? 0);
            $afterDisc  = (float) ($product->after_discount ?? 0);
            
            if ($afterDisc > 0 && $afterDisc < $basePrice) {
                $finalPrice = $afterDisc;
            } else {
                $finalPrice = $basePrice;
            }
        }

        $discountPerUnit = max(0, $basePrice - $finalPrice);

        return [$finalPrice, $discountPerUnit, $basePrice];
    }

    /**
     * ✅ STOCK HELPER
     */
    private function getAvailableStock(Product $product, int $variationId = 0): int
    {
        if ((int)($product->is_stock ?? 0) !== 1) return PHP_INT_MAX;

        $available = 0;

        if ($variationId > 0) {
            $query = ProductStock::where('variation_id', $variationId);
            
            if (Schema::hasColumn((new ProductStock)->getTable(), 'product_id')) {
                 $query->where('product_id', $product->id);
            }
            
            $available = (int) $query->sum('quantity');
        }

        if ($available <= 0 && $product->type == 'single') {
            $available = (int) ($product->stock_quantity ?? 0);
        } elseif ($available <= 0 && $variationId == 0) {
            $available = (int) ($product->stock_quantity ?? 0);
        }

        return $available;
    }

    public function index()
    {
        $cart = session()->get('cart', []);

        if (request()->ajax()) {
            $segm = request()->segment(1) ?? 'home';
            $view = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
            return response()->json(['success'=>true, 'html'=>$view]);
        }

        return view('frontend.cart.index', compact('cart'));
    }

    /**
     * ✅ Core add-to-cart logic
     */
    private function addToCart(Request $request, bool $sendFacebookCapi = false): array
    {
        $request->validate([
            'product_id'   => 'required|integer|min:1',
            'variation_id' => 'nullable|integer',
            'quantity'     => 'nullable|integer|min:1',
            'action_type'  => 'nullable|string',
            'event_id'     => 'nullable|string' // ✅ Event ID Accept korar jonno
        ]);

        $segm = request()->segment(1) ?? 'home';

        $product_id   = (int) $request->product_id;
        $variation_id = (int) ($request->variation_id ?? 0);
        $quantity     = (int) ($request->quantity ?? 1);
        $eventId      = $request->event_id ?? "ATC_" . $product_id . "_" . time(); // ✅ Browser theke ID na asle default banabe

        if ($quantity <= 0) {
            return [
                'ok' => false,
                'payload' => $this->errorResponse($request, 'Please Select Minimum 1 Quantity', 422)
            ];
        }

        $product = Product::with(['category'])->find($product_id);
        if (!$product) {
            return [
                'ok' => false,
                'payload' => $this->errorResponse($request, 'Product not found!', 404)
            ];
        }

        /**
         * ✅ Variation strict check
         */
        $variation = null;

        if ($product->type === 'single') {
            if ($variation_id > 0) {
                $variation = Variation::with(['size','color'])
                    ->where('id', $variation_id)
                    ->where('product_id', $product->id)
                    ->first();
            }
            
            if (!$variation) {
                $variation = Variation::with(['size','color'])
                    ->where('product_id', $product->id)
                    ->orderBy('id')
                    ->first();
            }

            if ($variation) {
                $variation_id = (int)$variation->id;
            } else {
                 return [
                    'ok' => false,
                    'payload' => $this->errorResponse($request, 'Variation setup error for this product!', 422)
                ];
            }

        } else {
            if ($variation_id > 0) {
                $variation = Variation::with(['size','color'])
                    ->where('id', $variation_id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$variation) {
                    return [
                        'ok' => false,
                        'payload' => $this->errorResponse($request, 'Invalid variation selected!', 422)
                    ];
                }
            } else {
                return [
                    'ok' => false,
                    'payload' => $this->errorResponse($request, 'Please select product options!', 422)
                ];
            }
        }

        // ✅ Price & Discount Calculation (FIXED)
        [$finalPrice, $discountPerUnit, $basePrice] = $this->calculatePrice($product, $variation);

        $cart = session()->get('cart', []);

        // ✅ Stock Check
        $is_stock = (int) ($product->is_stock ?? 0);

        if ($is_stock === 1) {
            $stockQty = $this->getAvailableStock($product, (int)$variation_id);

            if ($stockQty < $quantity) {
                return [
                    'ok' => false,
                    'payload' => $this->errorResponse($request, 'Stock Not Available!', 422)
                ];
            }
            
            if (isset($cart[$variation_id])) {
                $existingQty = (int)($cart[$variation_id]['quantity'] ?? 0);
                if ($stockQty < ($existingQty + $quantity)) {
                    return [
                        'ok' => false,
                        'payload' => $this->errorResponse($request, 'Stock limit reached! You already have this item in cart.', 422)
                    ];
                }
            }
        }

        // ✅ Add/Update Cart
        if (isset($cart[$variation_id])) {
            $cart[$variation_id]['quantity']       += $quantity;
            $cart[$variation_id]['price']          = $finalPrice;
            $cart[$variation_id]['discount']       = $discountPerUnit;
            $cart[$variation_id]['original_price'] = $basePrice;
            $cart[$variation_id]['variation_id']   = $variation_id;
            $cart[$variation_id]['product_id']     = $product_id;
        } else {
            $cart[$variation_id] = [
                "name"             => $product->name,
                "size"             => $variation && $variation->size ? ($variation->size->title ?? '') : '',
                "color"            => $variation && $variation->color ? ($variation->color->name ?? '') : '',
                "quantity"         => $quantity,
                "price"            => $finalPrice,
                "discount"         => $discountPerUnit,
                "original_price"   => $basePrice,
                "variation_id"     => $variation_id,
                "product_id"       => $product_id,
                "category_name"    => $product->category->name ?? '',
                "purchase_price"   => $product->purchase_prices,
                "image"            => $product->image,
                "is_stock"         => $is_stock,
                "is_free_shipping" => $product->is_free_shipping
            ];
        }

        session()->put('cart', $cart);

        // ✅ Facebook CAPI - Server Side Tracking Fixed
        if ($sendFacebookCapi) {
            try {
                // Deduplication check for CAPI based on Event ID
                $dedupeKey = 'capi_atc_' . $eventId;
                if (!session()->has($dedupeKey)) {
                    session()->put($dedupeKey, 1);
                    
                    FacebookConversion::sendAddToCart([
                        'content_ids' => [$product->id],
                        'value'       => $finalPrice * $quantity,
                        'currency'    => 'BDT',
                        'contents'    => [
                            [
                                'id'         => $product->id,
                                'quantity'   => $quantity,
                                'item_price' => $finalPrice,
                            ]
                        ]
                    ], $eventId); // ✅ Syncing the Event ID
                }
            } catch (\Exception $e) {
                Log::error('Facebook CAPI AddToCart Error: ' . $e->getMessage());
            }
        }

        $view         = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
        $total_item   = function_exists('getTotalCart') ? getTotalCart() : count($cart);
        $total_amount = function_exists('getTotalAmount') ? getTotalAmount() : 0;

        $actionType = $request->action_type ?? 'order';
        $url = ($actionType === 'cart')
            ? ''
            : route('front.checkouts.index');

        return [
            'ok' => true,
            'payload' => [
                'success' => true,
                'msg'     => 'Product added to cart successfully!',
                'html'    => $view,
                'item'    => $total_item,
                'amount'  => $total_amount,
                'url'     => $url
            ]
        ];
    }

    private function errorResponse(Request $request, string $msg, int $status = 422)
    {
        if (!$request->ajax() && !$request->expectsJson()) {
            return redirect()->back()->with('error', $msg);
        }
        return response()->json(['success'=>false, 'msg'=>$msg], $status);
    }

    public function storeCart(Request $request)
    {
        $res = $this->addToCart($request, true);

        if (!$res['ok']) {
            return $res['payload'];
        }

        return response()->json($res['payload']);
    }

    public function store(Request $request)
    {
        $res = $this->addToCart($request, false);

        if (!$res['ok']) {
            return $res['payload'];
        }

        if (!$request->ajax() && !$request->expectsJson()) {
            $actionType = $request->action_type ?? 'order';

            if ($actionType === 'cart') {
                return redirect()->route('front.carts.index')->with('success', 'Product added to cart successfully!');
            }

            return redirect()->route('front.checkouts.index')->with('success', 'Product added to cart successfully!');
        }

        return response()->json($res['payload']);
    }

    public function edit(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['success'=>false, 'msg'=>'Something Went Wrong!']);
        }

        $qty  = (int) $request->quantity;
        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return response()->json(['success'=>false, 'msg'=>'Item not found in cart!']);
        }

        $segm = $request->segment ? $request->segment : 'home';

        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $product_id   = (int) ($cart[$id]["product_id"] ?? 0);
            $variation_id = (int) ($cart[$id]["variation_id"] ?? 0);

            $product = Product::find($product_id);
            if (!$product) {
                unset($cart[$id]);
                session()->put('cart', $cart);
                return response()->json(['success'=>false, 'msg'=>'Product not found!']);
            }

            // Stock Check for Edit
            $is_stock = (int) ($product->is_stock ?? 0);
            if ($is_stock === 1) {
                $stockQty = $this->getAvailableStock($product, $variation_id);
                if ($stockQty < $qty) {
                    return response()->json(['success'=>false, 'msg'=>'Stock Not Available!']);
                }
            }

            $cart[$id]["quantity"] = $qty;
        }

        session()->put('cart', $cart);

        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += ((float)$item['price']) * ((int)$item['quantity']);
        }

        $view  = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
        $view2 = view('frontend.cart.details')->render();
        $view3 = view('frontend.cart.other_details', compact('totalPrice'))->render();

        return response()->json([
            'success' => true,
            'msg'     => 'Update cart successfully!',
            'html'    => $view,
            'html2'   => $view2,
            'html3'   => $view3,
            'segment' => $segm
        ]);
    }

    public function destroy($id)
    {
        if (!$id) {
            return response()->json(['success'=>false, 'msg'=>'Something Went Wrong!']);
        }

        $segm = request()->segment(1) ?? 'home';

        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += ((float)$item['price']) * ((int)$item['quantity']);
        }

        $view       = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();
        $view2      = view('frontend.cart.details')->render();
        $view3      = view('frontend.cart.other_details', compact('totalPrice'))->render();
        $total_item = function_exists('getTotalCart') ? getTotalCart() : count($cart);
        $url        = route('front.home');

        return response()->json([
          'success' => true,
          'msg'     => 'Product removed successfully!',
          'html'    => $view,
          'html2'   => $view2,
          'html3'   => $view3,
          'item'    => $total_item,
          'segment' => $segm,
          'url'     => $url,
        ]);
    }

    public function clearAll()
    {
        session()->put('cart', []);
        return redirect()->route('front.home');
    }
}
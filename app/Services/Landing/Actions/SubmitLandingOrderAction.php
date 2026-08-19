<?php

namespace App\Services\Landing\Actions;

use App\Http\Traits\DetectsOrderSource;
use App\Jobs\SendOrderNotification;
use App\Models\DeliveryCharge;
use App\Models\DynamicLandingPageComponent;
use App\Models\Information;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variation;
use App\Services\Landing\Contracts\LandingTransactionalActionHandler;
use App\Services\Landing\LandingActionResult;
use App\Services\Landing\LandingComponentConfigService;
use App\Services\Landing\LandingComponentRegistry;
use App\Services\Landing\LandingRenderSupport;
use App\Utils\ModulUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SubmitLandingOrderAction implements LandingTransactionalActionHandler
{
    use DetectsOrderSource;

    public function __construct(
        private LandingComponentConfigService $configService,
        private LandingComponentRegistry $componentRegistry,
        private LandingRenderSupport $renderSupport,
        private ModulUtil $modulUtil
    ) {
    }

    public function key(): string
    {
        return 'order-submission';
    }

    public function supportedComponentKeys(): array
    {
        return [
            'product-grid-v1',
            'seed-checkout-v1',
            'seed-checkout-v2',
            'seed-mobile-checkout-sticky-v1',
            'bari12-checkout-form-v1',
            'sheikh-checkout-form-v1',
        ];
    }

    public function handle(
        DynamicLandingPageComponent $component,
        array $payload,
        Request $request
    ): LandingActionResult {
        $data = validator($payload, [
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'variation_id' => ['nullable', 'integer', 'min:1'],
            'first_name' => ['required', 'string', 'max:200'],
            'mobile' => ['required', 'digits:11'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'delivery_charge_id' => ['nullable', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:50', 'in:cod,cash on delivery,cash_on_delivery'],
        ])->validate();

        $quantity = (int) ($data['quantity'] ?? 1);
        $product = Product::query()
            ->withSum('variations', 'stock_quantity')
            ->whereKey($data['product_id'])
            ->lockForUpdate()
            ->first();

        if (!$product || !$this->isVisibleProduct($product)) {
            throw ValidationException::withMessages([
                'product_id' => ['The selected product is not available.'],
            ]);
        }

        $this->ensureProductAllowed($component, $product);

        $variation = $this->resolveVariation($product, $data['variation_id'] ?? null);
        $this->ensureStockAvailable($product, $variation, $quantity);

        $priceInfo = $this->resolveCustomPackagePriceDiscount($component, $product, $variation, $quantity)
            ?? $this->resolvePrice($product, $variation);
        $lineTotal = $priceInfo['line_total'] ?? round($priceInfo['unit_price'] * $quantity, 2);
        $discountTotal = $priceInfo['discount_total'] ?? round($priceInfo['discount'] * $quantity, 2);
        $originalAmount = $priceInfo['amount'] ?? round($lineTotal + $discountTotal, 2);
        $this->ensureOrderLimits($quantity, $lineTotal);
        $this->rejectRecentDuplicateOrder($data, $request);
        $shippingCharge = $this->resolveShippingCharge($product, $quantity, $data['delivery_charge_id'] ?? null);
        $user = $this->resolveCustomer($data);

        $orderData = [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'invoice_no' => (string) random_int(111111, 999999),
            'first_name' => $data['first_name'],
            'mobile' => $data['mobile'],
            'shipping_address' => $data['shipping_address'],
            'ip_address' => $request->ip(),
            'note' => $data['note'] ?? null,
            'delivery_charge_id' => $data['delivery_charge_id'] ?? null,
            'payment_method' => 'cod',
            'payment_status' => 'due',
            'status' => 'pending',
            'amount' => $originalAmount,
            'discount' => $discountTotal,
            'shipping_charge' => $shippingCharge,
            'final_amount' => $lineTotal + $shippingCharge,
            'assign_user_id' => null,
        ];

        $this->applyOrderSource($orderData, $request);

        $orderDetailData = [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $priceInfo['unit_price'],
            'discount' => $priceInfo['discount'],
            'is_stock' => $product->is_stock ?? 1,
            'purchase_price' => $priceInfo['purchase_price'],
            'variation_id' => $variation?->id,
        ];

        $order = $this->createOrReuseIncompleteOrder($user, $data, $orderData, $orderDetailData);

        $this->decrementStock($product, $variation, $quantity);
        $this->modulUtil->orderPayment($order, []);
        $this->modulUtil->orderstatus($order);
        SendOrderNotification::dispatchAfterResponse($order);

        return LandingActionResult::success([
            'success' => true,
            'message' => 'Order created successfully.',
            'order_id' => $order->id,
            'url' => route('front.confirmOrderlanding', [$order->id]),
        ], $order->id, 201);
    }

    private function ensureProductAllowed(DynamicLandingPageComponent $component, Product $product): void
    {
        $config = $this->configService->resolveForRendering($component->component_key, $component->config ?? []);
        $dataSource = $config['data_source'] ?? [];
        $type = $dataSource['type'] ?? 'manual';

        if ($type === 'manual') {
            $allowedProductIds = $this->normalizeIds($dataSource['product_ids'] ?? []);

            if (in_array($product->id, $allowedProductIds, true)) {
                return;
            }
        }

        if ($type === 'category') {
            $allowedCategoryIds = $this->normalizeIds($dataSource['category_ids'] ?? []);

            if (
                in_array((int) $product->category_id, $allowedCategoryIds, true)
                && $this->categorySourceIncludesProduct($product, $allowedCategoryIds, $dataSource)
            ) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'product_id' => ['The selected product is not allowed for this landing component.'],
        ]);
    }

    private function resolveVariation(Product $product, mixed $variationId): ?Variation
    {
        if (!$variationId) {
            return $product->variations()
                ->orderBy('id')
                ->lockForUpdate()
                ->first();
        }

        $variation = $product->variations()
            ->whereKey((int) $variationId)
            ->lockForUpdate()
            ->first();

        if (!$variation) {
            throw ValidationException::withMessages([
                'variation_id' => ['The selected product option is not available.'],
            ]);
        }

        return $variation;
    }

    private function categorySourceIncludesProduct(
        Product $product,
        array $allowedCategoryIds,
        array $dataSource
    ): bool {
        $limit = max(1, min((int) ($dataSource['limit'] ?? 8), 24));

        return Product::query()
            ->where(fn ($query) => $query->whereNull('status')->orWhereIn('status', [1, '1', true, 'active', 'Active']))
            ->whereIn('category_id', $allowedCategoryIds)
            ->latest()
            ->limit($limit)
            ->pluck('id')
            ->contains($product->id);
    }

    private function ensureOrderLimits(int $quantity, float $lineTotal): void
    {
        $info = Information::first();

        if (($info?->max_order_qty ?? 0) > 0 && $quantity > (int) $info->max_order_qty) {
            throw ValidationException::withMessages([
                'quantity' => ["You can order a maximum of {$info->max_order_qty} items at a time."],
            ]);
        }

        if (($info?->max_order_amount ?? 0) > 0 && $lineTotal > (float) $info->max_order_amount) {
            throw ValidationException::withMessages([
                'quantity' => ["Your order amount cannot exceed {$info->max_order_amount}."],
            ]);
        }
    }

    private function rejectRecentDuplicateOrder(array $data, Request $request): void
    {
        $info = Information::first();

        if (!$info) {
            return;
        }

        $mobile = (string) ($data['mobile'] ?? '');
        $ipAddress = (string) $request->ip();
        $appliesMobileCheck = (int) ($info->is_mobile_check ?? 0) === 1 && $mobile !== '';
        $appliesIpCheck = (int) ($info->is_ip_check ?? 0) === 1 && $ipAddress !== '';

        if (!$appliesMobileCheck && !$appliesIpCheck) {
            return;
        }

        $limitMinutes = (int) ($info->time_limit ?? 60);
        $limitMinutes = $limitMinutes > 0 ? $limitMinutes : 60;

        $recentOrder = Order::query()
            ->whereNot('status', 'incomplete')
            ->where(function ($query) use ($appliesMobileCheck, $appliesIpCheck, $mobile, $ipAddress) {
                if ($appliesMobileCheck) {
                    $query->where('mobile', $mobile);
                }

                if ($appliesIpCheck) {
                    $appliesMobileCheck
                        ? $query->orWhere('ip_address', $ipAddress)
                        : $query->where('ip_address', $ipAddress);
                }
            })
            ->where('created_at', '>=', now()->subMinutes($limitMinutes))
            ->latest()
            ->first();

        if (!$recentOrder) {
            return;
        }

        throw ValidationException::withMessages([
            'mobile' => ['You already have a recent order. Please try again later.'],
        ]);
    }

    private function createOrReuseIncompleteOrder(
        User $user,
        array $data,
        array $orderData,
        array $orderDetailData
    ): Order {
        $order = Order::query()
            ->where('status', 'incomplete')
            ->where(function ($query) use ($user, $data) {
                $query->where('user_id', $user->id);

                if (!empty($data['mobile'])) {
                    $query->orWhere('mobile', $data['mobile']);
                }
            })
            ->latest()
            ->lockForUpdate()
            ->first();

        if (!$order) {
            $order = Order::create($this->onlyExistingColumns('orders', $orderData));
        } else {
            $order->details()->delete();
            $order->update($this->onlyExistingColumns('orders', $orderData));
        }

        $order->details()->create($this->onlyExistingColumns('order_details', $orderDetailData));

        return $order;
    }

    private function ensureStockAvailable(Product $product, ?Variation $variation, int $quantity): void
    {
        if ((int) ($product->is_stock ?? 1) !== 1) {
            return;
        }

        $stock = $variation
            ? (int) ($variation->stock_quantity ?? 0)
            : (int) ($product->stock_quantity ?? 0);

        if ($stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Stock is not available for the selected quantity.'],
            ]);
        }
    }

    private function decrementStock(Product $product, ?Variation $variation, int $quantity): void
    {
        if ((int) ($product->is_stock ?? 1) !== 1) {
            return;
        }

        if ($variation) {
            $variation->decrement('stock_quantity', $quantity);

            return;
        }

        $product->decrement('stock_quantity', $quantity);
    }

    private function resolveCustomer(array $data): User
    {
        $baseUsername = Str::slug($data['first_name'], '') ?: 'customer';
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->where('mobile', '!=', $data['mobile'])->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return User::updateOrCreate(
            ['mobile' => $data['mobile']],
            [
                'first_name' => $data['first_name'],
                'username' => $username,
                'shipping_address' => $data['shipping_address'],
                'note' => $data['note'] ?? null,
                'status' => 1,
            ]
        );
    }

    private function resolvePrice(Product $product, ?Variation $variation): array
    {
        $basePrice = (float) ($variation?->price ?? $product->sell_price ?? 0);
        $unitPrice = (float) (
            $variation?->after_discount_price
            ?? $product->after_discount
            ?? 0
        );

        if ($unitPrice <= 0) {
            $unitPrice = $basePrice;
        }

        return [
            'unit_price' => $unitPrice,
            'discount' => max(0, $basePrice - $unitPrice),
            'purchase_price' => (float) ($variation?->purchase_price ?? $product->purchase_price ?? $product->purchase_prices ?? 0),
        ];
    }

    private function resolveCustomPackagePriceDiscount(
        DynamicLandingPageComponent $component,
        Product $product,
        ?Variation $variation,
        int $quantity
    ): ?array {
        $quantity = max(1, $quantity);
        $storedContent = is_array($component->config['content'] ?? null)
            ? $component->config['content']
            : [];
        $packages = $storedContent['packages'] ?? [];

        if (!is_array($packages)) {
            return null;
        }

        $package = collect($packages)->first(function ($package) use ($quantity) {
            return is_array($package)
                && max(1, (int) ($package['quantity'] ?? 1)) === $quantity
                && array_key_exists('price', $package);
        });

        if (!is_array($package)) {
            return null;
        }

        try {
            $definition = $this->componentRegistry->get($component->component_key);
        } catch (Throwable) {
            $definition = null;
        }

        if ($definition && $this->renderSupport->customCheckoutPackagePrice($package, $definition) === null) {
            return null;
        }

        $customTotal = $this->renderSupport->parseMoneyValue($package['price'] ?? null);

        if ($customTotal === null || $customTotal <= 0) {
            return null;
        }

        $originalUnitPrice = (float) ($variation?->price ?: $product->sell_price ?: $product->regular_price ?: 0);
        $originalTotal = $originalUnitPrice * $quantity;

        if ($originalTotal <= 0) {
            return null;
        }

        $customTotal = min($customTotal, $originalTotal);
        $unitPrice = round($customTotal / $quantity, 2);

        return [
            'unit_price' => $unitPrice,
            'discount' => round(($originalTotal - $customTotal) / $quantity, 2),
            'line_total' => $customTotal,
            'discount_total' => max(0, round($originalTotal - $customTotal, 2)),
            'amount' => $originalTotal,
            'original_unit_price' => $originalUnitPrice,
            'original_total' => $originalTotal,
            'purchase_price' => (float) ($variation?->purchase_price ?? $product->purchase_price ?? $product->purchase_prices ?? 0),
        ];
    }

    private function resolveShippingCharge(Product $product, int $quantity, mixed $deliveryChargeId): float
    {
        if ((int) ($product->is_free_shipping ?? 0) === 1) {
            return 0;
        }

        if (!$deliveryChargeId) {
            return 0;
        }

        $deliveryCharge = DeliveryCharge::find((int) $deliveryChargeId);

        return $deliveryCharge ? (float) $deliveryCharge->amount : 0;
    }

    private function isVisibleProduct(Product $product): bool
    {
        return $product->status === null
            || in_array($product->status, [1, '1', true, 'active', 'Active'], true);
    }

    private function applyOrderSource(array &$orderData, Request $request): void
    {
        if (!Schema::hasColumn('orders', 'order_source')) {
            if (Schema::hasColumn('orders', 'referer_url')) {
                $orderData['referer_url'] = (string) $request->headers->get('referer', '');
            }

            return;
        }

        $source = $this->detectOrderSource();

        $orderData['order_source'] = $source['source'];
        $orderData['utm_source'] = $source['utm_source'];
        $orderData['utm_medium'] = $source['utm_medium'];
        $orderData['utm_campaign'] = $source['utm_campaign'];
        $orderData['referer_url'] = $source['referer'] ?: (string) $request->headers->get('referer', '');
    }

    private function normalizeIds(mixed $ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $key) => Schema::hasColumn($table, $key))
            ->all();
    }
}

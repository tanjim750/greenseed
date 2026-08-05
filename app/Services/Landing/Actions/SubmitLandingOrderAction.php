<?php

namespace App\Services\Landing\Actions;

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
use App\Utils\ModulUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SubmitLandingOrderAction implements LandingTransactionalActionHandler
{
    public function __construct(
        private LandingComponentConfigService $configService,
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
        $this->ensureOrderLimits($product, $variation, $quantity);
        $this->ensureStockAvailable($product, $variation, $quantity);

        $user = $this->resolveCustomer($data);
        $priceInfo = $this->resolvePrice($product, $variation);
        $lineTotal = $priceInfo['unit_price'] * $quantity;
        $discountTotal = $priceInfo['discount'] * $quantity;
        $shippingCharge = $this->resolveShippingCharge($product, $quantity, $data['delivery_charge_id'] ?? null);

        $orderData = [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'invoice_no' => (string) random_int(111111, 999999),
            'first_name' => $data['first_name'],
            'mobile' => $data['mobile'],
            'shipping_address' => $data['shipping_address'],
            'note' => $data['note'] ?? null,
            'delivery_charge_id' => $data['delivery_charge_id'] ?? null,
            'payment_method' => 'cod',
            'payment_status' => 'due',
            'status' => 'pending',
            'amount' => $lineTotal + $discountTotal,
            'discount' => $discountTotal,
            'shipping_charge' => $shippingCharge,
            'final_amount' => $lineTotal + $shippingCharge,
            'assign_user_id' => null,
        ];

        if (Schema::hasColumn('orders', 'order_source')) {
            $orderData['order_source'] = 'dynamic_landing_page';
        }

        if (Schema::hasColumn('orders', 'referer_url')) {
            $orderData['referer_url'] = (string) $request->headers->get('referer', '');
        }

        $order = Order::create($this->onlyExistingColumns('orders', $orderData));
        $order->details()->create($this->onlyExistingColumns('order_details', [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $priceInfo['unit_price'],
            'discount' => $priceInfo['discount'],
            'is_stock' => $product->is_stock ?? 1,
            'purchase_price' => $priceInfo['purchase_price'],
            'variation_id' => $variation?->id,
        ]));

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

    private function ensureOrderLimits(Product $product, ?Variation $variation, int $quantity): void
    {
        $info = Information::first();
        $priceInfo = $this->resolvePrice($product, $variation);
        $lineTotal = $priceInfo['unit_price'] * $quantity;

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

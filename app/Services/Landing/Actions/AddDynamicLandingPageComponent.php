<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Models\Product;
use App\Services\Landing\LandingComponentConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AddDynamicLandingPageComponent
{
    private const CHECKOUT_COMPONENT_KEYS = [
        'seed-checkout-v1',
        'seed-checkout-v2',
        'seed-mobile-checkout-sticky-v1',
        'bari12-checkout-form-v1',
        'sheikh-checkout-form-v1',
    ];

    public function __construct(
        private LandingComponentConfigService $configService
    ) {
    }

    public function execute(
        DynamicLandingPage $page,
        string $componentKey,
        array $config = []
    ): DynamicLandingPageComponent {
        $config = $this->clearPackageItemsWithoutProduct($componentKey, $config);
        $config = $this->prefillPackageItemsFromProduct($componentKey, $config);
        $validatedConfig = $this->configService->validateForStorage($componentKey, $config);

        return DB::transaction(function () use ($page, $componentKey, $validatedConfig) {
            DynamicLandingPage::whereKey($page->getKey())->lockForUpdate()->first();

            $nextOrder = ((int) $page->components()->max('sort_order')) + 1;

            return $page->components()->create([
                'component_key' => $componentKey,
                'instance_scope' => $this->generateInstanceScope(),
                'sort_order' => $nextOrder,
                'config' => $validatedConfig,
                'is_enabled' => true,
            ]);
        });
    }

    private function generateInstanceScope(): string
    {
        do {
            $scope = 'cmp_' . Str::lower(Str::random(12));
        } while (DynamicLandingPageComponent::where('instance_scope', $scope)->exists());

        return $scope;
    }

    private function clearPackageItemsWithoutProduct(string $componentKey, array $config): array
    {
        if (!in_array($componentKey, self::CHECKOUT_COMPONENT_KEYS, true)) {
            return $config;
        }

        $productIds = collect($config['data_source']['product_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0);

        if ($productIds->isNotEmpty() || !is_array($config['content']['packages'] ?? null)) {
            return $config;
        }

        foreach ($config['content']['packages'] as $index => $package) {
            if (!is_array($package)) {
                continue;
            }

            $config['content']['packages'][$index] = [
                'quantity' => max(1, (int) ($package['quantity'] ?? 1)),
                'title' => null,
                'subtitle' => null,
                'price' => null,
                'badge' => null,
            ];
        }

        return $config;
    }

    private function prefillPackageItemsFromProduct(string $componentKey, array $config): array
    {
        if (!in_array($componentKey, self::CHECKOUT_COMPONENT_KEYS, true) || !is_array($config['content']['packages'] ?? null)) {
            return $config;
        }

        $product = $this->selectedProduct($config);

        if (!$product) {
            return $config;
        }

        $unitPrice = $this->orderBasePrice($product);

        foreach ($config['content']['packages'] as $index => $package) {
            if (!is_array($package)) {
                continue;
            }

            $quantity = max(1, (int) ($package['quantity'] ?? 1));
            $package['quantity'] = $quantity;

            if ($this->isBlank($package['title'] ?? null)) {
                $package['title'] = $quantity > 1
                    ? $quantity . ' x ' . (string) $product->name
                    : (string) $product->name;
            }

            if ($this->isBlank($package['subtitle'] ?? null)) {
                $package['subtitle'] = $product->sku
                    ? 'SKU: ' . $product->sku
                    : ($product->availability_text ?: null);
            }

            if ($this->isBlank($package['price'] ?? null)) {
                $package['price'] = $this->formatMoney($unitPrice * $quantity);
                $package['__price_custom'] = false;
            }

            $config['content']['packages'][$index] = $package;
        }

        return $config;
    }

    private function selectedProduct(array $config): ?Product
    {
        $productId = collect($config['data_source']['product_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->first();

        if (!$productId) {
            return null;
        }

        return Product::query()
            ->whereKey($productId)
            ->where(fn ($query) => $query->whereNull('status')->orWhereIn('status', [1, '1', true, 'active', 'Active']))
            ->first();
    }

    private function orderBasePrice(Product $product): float
    {
        $variation = $product->variations()->orderBy('id')->first();

        return (float) ($variation?->price ?: $product->sell_price ?: $product->regular_price ?: 0);
    }

    private function formatMoney(float $amount): string
    {
        return function_exists('priceFormate')
            ? priceFormate($amount)
            : number_format($amount, 2);
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}

<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPageComponent;
use App\Models\Product;
use App\Services\Landing\LandingComponentConfigService;

final class UpdateDynamicLandingPageComponentConfig
{
    private const CUSTOM_PRICE_FLAG = '__price_custom';
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
        DynamicLandingPageComponent $component,
        array $config
    ): DynamicLandingPageComponent {
        $config = $this->clearPackageItemsWithoutProduct($component, $config);
        $config = $this->prefillPackageItemsFromProduct($component, $config);
        $config = $this->annotateCustomPackagePrices($component, $config);

        $component->update([
            'config' => $this->configService->validateForStorage(
                $component->component_key,
                $config
            ),
        ]);

        return $component->refresh();
    }

    private function clearPackageItemsWithoutProduct(DynamicLandingPageComponent $component, array $config): array
    {
        if (!in_array($component->component_key, self::CHECKOUT_COMPONENT_KEYS, true)) {
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

    private function prefillPackageItemsFromProduct(DynamicLandingPageComponent $component, array $config): array
    {
        if (!in_array($component->component_key, self::CHECKOUT_COMPONENT_KEYS, true) || !is_array($config['content']['packages'] ?? null)) {
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
                $package[self::CUSTOM_PRICE_FLAG] = false;
            }

            $config['content']['packages'][$index] = $package;
        }

        return $config;
    }

    private function annotateCustomPackagePrices(DynamicLandingPageComponent $component, array $config): array
    {
        if (!is_array($config['content']['packages'] ?? null)) {
            return $config;
        }

        $oldPackages = is_array($component->config['content']['packages'] ?? null)
            ? $component->config['content']['packages']
            : [];

        foreach ($config['content']['packages'] as $index => $package) {
            if (!is_array($package)) {
                continue;
            }

            $oldPackage = $this->matchingOldPackage($oldPackages, $package, $index);
            $hasPrice = array_key_exists('price', $package) && trim((string) $package['price']) !== '';

            if (!$hasPrice) {
                unset($package[self::CUSTOM_PRICE_FLAG]);
                $config['content']['packages'][$index] = $package;
                continue;
            }

            $hadCustomPrice = is_array($oldPackage)
                && $this->truthy($oldPackage[self::CUSTOM_PRICE_FLAG] ?? false);
            $priceChanged = is_array($oldPackage)
                ? $this->normalizePrice($package['price'] ?? null) !== $this->normalizePrice($oldPackage['price'] ?? null)
                : true;
            $oldPriceBlank = !is_array($oldPackage) || $this->isBlank($oldPackage['price'] ?? null);

            if (array_key_exists(self::CUSTOM_PRICE_FLAG, $package) && !$this->truthy($package[self::CUSTOM_PRICE_FLAG])) {
                $package[self::CUSTOM_PRICE_FLAG] = $priceChanged && !$oldPriceBlank;
                $config['content']['packages'][$index] = $package;
                continue;
            }

            if ($hadCustomPrice || $priceChanged || $this->truthy($package[self::CUSTOM_PRICE_FLAG] ?? false)) {
                $package[self::CUSTOM_PRICE_FLAG] = true;
            } else {
                unset($package[self::CUSTOM_PRICE_FLAG]);
            }

            $config['content']['packages'][$index] = $package;
        }

        return $config;
    }

    private function matchingOldPackage(array $oldPackages, array $package, int $index): ?array
    {
        $quantity = max(1, (int) ($package['quantity'] ?? 1));

        $matched = collect($oldPackages)->first(function ($oldPackage) use ($quantity) {
            return is_array($oldPackage)
                && max(1, (int) ($oldPackage['quantity'] ?? 1)) === $quantity;
        });

        if (is_array($matched)) {
            return $matched;
        }

        return is_array($oldPackages[$index] ?? null) ? $oldPackages[$index] : null;
    }

    private function normalizePrice(mixed $price): string
    {
        return trim((string) $price);
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

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value !== 0.0;
        }

        if (!is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }
}

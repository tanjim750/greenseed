<?php

namespace App\Services\Landing\DataResolvers;

use App\Models\DynamicLandingPageComponent;
use App\Models\Product;
use App\Services\Landing\Contracts\LandingComponentDataResolver;

final class ProductGridResolver implements LandingComponentDataResolver
{
    public function resolve(
        DynamicLandingPageComponent $component,
        array $config
    ): array {
        $dataSource = $config['data_source'] ?? [];
        $type = $dataSource['type'] ?? 'manual';

        $products = match ($type) {
            'manual' => $this->resolveManualProducts($dataSource),
            'category' => $this->resolveCategoryProducts($dataSource),
            default => collect(),
        };

        return [
            'products' => $products,
        ];
    }

    private function resolveManualProducts(array $dataSource)
    {
        $productIds = $this->normalizeIds($dataSource['product_ids'] ?? []);

        if (empty($productIds)) {
            return collect();
        }

        return Product::query()
            ->with(['images', 'variations'])
            ->withSum('variations', 'stock_quantity')
            ->where(fn ($query) => $this->applyVisibleProductFilter($query))
            ->whereIn('id', $productIds)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $productIds, true))
            ->values()
            ->map(fn (Product $product) => $this->presentProduct($product));
    }

    private function resolveCategoryProducts(array $dataSource)
    {
        $categoryIds = $this->normalizeIds($dataSource['category_ids'] ?? []);
        $limit = max(1, min((int) ($dataSource['limit'] ?? 8), 24));

        if (empty($categoryIds)) {
            return collect();
        }

        return Product::query()
            ->with(['images', 'variations'])
            ->withSum('variations', 'stock_quantity')
            ->where(fn ($query) => $this->applyVisibleProductFilter($query))
            ->whereIn('category_id', $categoryIds)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => $this->presentProduct($product));
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

    private function presentProduct(Product $product): array
    {
        $priceInfo = function_exists('getProductInfo')
            ? getProductInfo($product)
            : [
                'price' => $product->after_discount > 0 ? $product->after_discount : $product->sell_price,
                'old_price' => $product->sell_price,
                'discount_amount' => $product->dicount_amount,
            ];

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'url' => route('front.products.show', $product),
            'image_url' => getImage('products', $product->image),
            'gallery' => $product->images
                ->map(fn ($image) => getImage('products', $image->image ?? null))
                ->values()
                ->all(),
            'price' => $priceInfo['price'] ?? 0,
            'old_price' => $priceInfo['old_price'] ?? 0,
            'discount_amount' => $priceInfo['discount_amount'] ?? 0,
            'formatted_price' => priceFormate($priceInfo['price'] ?? 0),
            'formatted_old_price' => priceFormate($priceInfo['old_price'] ?? 0),
            'stock' => $product->total_stock,
            'availability_text' => $product->availability_text,
            'variations' => $product->variations
                ->map(fn ($variation) => [
                    'id' => $variation->id,
                    'title' => $this->presentVariationTitle($variation),
                    'stock' => (int) ($variation->stock_quantity ?? 0),
                ])
                ->values()
                ->all(),
        ];
    }

    private function presentVariationTitle($variation): string
    {
        $title = trim((string) ($variation->title ?? $variation->name ?? $variation->label ?? ''));

        return $title !== '' ? $title : 'Variant';
    }

    private function applyVisibleProductFilter($query): void
    {
        $query->whereNull('status')
            ->orWhereIn('status', [1, '1', true, 'active', 'Active']);
    }
}

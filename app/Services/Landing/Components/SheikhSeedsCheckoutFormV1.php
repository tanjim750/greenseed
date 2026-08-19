<?php

namespace App\Services\Landing\Components;

use App\Models\DynamicLandingPageComponent;
use App\Models\Product;

final class SheikhSeedsCheckoutFormV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'sheikh-checkout-form-v1';
    }

    public function name(): string
    {
        return 'Sheikh Seeds Checkout Form';
    }

    public function category(): string
    {
        return 'sheikh seeds stitch';
    }

    public function view(): string
    {
        return 'landing.components.sheikh-checkout-form-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'textarea', 'label' => 'Heading'],
                'products_heading' => ['type' => 'text', 'label' => 'Products Heading'],
                'billing_heading' => ['type' => 'text', 'label' => 'Billing Heading'],
                'shipping_heading' => ['type' => 'text', 'label' => 'Shipping Heading'],
                'shipping_label' => ['type' => 'text', 'label' => 'Shipping Label'],
                'packages' => ['type' => 'repeater', 'label' => 'Packages'],
                'order_heading' => ['type' => 'text', 'label' => 'Order Heading'],
                'payment_title' => ['type' => 'text', 'label' => 'Payment Title'],
                'payment_description' => ['type' => 'textarea', 'label' => 'Payment Description'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'card_background' => ['type' => 'color', 'label' => 'Card Background'],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
                'button_color' => ['type' => 'color', 'label' => 'Button Color'],
                'button_text_color' => ['type' => 'color', 'label' => 'Button Text Color'],
                'border_color' => ['type' => 'color', 'label' => 'Border Color'],
            ]),
            'settings' => [
                'default_quantity' => ['type' => 'number', 'label' => 'Default Quantity', 'min' => 1],
            ],
            'data_source' => [
                'product_ids' => ['type' => 'product_selector', 'label' => 'Order Product', 'multiple' => false],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'heading' => 'নিশ্চিন্তে অর্ডার করুন। অর্ডার করার পরে আমরা আপনাকে কল দিয়ে বিস্তারিত বলে কনফার্ম করবো।',
                'products_heading' => 'আপনার পছন্দের প্রোডাক্ট সিলেক্ট করুন।',
                'billing_heading' => 'Billing details',
                'shipping_heading' => 'Shipping',
                'shipping_label' => 'সারা বাংলাদেশে দ্রুত ডেলিভারি।',
                'packages' => [
                    ['quantity' => 2, 'title' => '২ প্যাকেট কেজি বেগুনের বীজ ডেলিভারি চার্জ ফ্রী', 'subtitle' => '', 'price' => '299.00৳'],
                    ['quantity' => 1, 'title' => '১ প্যাকেট কেজি বেগুনের বীজ', 'subtitle' => '', 'price' => '229.00৳'],
                ],
                'order_heading' => 'Your order',
                'payment_title' => 'Cash on delivery',
                'payment_description' => 'পণ্য হাতে পেয়ে পেমেন্ট করুন।',
                'button_text' => 'অর্ডার কনফার্ম করুন',
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#ffffff',
                'card_background' => '#ffffff',
                'primary_color' => '#22734e',
                'button_color' => '#f97316',
                'button_text_color' => '#ffffff',
                'border_color' => '#16a34a',
            ]),
            'settings' => [
                'default_quantity' => 2,
            ],
            'behaviours' => [],
            'data_source' => [
                'product_ids' => [],
            ],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.*' => ['nullable'],
            'content.packages' => ['nullable', 'array', 'min:1', 'max:6'],
            'content.packages.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'content.packages.*.title' => ['nullable', 'string', 'max:220'],
            'content.packages.*.subtitle' => ['nullable', 'string', 'max:220'],
            'content.packages.*.price' => ['nullable', 'string', 'max:40'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['required', 'array'],
            'settings.default_quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'data_source' => ['required', 'array'],
            'data_source.product_ids' => ['nullable', 'array'],
            'data_source.product_ids.*' => ['integer', 'min:1'],
            'behaviours' => ['nullable', 'array'],
        ];
    }

    public function behaviours(): array
    {
        return ['order-submission'];
    }

    public function resolveData(DynamicLandingPageComponent $component, array $config): array
    {
        $productId = collect($config['data_source']['product_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->first();

        if (!$productId) {
            return ['product' => null];
        }

        $product = Product::query()
            ->withSum('variations', 'stock_quantity')
            ->whereKey($productId)
            ->where(fn ($query) => $query->whereNull('status')->orWhereIn('status', [1, '1', true, 'active', 'Active']))
            ->first();

        if (!$product) {
            return ['product' => null];
        }

        $variation = $product->variations()->orderBy('id')->first();
        $price = (float) ($variation?->after_discount_price ?: $product->after_discount ?: $variation?->price ?: $product->sell_price ?: $product->regular_price ?: 0);
        $orderBasePrice = (float) ($variation?->price ?: $product->sell_price ?: $product->regular_price ?: 0);

        return [
            'product' => [
                'id' => (int) $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image_url' => function_exists('getImage') ? getImage('products', $product->image) : asset('products/' . $product->image),
                'price' => $price,
                'order_base_price' => $orderBasePrice,
                'formatted_price' => function_exists('priceFormate') ? priceFormate($price) : number_format($price, 2),
                'stock' => $product->total_stock,
                'availability_text' => $product->availability_text,
            ],
        ];
    }
}

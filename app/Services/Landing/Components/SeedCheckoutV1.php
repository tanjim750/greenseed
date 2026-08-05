<?php

namespace App\Services\Landing\Components;

use App\Models\DynamicLandingPageComponent;
use App\Models\Product;

final class SeedCheckoutV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-checkout-v1';
    }

    public function name(): string
    {
        return 'Seed Checkout Form';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-checkout-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'customer_heading' => ['type' => 'text', 'label' => 'Customer Heading'],
                'product_heading' => ['type' => 'text', 'label' => 'Product Heading'],
                'delivery_title' => ['type' => 'text', 'label' => 'Delivery Title'],
                'delivery_description' => ['type' => 'textarea', 'label' => 'Delivery Description'],
                'packages' => ['type' => 'repeater', 'label' => 'Packages'],
                'summary_title' => ['type' => 'text', 'label' => 'Summary Title'],
                'payment_note' => ['type' => 'text', 'label' => 'Payment Note'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'button_color' => ['type' => 'color', 'label' => 'Button Color'],
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
                'heading' => 'অর্ডার করতে নিচের ফর্মটি সঠিক ভাবে পূরণ করুন',
                'customer_heading' => 'আপনার তথ্য দিন',
                'product_heading' => 'পণ্য নির্বাচন করুন',
                'delivery_title' => 'ডেলিভারি চার্জ সম্পূর্ণ ফ্রি!',
                'delivery_description' => 'অর্ডার কনফার্ম করার পর ২-৩ দিনের মধ্যে হোম ডেলিভারি পাবেন ইনশাআল্লাহ।',
                'packages' => [
                    ['quantity' => 1, 'title' => '১ প্যাকেট বারি-১২ বেগুনের বীজ', 'subtitle' => '+ ১ প্যাকেট শসা বীজ ফ্রি', 'price' => '৳৩০০'],
                    ['quantity' => 2, 'title' => '২ প্যাকেট বারি-১২ বেগুনের বীজ', 'subtitle' => '+ ২ প্যাকেট শসা বীজ ফ্রি', 'price' => '৳৫৫০'],
                ],
                'summary_title' => 'অর্ডার সামারি',
                'payment_note' => 'পেমেন্ট মাধ্যম: ক্যাশ অন ডেলিভারি (পণ্য বুঝে পেয়ে টাকা দিন)',
                'button_text' => 'অর্ডার সম্পন্ন করুন',
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#eeeeea',
                'button_color' => '#0d631b',
            ]),
            'settings' => [
                'default_quantity' => 1,
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
            'content.heading' => ['nullable', 'string', 'max:180'],
            'content.customer_heading' => ['nullable', 'string', 'max:120'],
            'content.product_heading' => ['nullable', 'string', 'max:120'],
            'content.delivery_title' => ['nullable', 'string', 'max:120'],
            'content.delivery_description' => ['nullable', 'string', 'max:500'],
            'content.packages' => ['nullable', 'array', 'min:1', 'max:6'],
            'content.packages.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'content.packages.*.title' => ['nullable', 'string', 'max:160'],
            'content.packages.*.subtitle' => ['nullable', 'string', 'max:160'],
            'content.packages.*.price' => ['nullable', 'string', 'max:40'],
            'content.summary_title' => ['nullable', 'string', 'max:100'],
            'content.payment_note' => ['nullable', 'string', 'max:200'],
            'content.button_text' => ['nullable', 'string', 'max:80'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.button_color' => ['required'],
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

    public function resolveData(
        DynamicLandingPageComponent $component,
        array $config
    ): array {
        $productId = collect($config['data_source']['product_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->first();

        if (!$productId) {
            return [
                'product' => null,
            ];
        }

        $product = Product::query()
            ->withSum('variations', 'stock_quantity')
            ->whereKey($productId)
            ->where(fn ($query) => $query->whereNull('status')->orWhereIn('status', [1, '1', true, 'active', 'Active']))
            ->first();

        if (!$product) {
            return [
                'product' => null,
            ];
        }

        $price = (float) ($product->after_discount ?: $product->sell_price ?: $product->regular_price ?: 0);
        $oldPrice = (float) ($product->regular_price ?: $product->sell_price ?: 0);

        return [
            'product' => [
                'id' => (int) $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image_url' => function_exists('getImage') ? getImage('products', $product->image) : asset('products/' . $product->image),
                'price' => $price,
                'old_price' => $oldPrice,
                'formatted_price' => function_exists('priceFormate') ? priceFormate($price) : number_format($price, 2),
                'formatted_old_price' => function_exists('priceFormate') ? priceFormate($oldPrice) : number_format($oldPrice, 2),
                'stock' => $product->total_stock,
                'availability_text' => $product->availability_text,
            ],
        ];
    }
}

<?php

namespace App\Services\Landing\Components;

use App\Models\DynamicLandingPageComponent;
use App\Models\Product;

final class SeedMobileCheckoutStickyV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-mobile-checkout-sticky-v1';
    }

    public function name(): string
    {
        return 'Seed Mobile Checkout + Sticky CTA';
    }

    public function category(): string
    {
        return 'seed mobile';
    }

    public function view(): string
    {
        return 'landing.components.seed-mobile-checkout-sticky-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'customer_heading' => ['type' => 'text', 'label' => 'Customer Heading'],
                'product_heading' => ['type' => 'text', 'label' => 'Product Heading'],
                'summary_heading' => ['type' => 'text', 'label' => 'Summary Heading'],
                'guarantee_note' => ['type' => 'textarea', 'label' => 'Guarantee Note'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text'],
                'support_text' => ['type' => 'text', 'label' => 'Support Text'],
                'support_phone' => ['type' => 'text', 'label' => 'Support Phone'],
                'packages' => ['type' => 'repeater', 'label' => 'Packages'],
            ],
            'settings' => [
                'default_quantity' => ['type' => 'number', 'label' => 'Default Quantity', 'min' => 1],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'card_background' => ['type' => 'color', 'label' => 'Card Background'],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
                'soft_background' => ['type' => 'color', 'label' => 'Soft Background'],
                'border_color' => ['type' => 'color', 'label' => 'Border Color'],
            ]),
            'data_source' => [
                'product_ids' => ['type' => 'product_selector', 'label' => 'Order Product', 'multiple' => false],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'customer_heading' => 'অর্ডার করতে আপনার তথ্য দিন',
                'product_heading' => 'পণ্য নির্বাচন করুন',
                'summary_heading' => 'অর্ডার সারাংশ',
                'guarantee_note' => 'বীজ না গজালে আমরা আপনাকে নতুন বীজ অথবা টাকা ফেরত দেব ইন-শা-আল্লাহ।',
                'button_text' => 'অর্ডার কনফার্ম করুন',
                'support_text' => 'সহযোগিতার জন্য কল করুন',
                'support_phone' => '01897926161',
                'packages' => [
                    ['quantity' => 1, 'title' => '১ প্যাকেট বারি-১২ বেগুন + ১ প্যাকেট শসা', 'subtitle' => '', 'price' => '৩০০৳', 'badge' => ''],
                    ['quantity' => 2, 'title' => '২ প্যাকেট বারি-১২ বেগুন + ২ প্যাকেট শসা', 'subtitle' => '', 'price' => '৫৫০৳', 'badge' => 'সেরা ভ্যালু'],
                ],
            ],
            'settings' => [
                'default_quantity' => 1,
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#faf9f5',
                'card_background' => '#ffffff',
                'primary_color' => '#0d631b',
                'soft_background' => '#f4f4f0',
                'border_color' => '#bfcaba',
            ]),
            'data_source' => [
                'product_ids' => [],
            ],
            'behaviours' => [],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.customer_heading' => ['nullable', 'string', 'max:160'],
            'content.product_heading' => ['nullable', 'string', 'max:160'],
            'content.summary_heading' => ['nullable', 'string', 'max:160'],
            'content.guarantee_note' => ['nullable', 'string', 'max:500'],
            'content.button_text' => ['nullable', 'string', 'max:100'],
            'content.support_text' => ['nullable', 'string', 'max:120'],
            'content.support_phone' => ['nullable', 'string', 'max:30'],
            'content.packages' => ['nullable', 'array', 'min:1', 'max:6'],
            'content.packages.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'content.packages.*.title' => ['nullable', 'string', 'max:200'],
            'content.packages.*.subtitle' => ['nullable', 'string', 'max:200'],
            'content.packages.*.price' => ['nullable', 'string', 'max:40'],
            'content.packages.*.badge' => ['nullable', 'string', 'max:80'],
            'settings' => ['required', 'array'],
            'settings.default_quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
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

<?php

namespace App\Services\Landing\Components;

use App\Models\DynamicLandingPageComponent;
use App\Models\Product;

final class SeedCheckoutV2 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-checkout-v2';
    }

    public function name(): string
    {
        return 'Seed Checkout V2';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-checkout-v2';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'subheading' => ['type' => 'textarea', 'label' => 'Subheading'],
                'customer_heading' => ['type' => 'text', 'label' => 'Customer Heading'],
                'product_heading' => ['type' => 'text', 'label' => 'Product Heading'],
                'summary_heading' => ['type' => 'text', 'label' => 'Summary Heading'],
                'trust_title' => ['type' => 'text', 'label' => 'Trust Title'],
                'trust_description' => ['type' => 'textarea', 'label' => 'Trust Description'],
                'payment_title' => ['type' => 'text', 'label' => 'Payment Title'],
                'payment_description' => ['type' => 'textarea', 'label' => 'Payment Description'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text'],
                'secure_text' => ['type' => 'text', 'label' => 'Secure Text'],
                'whatsapp_text' => ['type' => 'text', 'label' => 'WhatsApp Text'],
                'whatsapp_url' => ['type' => 'url', 'label' => 'WhatsApp URL'],
                'packages' => ['type' => 'repeater', 'label' => 'Packages'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'card_background' => ['type' => 'color', 'label' => 'Card Background'],
                'soft_background' => ['type' => 'color', 'label' => 'Soft Background'],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
                'accent_color' => ['type' => 'color', 'label' => 'Accent Color'],
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
                'heading' => 'অর্ডার কনফার্ম করুন',
                'subheading' => 'সঠিক তথ্য দিয়ে আপনার অর্ডারটি সম্পন্ন করুন। আমরা দ্রুত আপনার সাথে যোগাযোগ করব।',
                'customer_heading' => 'আপনার তথ্য',
                'product_heading' => 'আপনার পণ্য',
                'summary_heading' => 'অর্ডার সারসংক্ষেপ',
                'trust_title' => 'বীজ না গজালে টাকা ফেরত!',
                'trust_description' => 'আমরা আমাদের বীজের গুণগত মান নিয়ে শতভাগ আত্মবিশ্বাসী।',
                'payment_title' => 'পেমেন্ট পদ্ধতি: ক্যাশ অন ডেলিভারি',
                'payment_description' => 'পণ্য হাতে পেয়ে টাকা পরিশোধ করুন। যদি কোনো কারণে পণ্য রিসিভ করতে না পারেন, তবে ডেলিভারি চার্জ দিয়ে দিবেন।',
                'button_text' => 'অর্ডার কনফার্ম করুন',
                'secure_text' => 'নিরাপদ এবং সুরক্ষিত অর্ডার',
                'whatsapp_text' => 'হোয়াটসআপ করুনঃ 01897926161',
                'whatsapp_url' => 'https://wa.me/01897926161',
                'packages' => [
                    ['quantity' => 1, 'title' => '১ পেকেট বারি-১২ বেগুনের বীজ + ১ পেকেট শসা বীজ', 'subtitle' => '', 'price' => '৩০০৳'],
                    ['quantity' => 2, 'title' => '২ পেকেট বারি-১২ বেগুনের বীজ + ২ পেকেট শসা বীজ', 'subtitle' => '', 'price' => '৫৫০৳'],
                ],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#faf9f5',
                'card_background' => '#ffffff',
                'soft_background' => '#f4f4f0',
                'primary_color' => '#0d631b',
                'accent_color' => '#2e7d32',
                'border_color' => '#bfcaba',
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
            'content.subheading' => ['nullable', 'string', 'max:500'],
            'content.customer_heading' => ['nullable', 'string', 'max:120'],
            'content.product_heading' => ['nullable', 'string', 'max:120'],
            'content.summary_heading' => ['nullable', 'string', 'max:120'],
            'content.trust_title' => ['nullable', 'string', 'max:140'],
            'content.trust_description' => ['nullable', 'string', 'max:500'],
            'content.payment_title' => ['nullable', 'string', 'max:140'],
            'content.payment_description' => ['nullable', 'string', 'max:500'],
            'content.button_text' => ['nullable', 'string', 'max:100'],
            'content.secure_text' => ['nullable', 'string', 'max:140'],
            'content.whatsapp_text' => ['nullable', 'string', 'max:160'],
            'content.whatsapp_url' => ['nullable', 'string', 'max:2048'],
            'content.packages' => ['nullable', 'array', 'min:1', 'max:6'],
            'content.packages.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'content.packages.*.title' => ['nullable', 'string', 'max:200'],
            'content.packages.*.subtitle' => ['nullable', 'string', 'max:200'],
            'content.packages.*.price' => ['nullable', 'string', 'max:40'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.card_background' => ['required'],
            'style.soft_background' => ['required'],
            'style.primary_color' => ['required'],
            'style.accent_color' => ['required'],
            'style.border_color' => ['required'],
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
        $oldPrice = (float) ($variation?->price ?: $product->regular_price ?: $product->sell_price ?: 0);

        return [
            'product' => [
                'id' => (int) $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image_url' => function_exists('getImage') ? getImage('products', $product->image) : asset('products/' . $product->image),
                'price' => $price,
                'order_base_price' => $orderBasePrice,
                'old_price' => $oldPrice,
                'formatted_price' => function_exists('priceFormate') ? priceFormate($price) : number_format($price, 2),
                'formatted_old_price' => function_exists('priceFormate') ? priceFormate($oldPrice) : number_format($oldPrice, 2),
                'stock' => $product->total_stock,
                'availability_text' => $product->availability_text,
            ],
        ];
    }
}

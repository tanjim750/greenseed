<?php

namespace App\Services\Landing\Components;

final class SeedBenefitsV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-benefits-v1';
    }

    public function name(): string
    {
        return 'Seed Benefits';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-benefits-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'feature_title' => ['type' => 'text', 'label' => 'Feature Title'],
                'feature_description' => ['type' => 'textarea', 'label' => 'Feature Description'],
                'feature_points' => ['type' => 'repeater', 'label' => 'Feature Points'],
                'cards' => ['type' => 'repeater', 'label' => 'Benefit Cards'],
                'trust_cards' => ['type' => 'repeater', 'label' => 'Trust Cards'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'accent_color' => ['type' => 'color', 'label' => 'Accent Color'],
            ]),
            'settings' => [],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'heading' => 'কেন বারি বেগুন-১২ চাষ করবেন?',
                'feature_title' => 'উচ্চ ফলনশীল ও পুষ্টিগুণে ভরপুর',
                'feature_description' => 'বারি বেগুন-১২ একটি নতুন জাতের উন্নত বেগুন, যা লবনাক্ত জমিতেও সফলভাবে চাষ করা যায়। এটি রোগ প্রতিরোধে সক্ষম এবং দীর্ঘ সময় ফলন দেয়।',
                'feature_points' => [
                    'প্রতিটি বেগুনের ওজন ৮০০ গ্রাম থেকে ১ কেজি পর্যন্ত হয়।',
                    'লবনাক্ততা এবং উচ্চ তাপমাত্রায় ফলন ভালো হয়।',
                ],
                'cards' => [
                    ['icon' => 'local_shipping', 'title' => 'ফ্রি ডেলিভারি', 'description' => 'সারা বাংলাদেশে দ্রুত এবং সম্পূর্ণ বিনামূল্যে ডেলিভারি চার্জ ছাড়া হোম ডেলিভারি।'],
                    ['icon' => 'menu_book', 'title' => 'গাইডলাইন বই', 'description' => 'বীজ রোপন পদ্ধতি ও পরিচর্যার জন্য একটি বিস্তারিত গাইডলাইন বই উপহার।'],
                ],
                'trust_cards' => [
                    ['icon' => 'verified', 'title' => '১০০% অরিজিনাল বীজ', 'description' => 'আমরা সরাসরি বিশ্বস্ত উৎস থেকে সংগৃহীত এ গ্রেড কোয়ালিটির হাইব্রিড বীজ সরবরাহ করি।'],
                    ['icon' => 'sentiment_satisfied', 'title' => 'মানি ব্যাক গ্যারান্টি', 'description' => 'বীজ না গজালে টাকা ফেরতের ১০০% নিশ্চয়তা দিচ্ছি আমরা।'],
                ],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#ffffff',
                'accent_color' => '#ffb300',
            ]),
            'settings' => [],
            'behaviours' => [],
            'data_source' => [],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.heading' => ['nullable', 'string', 'max:160'],
            'content.feature_title' => ['nullable', 'string', 'max:160'],
            'content.feature_description' => ['nullable', 'string', 'max:1000'],
            'content.feature_points' => ['nullable', 'array'],
            'content.feature_points.*' => ['nullable', 'string', 'max:300'],
            'content.cards' => ['nullable', 'array'],
            'content.cards.*.icon' => ['nullable', 'string', 'max:80'],
            'content.cards.*.title' => ['nullable', 'string', 'max:120'],
            'content.cards.*.description' => ['nullable', 'string', 'max:500'],
            'content.trust_cards' => ['nullable', 'array'],
            'content.trust_cards.*.icon' => ['nullable', 'string', 'max:80'],
            'content.trust_cards.*.title' => ['nullable', 'string', 'max:120'],
            'content.trust_cards.*.description' => ['nullable', 'string', 'max:500'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.accent_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

<?php

namespace App\Services\Landing\Components;

final class SeedOfferHeroV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-offer-hero-v1';
    }

    public function name(): string
    {
        return 'Seed Offer Hero';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-offer-hero-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'badge_text' => ['type' => 'text', 'label' => 'Badge Text'],
                'title' => ['type' => 'text', 'label' => 'Title', 'required' => true],
                'description' => ['type' => 'textarea', 'label' => 'Description'],
                'offer_label' => ['type' => 'text', 'label' => 'Offer Label'],
                'price' => ['type' => 'text', 'label' => 'Offer Price'],
                'old_price' => ['type' => 'text', 'label' => 'Old Price'],
                'timer_label' => ['type' => 'text', 'label' => 'Timer Label'],
                'image_url' => ['type' => 'url', 'label' => 'Hero Image URL'],
                'image_alt' => ['type' => 'textarea', 'label' => 'Hero Image Alt'],
                'trust_badge' => ['type' => 'text', 'label' => 'Trust Badge'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'accent_color' => ['type' => 'color', 'label' => 'Accent Color'],
                'text_color' => ['type' => 'color', 'label' => 'Text Color'],
            ]),
            'settings' => [
                'show_countdown' => ['type' => 'boolean', 'label' => 'Show Countdown'],
                'countdown.duration_hours' => ['type' => 'number', 'label' => 'Countdown Hours', 'min' => 1],
                'countdown.starts_at' => ['type' => 'datetime', 'label' => 'Starts At'],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'badge_text' => 'সীমিত সময়ের অফার!',
                'title' => 'বারি বেগুন-১২ এর প্রিমিয়াম বীজ এখন আরও সুলভে',
                'description' => 'প্রতিটি বেগুন ১ কেজি পর্যন্ত ওজনের হতে পারে। উচ্চ ফলনশীল ও লবনাক্ততা সহিষ্ণু উন্নত জাতের বীজ সরাসরি আপনার দুয়ারে।',
                'offer_label' => 'অফার মূল্য',
                'price' => '৳৩০০',
                'old_price' => '৳৪০০',
                'timer_label' => 'অফারটি শেষ হবে',
                'image_url' => '',
                'image_alt' => 'Bari-12 eggplant seeds product image',
                'trust_badge' => '১০০% গ্যারান্টি',
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#0d631b',
                'accent_color' => '#ffb300',
                'text_color' => '#ffffff',
            ]),
            'settings' => [
                'show_countdown' => true,
                'countdown' => [
                    'duration_hours' => 4,
                    'starts_at' => null,
                ],
            ],
            'behaviours' => [],
            'data_source' => [],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.badge_text' => ['nullable', 'string', 'max:100'],
            'content.title' => ['required', 'string', 'max:200'],
            'content.description' => ['nullable', 'string', 'max:1000'],
            'content.offer_label' => ['nullable', 'string', 'max:80'],
            'content.price' => ['nullable', 'string', 'max:40'],
            'content.old_price' => ['nullable', 'string', 'max:40'],
            'content.timer_label' => ['nullable', 'string', 'max:80'],
            'content.image_url' => ['nullable', 'string', 'max:2048'],
            'content.image_alt' => ['nullable', 'string', 'max:500'],
            'content.trust_badge' => ['nullable', 'string', 'max:100'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.accent_color' => ['required'],
            'style.text_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['required', 'array'],
            'settings.show_countdown' => ['required', 'boolean'],
            'settings.countdown' => ['required', 'array'],
            'settings.countdown.duration_hours' => ['required', 'integer', 'min:1', 'max:8760'],
            'settings.countdown.starts_at' => ['nullable', 'date'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }

    public function behaviours(): array
    {
        return ['recurring-countdown'];
    }
}

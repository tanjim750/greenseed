<?php

namespace App\Services\Landing\Components;

final class SeedMobileOfferCountdownV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-mobile-offer-countdown-v1';
    }

    public function name(): string
    {
        return 'Seed Mobile Offer Countdown';
    }

    public function category(): string
    {
        return 'seed mobile';
    }

    public function view(): string
    {
        return 'landing.components.seed-mobile-offer-countdown-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'subheading' => ['type' => 'text', 'label' => 'Subheading'],
                'regular_price' => ['type' => 'text', 'label' => 'Regular Price'],
                'offer_price' => ['type' => 'text', 'label' => 'Offer Price'],
            ],
            'settings' => [
                'hours' => ['type' => 'number', 'label' => 'Hours', 'min' => 0],
                'minutes' => ['type' => 'number', 'label' => 'Minutes', 'min' => 0],
                'seconds' => ['type' => 'number', 'label' => 'Seconds', 'min' => 0],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
                'accent_color' => ['type' => 'color', 'label' => 'Accent Color'],
                'card_background' => ['type' => 'color', 'label' => 'Card Background'],
            ]),
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'heading' => 'অফারটি সীমিত সময়ের জন্য',
                'subheading' => '- আজ অর্ডার করলেই পাচ্ছেন স্পেশাল ডিসকাউন্ট -',
                'regular_price' => 'রেগুলার মূল্য ৪০০৳',
                'offer_price' => 'আজকের অফার ৩০০৳',
            ],
            'settings' => [
                'hours' => 3,
                'minutes' => 45,
                'seconds' => 16,
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'primary_color' => '#0d631b',
                'accent_color' => '#ffb300',
                'card_background' => '#ffffff',
            ]),
            'behaviours' => [],
            'data_source' => [],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.*' => ['nullable', 'string', 'max:180'],
            'settings' => ['required', 'array'],
            'settings.hours' => ['required', 'integer', 'min:0', 'max:999'],
            'settings.minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'settings.seconds' => ['required', 'integer', 'min:0', 'max:59'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

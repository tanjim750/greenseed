<?php

namespace App\Services\Landing\Components;

final class SeedMobileTrustHeroV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-mobile-trust-hero-v1';
    }

    public function name(): string
    {
        return 'Seed Mobile Trust + Hero';
    }

    public function category(): string
    {
        return 'seed mobile';
    }

    public function view(): string
    {
        return 'landing.components.seed-mobile-trust-hero-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'trust_message' => ['type' => 'text', 'label' => 'Trust Message'],
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'subheading' => ['type' => 'textarea', 'label' => 'Subheading'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
                'trust_background' => ['type' => 'color', 'label' => 'Trust Background'],
            ]),
            'settings' => [],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'trust_message' => 'বীজ না গজালে টাকা ফেরত পাবেন ইনশাআল্লাহ',
                'heading' => 'অর্ডার কনফার্ম করুন',
                'subheading' => 'সঠিক তথ্য দিয়ে ফরমটি পূরণ করুন। আমরা আপনার সাথে যোগাযোগ করব।',
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#faf9f5',
                'primary_color' => '#0d631b',
                'trust_background' => '#d1e7dd',
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
            'content.trust_message' => ['nullable', 'string', 'max:180'],
            'content.heading' => ['nullable', 'string', 'max:160'],
            'content.subheading' => ['nullable', 'string', 'max:500'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

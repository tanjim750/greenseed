<?php

namespace App\Services\Landing\Components;

final class SeedMobileGalleryV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-mobile-gallery-v1';
    }

    public function name(): string
    {
        return 'Seed Mobile Image Gallery';
    }

    public function category(): string
    {
        return 'seed mobile';
    }

    public function view(): string
    {
        return 'landing.components.seed-mobile-gallery-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'images' => ['type' => 'repeater', 'label' => 'Images'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'card_background' => ['type' => 'color', 'label' => 'Card Background'],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
            ]),
            'settings' => [],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'heading' => 'বারি বেগুন-১২ এর কিছু ছবি',
                'images' => [
                    ['url' => '', 'alt' => 'Bari-12 Eggplant 1'],
                    ['url' => '', 'alt' => 'Bari-12 Eggplant 2'],
                ],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'card_background' => '#ffffff',
                'primary_color' => '#0d631b',
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
            'content.images' => ['nullable', 'array', 'max:12'],
            'content.images.*.url' => ['nullable', 'string', 'max:2048'],
            'content.images.*.alt' => ['nullable', 'string', 'max:500'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

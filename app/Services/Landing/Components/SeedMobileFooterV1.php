<?php

namespace App\Services\Landing\Components;

final class SeedMobileFooterV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-mobile-footer-v1';
    }

    public function name(): string
    {
        return 'Seed Mobile Footer';
    }

    public function category(): string
    {
        return 'seed mobile';
    }

    public function view(): string
    {
        return 'landing.components.seed-mobile-footer-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'brand' => ['type' => 'text', 'label' => 'Brand'],
                'copyright' => ['type' => 'text', 'label' => 'Copyright'],
                'links' => ['type' => 'repeater', 'label' => 'Links'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color'],
            ]),
            'settings' => [],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'brand' => 'Green Seed BD',
                'copyright' => '© 2024 Green Seed BD. All rights reserved.',
                'links' => [
                    ['label' => 'Privacy Policy', 'url' => '#'],
                    ['label' => 'Terms of Service', 'url' => '#'],
                    ['label' => 'Shipping Info', 'url' => '#'],
                ],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#f4f4f0',
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
            'content.brand' => ['nullable', 'string', 'max:100'],
            'content.copyright' => ['nullable', 'string', 'max:180'],
            'content.links' => ['nullable', 'array', 'max:8'],
            'content.links.*.label' => ['nullable', 'string', 'max:80'],
            'content.links.*.url' => ['nullable', 'string', 'max:2048'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

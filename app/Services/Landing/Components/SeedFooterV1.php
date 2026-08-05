<?php

namespace App\Services\Landing\Components;

final class SeedFooterV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-footer-v1';
    }

    public function name(): string
    {
        return 'Seed Footer';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-footer-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'brand' => ['type' => 'text', 'label' => 'Brand'],
                'description' => ['type' => 'textarea', 'label' => 'Description'],
                'links' => ['type' => 'repeater', 'label' => 'Footer Links'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
            ]),
            'settings' => [],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'brand' => 'Green Seed BD',
                'description' => '© 2024 Green Seed BD. Growth, precision, and earth-bound reliability.',
                'links' => [
                    ['label' => 'Privacy Policy', 'url' => '#'],
                    ['label' => 'Terms of Service', 'url' => '#'],
                    ['label' => 'Shipping Info', 'url' => '#'],
                    ['label' => 'Contact Us', 'url' => '#'],
                ],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#e2e3df',
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
            'content.description' => ['nullable', 'string', 'max:500'],
            'content.links' => ['nullable', 'array', 'max:8'],
            'content.links.*.label' => ['nullable', 'string', 'max:80'],
            'content.links.*.url' => ['nullable', 'string', 'max:2048'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

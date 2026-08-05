<?php

namespace App\Services\Landing\Components;

final class ProductGridV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'product-grid-v1';
    }

    public function name(): string
    {
        return 'Product Grid';
    }

    public function category(): string
    {
        return 'products';
    }

    public function view(): string
    {
        return 'landing.components.product-grid-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'section_title' => [
                    'type' => 'text',
                    'label' => 'Section Title',
                ],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => [
                    'type' => 'color',
                    'label' => 'Background Color',
                ],
            ]),
            'settings' => [
                'columns' => [
                    'type' => 'number',
                    'label' => 'Columns',
                    'min' => 1,
                    'max' => 6,
                ],
                'show_price' => [
                    'type' => 'boolean',
                    'label' => 'Show Price',
                ],
                'show_stock' => [
                    'type' => 'boolean',
                    'label' => 'Show Stock',
                ],
            ],
            'data_source' => [
                'type' => [
                    'type' => 'select',
                    'label' => 'Product Source',
                    'options' => ['manual', 'category'],
                ],
                'product_ids' => [
                    'type' => 'product_selector',
                    'label' => 'Products',
                ],
                'category_ids' => [
                    'type' => 'category_selector',
                    'label' => 'Categories',
                ],
                'limit' => [
                    'type' => 'number',
                    'label' => 'Limit',
                    'min' => 1,
                    'max' => 24,
                ],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'section_title' => 'Featured Products',
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#ffffff',
            ]),
            'settings' => [
                'columns' => 4,
                'show_price' => true,
                'show_stock' => false,
            ],
            'behaviours' => [],
            'data_source' => [
                'type' => 'manual',
                'product_ids' => [],
                'category_ids' => [],
                'limit' => 8,
            ],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.section_title' => ['nullable', 'string', 'max:160'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['required', 'array'],
            'settings.columns' => ['required', 'integer', 'min:1', 'max:6'],
            'settings.show_price' => ['required', 'boolean'],
            'settings.show_stock' => ['required', 'boolean'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['required', 'array'],
            'data_source.type' => ['required', 'in:manual,category'],
            'data_source.product_ids' => ['nullable', 'array'],
            'data_source.product_ids.*' => ['integer', 'min:1'],
            'data_source.category_ids' => ['nullable', 'array'],
            'data_source.category_ids.*' => ['integer', 'min:1'],
            'data_source.limit' => ['required', 'integer', 'min:1', 'max:24'],
        ];
    }

    public function dataResolver(): ?string
    {
        return 'product-grid';
    }

    public function behaviours(): array
    {
        return [
            'order-submission',
        ];
    }
}

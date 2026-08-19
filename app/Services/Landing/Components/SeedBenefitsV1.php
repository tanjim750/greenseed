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
                'cards' => ['type' => 'repeater', 'label' => 'Benefit Cards', 'fields' => ['title', 'description']],
                'trust_cards' => ['type' => 'repeater', 'label' => 'Trust Cards', 'fields' => ['title', 'description']],
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
                'heading' => '',
                'feature_title' => '',
                'feature_description' => '',
                'feature_points' => [],
                'cards' => [],
                'trust_cards' => [],
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
            'content.cards.*.title' => ['nullable', 'string', 'max:120'],
            'content.cards.*.description' => ['nullable', 'string', 'max:500'],
            'content.trust_cards' => ['nullable', 'array'],
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

<?php

namespace App\Services\Landing\Components;

final class SeedSupportV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-support-v1';
    }

    public function name(): string
    {
        return 'Seed Support CTA';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-support-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'phone' => ['type' => 'text', 'label' => 'Phone'],
                'badges' => ['type' => 'repeater', 'label' => 'Badges', 'fields' => ['text']],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'button_color' => ['type' => 'color', 'label' => 'Button Color'],
            ]),
            'settings' => [],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'heading' => '',
                'phone' => '',
                'badges' => [],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'button_color' => '#006e1c',
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
            'content.phone' => ['nullable', 'string', 'max:30'],
            'content.badges' => ['nullable', 'array', 'max:6'],
            'content.badges.*.text' => ['nullable', 'string', 'max:120'],
            'style' => ['required', 'array'],
            'style.button_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

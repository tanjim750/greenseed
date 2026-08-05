<?php

namespace App\Services\Landing\Components;

abstract class SeedMobileChecklistV1 extends BaseLandingComponent
{
    abstract protected function componentKey(): string;

    abstract protected function componentName(): string;

    abstract protected function defaultHeading(): string;

    abstract protected function defaultItems(): array;

    public function key(): string
    {
        return $this->componentKey();
    }

    public function name(): string
    {
        return $this->componentName();
    }

    public function category(): string
    {
        return 'seed mobile';
    }

    public function view(): string
    {
        return 'landing.components.seed-mobile-checklist-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'items' => ['type' => 'repeater', 'label' => 'Checklist Items'],
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
                'heading' => $this->defaultHeading(),
                'items' => $this->defaultItems(),
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
            'content.heading' => ['nullable', 'string', 'max:180'],
            'content.items' => ['nullable', 'array', 'max:12'],
            'content.items.*.icon' => ['nullable', 'string', 'max:80'],
            'content.items.*.text' => ['nullable', 'string', 'max:300'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

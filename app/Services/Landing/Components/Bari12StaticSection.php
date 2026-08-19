<?php

namespace App\Services\Landing\Components;

final class Bari12StaticSection extends BaseLandingComponent
{
    public function __construct(
        private string $componentKey,
        private string $componentName,
        private string $componentView,
        private array $componentSchema,
        private array $componentDefaults,
        private string $componentCategory = 'bari-12 stitch'
    ) {
    }

    public function key(): string
    {
        return $this->componentKey;
    }

    public function name(): string
    {
        return $this->componentName;
    }

    public function category(): string
    {
        return $this->componentCategory;
    }

    public function view(): string
    {
        return $this->componentView;
    }

    public function schema(): array
    {
        return array_merge($this->componentSchema, [
            'style' => array_merge(
                $this->commonStyleSchema(),
                $this->componentSchema['style'] ?? []
            ),
        ]);
    }

    public function defaults(): array
    {
        return $this->componentDefaults;
    }

    public function behaviours(): array
    {
        return $this->componentDefaults['behaviours'] ?? [];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'style' => ['required', 'array'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['nullable', 'array'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}

<?php

namespace App\Services\Landing\Components;

use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\Contracts\LandingComponentDefinition;
use App\Services\Landing\LandingTheme;
use Closure;

abstract class BaseLandingComponent implements LandingComponentDefinition
{
    protected function commonStyleSchema(): array
    {
        return [
            'padding' => [
                'type' => 'spacing_quad',
                'label' => 'Padding',
                'labels' => ['Top', 'Right', 'Bottom', 'Left'],
            ],
            'margin' => [
                'type' => 'spacing_quad',
                'label' => 'Margin',
                'labels' => ['Top', 'Right', 'Bottom', 'Left'],
            ],
            'section_max_width' => [
                'type' => 'text',
                'label' => 'Section Max Width',
            ],
            'content_max_width' => [
                'type' => 'text',
                'label' => 'Content Max Width',
            ],
            'text_align' => [
                'type' => 'select',
                'label' => 'Text Align',
                'options' => ['inherit', 'left', 'center', 'right'],
            ],
            'border_radius' => [
                'type' => 'text',
                'label' => 'Border Radius',
            ],
            'box_shadow' => [
                'type' => 'text',
                'label' => 'Box Shadow',
            ],
        ];
    }

    protected function commonStyleDefaults(): array
    {
        return [];
    }

    public function dataResolver(): ?string
    {
        return null;
    }

    public function resolveData(
        DynamicLandingPageComponent $component,
        array $config
    ): array {
        return [];
    }

    public function behaviours(): array
    {
        return [];
    }

    protected function styleValueRule(): Closure
    {
        return function ($attribute, $value, $fail) {
            if ($value === null) {
                return;
            }

            if ($this->isSpacingQuad($value)) {
                return;
            }

            if (is_string($value) && preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $value)) {
                return;
            }

            if (is_string($value) && app(LandingTheme::class)->isSafeCssValue($value)) {
                return;
            }

            if (!is_array($value)) {
                $fail("The {$attribute} value must be a hex color or a valid theme style reference.");

                return;
            }

            $mode = $value['mode'] ?? null;
            $styleValue = $value['value'] ?? null;

            if ($mode === 'token') {
                if (is_string($styleValue) && array_key_exists($styleValue, LandingTheme::DEFAULT_TOKENS)) {
                    return;
                }

                $fail("The {$attribute} token must be a supported theme token.");

                return;
            }

            if ($mode === 'custom') {
                if (app(LandingTheme::class)->isSafeCssValue($styleValue)) {
                    return;
                }

                $fail("The {$attribute} custom value must be a safe CSS value.");

                return;
            }

            $fail("The {$attribute} value must be a hex color or a valid theme style reference.");
        };
    }

    private function isSpacingQuad(mixed $value): bool
    {
        if (!is_array($value) || count($value) !== 4 || array_keys($value) !== [0, 1, 2, 3]) {
            return false;
        }

        return collect($value)
            ->every(fn ($item) => is_string($item) && app(LandingTheme::class)->isSafeCssValue($item));
    }
}

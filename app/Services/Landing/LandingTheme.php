<?php

namespace App\Services\Landing;

final class LandingTheme
{
    public const DEFAULT_TOKENS = [
        'primary' => '#2563eb',
        'secondary' => '#0ea5e9',
        'background' => '#ffffff',
        'surface' => '#f8fafc',
        'text' => '#111827',
        'muted_text' => '#64748b',
    ];

    public const DEFAULT_LAYOUT = [
        'margin' => ['0', '0', '0', '0'],
        'padding' => ['0', '0', '0', '0'],
    ];

    public function normalize(array $theme = []): array
    {
        $tokens = array_replace(
            self::DEFAULT_TOKENS,
            collect($theme)
                ->only(array_keys(self::DEFAULT_TOKENS))
                ->filter(fn ($value) => $this->isSafeCssValue($value))
                ->map(fn ($value) => trim((string) $value))
                ->all()
        );

        $tokens['layout'] = $this->normalizeLayout($theme['layout'] ?? []);

        return $tokens;
    }

    public function normalizeLayout(array $layout = []): array
    {
        return [
            'margin' => $this->normalizeSpacingQuad($layout['margin'] ?? null),
            'padding' => $this->normalizeSpacingQuad($layout['padding'] ?? null),
        ];
    }

    private function normalizeSpacingQuad(mixed $value): array
    {
        if (!is_array($value) || count($value) !== 4 || array_keys($value) !== [0, 1, 2, 3]) {
            return ['0', '0', '0', '0'];
        }

        return collect($value)
            ->map(fn ($item) => is_string($item) && $this->isSafeCssValue($item) ? trim($item) : '0')
            ->values()
            ->all();
    }

    public function isSafeCssValue(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);

        if ($value === '') {
            return false;
        }

        if (preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $value)) {
            return true;
        }

        $lowerValue = strtolower($value);

        if (
            str_contains($lowerValue, 'javascript')
            || str_contains($lowerValue, 'expression')
            || str_contains($lowerValue, 'url(')
            || str_contains($lowerValue, '@import')
        ) {
            return false;
        }

        if (preg_match('/^[A-Za-z0-9\s,().%-]+$/', $value)) {
            return true;
        }

        return false;
    }
}

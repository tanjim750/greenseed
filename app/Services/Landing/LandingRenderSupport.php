<?php

namespace App\Services\Landing;

final class LandingRenderSupport
{
    private const LAYOUT_STYLE_MAP = [
        'padding' => '--landing-component-padding',
        'margin' => '--landing-component-margin',
        'section_max_width' => '--landing-component-max-width',
        'content_max_width' => '--landing-content-max-width',
        'text_align' => '--landing-component-text-align',
        'border_radius' => '--landing-component-border-radius',
        'box_shadow' => '--landing-component-box-shadow',
    ];

    public function scopeClass(?string $scope): string
    {
        $rawScope = (string) $scope;
        $scope = preg_replace('/[^A-Za-z0-9_-]/', '', $rawScope);

        if ($scope === '' || $scope !== $rawScope || !str_starts_with($scope, 'cmp_')) {
            return 'cmp_invalid';
        }

        return $scope;
    }

    public function href(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '#';
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!$scheme) {
            return $url;
        }

        return in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true)
            ? $url
            : '#';
    }

    public function layoutStyleVariables(array $resolvedStyle): string
    {
        $variables = [];

        foreach (self::LAYOUT_STYLE_MAP as $styleKey => $cssVariable) {
            $value = $resolvedStyle[$styleKey] ?? null;

            if (is_array($value)) {
                $value = implode(' ', array_map(
                    fn ($item) => $this->normalizeLayoutCssValue($item, $styleKey),
                    array_filter($value, fn ($item) => is_string($item) && trim($item) !== '')
                ));
            } elseif (in_array($styleKey, ['section_max_width', 'content_max_width', 'border_radius'], true)) {
                $value = $this->normalizeLayoutCssValue($value, $styleKey);
            }

            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $variables[] = $cssVariable . ': ' . trim($value) . ';';
        }

        return implode(' ', $variables);
    }

    private function normalizeLayoutCssValue(mixed $value, string $styleKey): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if ($styleKey === 'box_shadow') {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value === 0.0 ? '0' : $value . 'px';
        }

        return $value;
    }
}

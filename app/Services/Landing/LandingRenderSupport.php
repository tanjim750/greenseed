<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\Contracts\LandingComponentDefinition;

final class LandingRenderSupport
{
    private const CHECKOUT_COMPONENT_KEYS = [
        'seed-checkout-v1',
        'seed-checkout-v2',
        'seed-mobile-checkout-sticky-v1',
        'bari12-checkout-form-v1',
        'sheikh-checkout-form-v1',
    ];

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

    public function checkoutAnchorId(DynamicLandingPageComponent $component): string
    {
        $checkoutComponents = $this->checkoutSiblings($component);
        $position = $checkoutComponents
            ->search(fn (DynamicLandingPageComponent $checkout) => $this->sameComponent($checkout, $component));

        if ($position === false) {
            return $this->fallbackCheckoutAnchorId($component);
        }

        $position = (int) $position + 1;

        return $position === 1
            ? 'greenseed-checkout-form'
            : 'greenseed-checkout-form-' . $position;
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

    public function checkoutPackagePrice(
        array $package,
        LandingComponentDefinition $definition,
        float $unitPrice,
        int $quantity
    ): array {
        $baseTotal = max(0, $unitPrice) * max(1, $quantity);
        $formattedBaseTotal = $this->formatMoney($baseTotal);
        $customPrice = $this->customCheckoutPackagePrice($package, $definition);

        return [
            'price' => $customPrice ?? $formattedBaseTotal,
            'original_price' => $customPrice ? $formattedBaseTotal : null,
            'has_custom_price' => (bool) $customPrice,
        ];
    }

    public function customCheckoutPackagePrice(array $package, LandingComponentDefinition $definition): ?string
    {
        if (!array_key_exists('price', $package) || trim((string) $package['price']) === '') {
            return null;
        }

        if (array_key_exists('__price_custom', $package)) {
            return $this->truthy($package['__price_custom'])
                ? (string) $package['price']
                : null;
        }

        $packagePrice = $this->parseMoneyValue($package['price']);

        if ($packagePrice === null) {
            return (string) $package['price'];
        }

        $defaultPackage = $this->defaultPackageForQuantity($definition, max(1, (int) ($package['quantity'] ?? 1)));
        $defaultPrice = is_array($defaultPackage ?? null)
            ? $this->parseMoneyValue($defaultPackage['price'] ?? null)
            : null;

        if ($defaultPrice !== null && abs($packagePrice - $defaultPrice) < 0.01) {
            return null;
        }

        return (string) $package['price'];
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value !== 0.0;
        }

        if (!is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    public function parseMoneyValue(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = trim(strtr($value, [
            '০' => '0',
            '১' => '1',
            '২' => '2',
            '৩' => '3',
            '৪' => '4',
            '৫' => '5',
            '৬' => '6',
            '৭' => '7',
            '৮' => '8',
            '৯' => '9',
        ]));
        $normalized = str_replace(',', '', $normalized);
        $normalized = preg_replace('/[^0-9.]/', '', $normalized);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        if (substr_count($normalized, '.') > 1 || !preg_match('/^\d+(?:\.\d+)?$/', $normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function checkoutSiblings(DynamicLandingPageComponent $component)
    {
        $page = $component->relationLoaded('page')
            ? $component->getRelation('page')
            : ($component->relationLoaded('dynamicLandingPage') ? $component->getRelation('dynamicLandingPage') : null);

        if ($page && $page->relationLoaded('components')) {
            return $page->getRelation('components')
                ->filter(fn (DynamicLandingPageComponent $sibling) => $this->isCheckoutComponent($sibling))
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();
        }

        if ((int) ($component->dynamic_landing_page_id ?? 0) > 0) {
            return DynamicLandingPageComponent::query()
                ->where('dynamic_landing_page_id', $component->dynamic_landing_page_id)
                ->where('is_enabled', true)
                ->whereIn('component_key', self::CHECKOUT_COMPONENT_KEYS)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        return collect([$component])->filter(fn (DynamicLandingPageComponent $sibling) => $this->isCheckoutComponent($sibling))->values();
    }

    private function isCheckoutComponent(DynamicLandingPageComponent $component): bool
    {
        return (bool) ($component->is_enabled ?? true)
            && in_array($component->component_key, self::CHECKOUT_COMPONENT_KEYS, true);
    }

    private function sameComponent(DynamicLandingPageComponent $left, DynamicLandingPageComponent $right): bool
    {
        $leftId = $left->getAttribute('source_component_id') ?? $left->id;
        $rightId = $right->getAttribute('source_component_id') ?? $right->id;

        if ($leftId && $rightId) {
            return (int) $leftId === (int) $rightId;
        }

        return (string) $left->instance_scope === (string) $right->instance_scope;
    }

    private function fallbackCheckoutAnchorId(DynamicLandingPageComponent $component): string
    {
        $suffix = $component->getAttribute('source_component_id') ?? $component->id;

        if ($suffix) {
            return 'greenseed-checkout-form-' . (int) $suffix;
        }

        $scope = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $component->instance_scope);

        return $scope
            ? 'greenseed-checkout-form-' . $scope
            : 'greenseed-checkout-form';
    }

    private function defaultPackageForQuantity(LandingComponentDefinition $definition, int $quantity): ?array
    {
        $packages = $definition->defaults()['content']['packages'] ?? [];

        if (!is_array($packages)) {
            return null;
        }

        return collect($packages)->first(function ($package) use ($quantity) {
            return is_array($package)
                && max(1, (int) ($package['quantity'] ?? 1)) === $quantity;
        });
    }

    private function formatMoney(float $amount): string
    {
        return function_exists('priceFormate')
            ? priceFormate($amount)
            : number_format($amount, 2);
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

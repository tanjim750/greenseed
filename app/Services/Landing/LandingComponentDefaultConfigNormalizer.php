<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingComponentDefinition;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class LandingComponentDefaultConfigNormalizer
{
    private const ROOT_KEYS = [
        'content',
        'style',
        'settings',
        'behaviours',
        'data_source',
    ];

    public function normalize(
        LandingComponentDefinition $definition,
        array $config
    ): array {
        $this->validateRootStructure($config);

        $config = $this->normalizeScalarValues($this->onlyKnownRootKeys($config));

        if ($definition instanceof \App\Services\Landing\Contracts\LandingComponentConfigNormalizer) {
            $config = $definition->normalize($config);
        }

        return $this->onlySchemaFields($definition, $config);
    }

    private function onlyKnownRootKeys(array $config): array
    {
        return Arr::only($config, self::ROOT_KEYS);
    }

    private function validateRootStructure(array $config): void
    {
        Validator::make(
            $config,
            collect(self::ROOT_KEYS)
                ->mapWithKeys(fn ($key) => [$key => ['sometimes', 'array']])
                ->all()
        )->validate();
    }

    private function onlySchemaFields(
        LandingComponentDefinition $definition,
        array $config
    ): array {
        $allowed = [];
        $schema = $definition->schema();

        foreach (['content', 'style', 'settings', 'data_source'] as $section) {
            foreach (array_keys($schema[$section] ?? []) as $field) {
                $path = $section . '.' . $field;

                if (Arr::has($config, $path)) {
                    Arr::set($allowed, $path, $this->normalizeByFieldType(
                        Arr::get($config, $path),
                        $schema[$section][$field]['type'] ?? null
                    ));
                }
            }
        }

        if (array_key_exists('behaviours', $config) && is_array($config['behaviours'])) {
            $allowed['behaviours'] = $config['behaviours'];
        }

        return $allowed;
    }

    private function normalizeScalarValues(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeScalarValues($item), $value);
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return $value;
    }

    private function normalizeByFieldType(mixed $value, ?string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'number' => is_numeric($value) ? (int) $value : $value,
            'boolean' => is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'color' => $this->normalizeStyleValue($value),
            'spacing_quad' => $this->normalizeSpacingQuad($value),
            'product_selector', 'category_selector' => $this->normalizeIdList($value),
            'url' => is_string($value) ? trim($value) : $value,
            default => $value,
        };
    }

    private function normalizeSpacingQuad(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $value = array_values($value);

        if (count($value) !== 4) {
            return $value;
        }

        return collect($value)
            ->map(fn ($item) => is_string($item) ? trim($item) : $item)
            ->all();
    }

    private function normalizeStyleValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return Str::lower($value);
        }

        if (!is_array($value)) {
            return $value;
        }

        $mode = $value['mode'] ?? null;

        if (!in_array($mode, ['token', 'custom'], true)) {
            return $value;
        }

        return [
            'mode' => $mode,
            'value' => is_string($value['value'] ?? null) ? trim($value['value']) : null,
        ];
    }

    private function normalizeIdList(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        return collect($value)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}

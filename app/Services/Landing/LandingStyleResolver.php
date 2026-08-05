<?php

namespace App\Services\Landing;

final class LandingStyleResolver
{
    public function __construct(
        private LandingTheme $theme
    ) {
    }

    public function resolve(array $styleConfig, array $pageTheme = []): array
    {
        $theme = $this->theme->normalize($pageTheme);
        $resolved = [];

        foreach ($styleConfig as $key => $value) {
            $resolved[$key] = $this->resolveArrayValue($value, $theme);
        }

        return $resolved;
    }

    private function resolveArrayValue(mixed $value, array $theme): mixed
    {
        if (!is_array($value) || array_keys($value) !== range(0, count($value) - 1)) {
            return $this->resolveValue($value, $theme);
        }

        $resolved = collect($value)
            ->map(fn ($item) => $this->resolveValue($item, $theme))
            ->all();

        return in_array(null, $resolved, true) ? null : $resolved;
    }

    public function resolveValue(mixed $value, array $theme): mixed
    {
        if (is_array($value)) {
            if (($value['mode'] ?? null) === 'token') {
                $token = $value['value'] ?? '';

                if (!is_string($token) || !array_key_exists($token, LandingTheme::DEFAULT_TOKENS)) {
                    return null;
                }

                $tokenValue = $theme[$token] ?? null;

                return $this->theme->isSafeCssValue($tokenValue)
                    ? trim((string) $tokenValue)
                    : null;
            }

            if (($value['mode'] ?? null) === 'custom') {
                $value = $value['value'] ?? null;
            } else {
                return null;
            }
        }

        if (!$this->theme->isSafeCssValue($value)) {
            return null;
        }

        return trim((string) $value);
    }
}

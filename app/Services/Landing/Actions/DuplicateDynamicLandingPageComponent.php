<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\LandingComponentConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DuplicateDynamicLandingPageComponent
{
    public function __construct(
        private LandingComponentConfigService $configService
    ) {
    }

    public function execute(DynamicLandingPageComponent $component): DynamicLandingPageComponent
    {
        $validatedConfig = $this->configService->validateForStorage(
            $component->component_key,
            $component->config ?? []
        );

        return DB::transaction(function () use ($component, $validatedConfig) {
            $page = $component->dynamicLandingPage;

            $page->newQuery()->whereKey($page->getKey())->lockForUpdate()->first();

            $nextOrder = ((int) $page->components()->max('sort_order')) + 1;

            return $page->components()->create([
                'component_key' => $component->component_key,
                'instance_scope' => $this->generateInstanceScope(),
                'sort_order' => $nextOrder,
                'config' => $validatedConfig,
                'is_enabled' => $component->is_enabled,
            ]);
        });
    }

    private function generateInstanceScope(): string
    {
        do {
            $scope = 'cmp_' . Str::lower(Str::random(12));
        } while (DynamicLandingPageComponent::where('instance_scope', $scope)->exists());

        return $scope;
    }
}

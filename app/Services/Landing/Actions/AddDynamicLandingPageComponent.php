<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\LandingComponentConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AddDynamicLandingPageComponent
{
    public function __construct(
        private LandingComponentConfigService $configService
    ) {
    }

    public function execute(
        DynamicLandingPage $page,
        string $componentKey,
        array $config = []
    ): DynamicLandingPageComponent {
        $validatedConfig = $this->configService->validateForStorage($componentKey, $config);

        return DB::transaction(function () use ($page, $componentKey, $validatedConfig) {
            DynamicLandingPage::whereKey($page->getKey())->lockForUpdate()->first();

            $nextOrder = ((int) $page->components()->max('sort_order')) + 1;

            return $page->components()->create([
                'component_key' => $componentKey,
                'instance_scope' => $this->generateInstanceScope(),
                'sort_order' => $nextOrder,
                'config' => $validatedConfig,
                'is_enabled' => true,
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

<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\LandingComponentConfigService;

final class UpdateDynamicLandingPageComponentConfig
{
    public function __construct(
        private LandingComponentConfigService $configService
    ) {
    }

    public function execute(
        DynamicLandingPageComponent $component,
        array $config
    ): DynamicLandingPageComponent {
        $component->update([
            'config' => $this->configService->validateForStorage(
                $component->component_key,
                $config
            ),
        ]);

        return $component->refresh();
    }
}

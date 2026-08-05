<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPageComponent;

final class SetDynamicLandingPageComponentVisibility
{
    public function execute(
        DynamicLandingPageComponent $component,
        bool $isEnabled
    ): DynamicLandingPageComponent {
        $component->update([
            'is_enabled' => $isEnabled,
        ]);

        return $component->refresh();
    }
}

<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderDynamicLandingPageComponents
{
    public function execute(
        DynamicLandingPage $page,
        array $componentIds
    ): void {
        $componentIds = array_map('intval', $componentIds);

        DB::transaction(function () use ($page, $componentIds) {
            DynamicLandingPage::whereKey($page->getKey())->lockForUpdate()->first();

            $currentIds = $page->components()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $this->validateComponentIds($currentIds, $componentIds);

            foreach ($componentIds as $index => $componentId) {
                $page->components()
                    ->whereKey($componentId)
                    ->update([
                        'sort_order' => $index + 1,
                    ]);
            }
        });
    }

    private function validateComponentIds(array $currentIds, array $componentIds): void
    {
        $uniqueIds = array_values(array_unique($componentIds));
        sort($uniqueIds);

        $expectedIds = array_values(array_unique($currentIds));
        sort($expectedIds);

        if (count($componentIds) !== count($uniqueIds) || $uniqueIds !== $expectedIds) {
            throw ValidationException::withMessages([
                'component_ids' => 'The component_ids list must contain every component for this page exactly once.',
            ]);
        }
    }
}

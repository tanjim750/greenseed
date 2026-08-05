<?php

namespace App\Services\Landing\Actions;

use App\Models\DynamicLandingPageComponent;
use Illuminate\Support\Facades\DB;

final class DeleteDynamicLandingPageComponent
{
    public function execute(DynamicLandingPageComponent $component): void
    {
        DB::transaction(function () use ($component) {
            $page = $component->dynamicLandingPage;

            $page->newQuery()->whereKey($page->getKey())->lockForUpdate()->first();

            $component->delete();

            $page->components()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->values()
                ->each(function (DynamicLandingPageComponent $component, int $index) {
                    $component->update([
                        'sort_order' => $index + 1,
                    ]);
                });
        });
    }
}

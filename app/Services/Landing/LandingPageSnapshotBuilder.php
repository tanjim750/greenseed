<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPage;

final class LandingPageSnapshotBuilder
{
    public function __construct(
        private LandingComponentConfigService $configService
    ) {
    }

    public function build(DynamicLandingPage $page): array
    {
        $page->load([
            'components' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        return [
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'theme' => $page->theme ?? [],
                'seo' => $page->seo ?? [],
            ],
            'components' => $page->components
                ->map(function ($component) {
                    return [
                        'id' => $component->id,
                        'source_component_id' => $component->id,
                        'component_key' => $component->component_key,
                        'instance_scope' => $component->instance_scope,
                        'sort_order' => (int) $component->sort_order,
                        'config' => $this->configService->validateForStorage(
                            $component->component_key,
                            $component->config ?? []
                        ),
                        'is_enabled' => (bool) $component->is_enabled,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}

<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Models\DynamicLandingPageVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class LandingPageRenderer
{
    public function __construct(
        private LandingComponentRenderer $componentRenderer,
        private LandingTheme $theme
    ) {
    }

    public function render(DynamicLandingPage $page): View
    {
        $page->load([
            'components' => fn ($query) => $query
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        return $this->renderLoadedPage($page);
    }

    public function renderPublished(DynamicLandingPage $page): View
    {
        $version = $page->publishedVersion()->firstOrFail();

        return $this->renderVersion($version);
    }

    public function renderVersion(DynamicLandingPageVersion $version): View
    {
        return $this->renderLoadedPage(
            $this->pageFromSnapshot($version->snapshot ?? [], $version->page, $version->id)
        );
    }

    private function renderLoadedPage(DynamicLandingPage $page): View
    {
        return view('landing.show', [
            'page' => $page,
            'componentRenderer' => $this->componentRenderer,
            'themeTokens' => $this->theme->normalize($page->theme ?? []),
        ]);
    }

    public function renderHtml(DynamicLandingPage $page): string
    {
        return $this->render($page)->render();
    }

    public function renderPublishedHtml(DynamicLandingPage $page): string
    {
        return $this->renderPublished($page)->render();
    }

    public function renderPublishedSnapshotHtml(array $snapshot, ?int $versionId = null): string
    {
        return $this->renderLoadedPage(
            $this->pageFromSnapshot($snapshot, null, $versionId)
        )->render();
    }

    private function pageFromSnapshot(
        array $snapshot,
        ?DynamicLandingPage $fallbackPage = null,
        ?int $versionId = null
    ): DynamicLandingPage {
        $pageSnapshot = $snapshot['page'] ?? [];
        $page = new DynamicLandingPage();
        $page->setRawAttributes([
            'id' => $pageSnapshot['id'] ?? $fallbackPage?->id,
            'name' => $pageSnapshot['name'] ?? $fallbackPage?->name,
            'slug' => $pageSnapshot['slug'] ?? $fallbackPage?->slug,
            'status' => 'published',
            'published_at' => $fallbackPage?->published_at,
        ], true);
        $page->theme = $pageSnapshot['theme'] ?? [];
        $page->seo = $pageSnapshot['seo'] ?? [];
        $page->exists = true;

        $components = collect($snapshot['components'] ?? [])
            ->filter(fn ($component) => (bool) ($component['is_enabled'] ?? true))
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->map(function (array $componentSnapshot) use ($page, $versionId) {
                $component = new DynamicLandingPageComponent();
                $component->setRawAttributes([
                    'id' => $componentSnapshot['id'] ?? null,
                    'source_component_id' => $componentSnapshot['source_component_id'] ?? $componentSnapshot['id'] ?? null,
                    'published_version_id' => $versionId,
                    'dynamic_landing_page_id' => $page->id,
                    'component_key' => $componentSnapshot['component_key'] ?? '',
                    'instance_scope' => $componentSnapshot['instance_scope'] ?? 'cmp_invalid',
                    'sort_order' => (int) ($componentSnapshot['sort_order'] ?? 0),
                    'is_enabled' => true,
                ], true);
                $component->config = $componentSnapshot['config'] ?? [];
                $component->exists = false;
                $component->setRelation('page', $page);
                $component->setRelation('dynamicLandingPage', $page);

                return $component;
            })
            ->values();

        $page->setRelation('components', new EloquentCollection($components->all()));

        return $page;
    }
}

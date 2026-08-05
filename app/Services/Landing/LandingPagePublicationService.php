<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Models\DynamicLandingPageVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class LandingPagePublicationService
{
    public function __construct(
        private LandingPageSnapshotBuilder $snapshotBuilder,
        private LandingPublishedPageCache $publishedPageCache
    ) {
    }

    public function publish(DynamicLandingPage $page, ?int $userId = null): DynamicLandingPageVersion
    {
        $version = DB::transaction(function () use ($page, $userId) {
            $lockedPage = DynamicLandingPage::query()
                ->whereKey($page->id)
                ->lockForUpdate()
                ->firstOrFail();

            $snapshot = $this->snapshotBuilder->build($lockedPage);
            $nextVersion = ((int) $lockedPage->versions()->max('version_number')) + 1;

            $lockedPage->versions()
                ->where('status', 'published')
                ->update(['status' => 'archived']);

            $version = $lockedPage->versions()->create([
                'version_number' => $nextVersion,
                'snapshot' => $snapshot,
                'status' => 'published',
                'created_by' => $userId,
                'published_at' => now(),
            ]);

            $lockedPage->update([
                'status' => 'published',
                'published_at' => now(),
                'updated_by' => $userId,
            ]);

            return $version;
        });

        $this->publishedPageCache->forgetSlug($page->fresh()?->slug);
        $this->publishedPageCache->forgetSlug($version->snapshot['page']['slug'] ?? null);

        return $version;
    }

    public function restoreToDraft(DynamicLandingPageVersion $version, ?int $userId = null): DynamicLandingPage
    {
        $page = DB::transaction(function () use ($version, $userId) {
            $version->load('page');

            $page = DynamicLandingPage::query()
                ->whereKey($version->dynamic_landing_page_id)
                ->lockForUpdate()
                ->firstOrFail();
            $snapshot = $version->snapshot ?? [];
            $pageSnapshot = $snapshot['page'] ?? [];

            $page->update([
                'name' => $pageSnapshot['name'] ?? $page->name,
                'theme' => $pageSnapshot['theme'] ?? [],
                'seo' => $pageSnapshot['seo'] ?? [],
                'status' => 'draft',
                'updated_by' => $userId,
            ]);

            $page->components()->delete();

            foreach (($snapshot['components'] ?? []) as $componentSnapshot) {
                $page->components()->create([
                    'component_key' => $componentSnapshot['component_key'],
                    'instance_scope' => $this->uniqueScope($componentSnapshot['instance_scope'] ?? null),
                    'sort_order' => (int) ($componentSnapshot['sort_order'] ?? 0),
                    'config' => $componentSnapshot['config'] ?? [],
                    'is_enabled' => (bool) ($componentSnapshot['is_enabled'] ?? true),
                ]);
            }

            return $page->fresh(['components']);
        });

        $this->publishedPageCache->forgetPage($page);
        $this->publishedPageCache->forgetSlug($version->snapshot['page']['slug'] ?? null);

        return $page;
    }

    private function uniqueScope(?string $scope): string
    {
        if (
            is_string($scope)
            && preg_match('/^cmp_[a-z0-9]+$/', $scope)
            && !DynamicLandingPageComponent::where('instance_scope', $scope)->exists()
        ) {
            return $scope;
        }

        do {
            $scope = 'cmp_' . Str::lower(Str::random(12));
        } while (DynamicLandingPageComponent::where('instance_scope', $scope)->exists());

        return $scope;
    }
}

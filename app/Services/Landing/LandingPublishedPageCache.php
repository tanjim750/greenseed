<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPage;
use Illuminate\Support\Facades\Cache;

final class LandingPublishedPageCache
{
    private const TTL_HOURS = 6;

    public function rememberBySlug(string $slug): array
    {
        return Cache::remember(
            $this->key($slug),
            now()->addHours(self::TTL_HOURS),
            fn () => $this->loadPublishedPayload($slug)
        );
    }

    public function forgetSlug(?string $slug): void
    {
        if (!$slug) {
            return;
        }

        Cache::forget($this->key($slug));
    }

    public function forgetPage(DynamicLandingPage $page): void
    {
        $this->forgetSlug($page->slug);
    }

    public function key(string $slug): string
    {
        return 'dynamic-landing-page:' . sha1($slug) . ':published';
    }

    private function loadPublishedPayload(string $slug): array
    {
        $page = DynamicLandingPage::query()
            ->where('slug', $slug)
            ->whereHas('publishedVersion')
            ->firstOrFail();

        $version = $page->publishedVersion()->firstOrFail();

        return [
            'page_id' => $page->id,
            'slug' => $page->slug,
            'version_id' => $version->id,
            'version_number' => $version->version_number,
            'published_at' => $version->published_at?->toISOString(),
            'snapshot' => $version->snapshot ?? [],
        ];
    }
}

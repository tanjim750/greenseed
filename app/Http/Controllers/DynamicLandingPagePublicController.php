<?php

namespace App\Http\Controllers;

use App\Models\DynamicLandingPage;
use App\Services\Landing\LandingPublishedPageCache;
use App\Services\Landing\LandingPageRenderer;
use Illuminate\Http\Response;

class DynamicLandingPagePublicController extends Controller
{
    public function __construct(
        private LandingPageRenderer $renderer,
        private LandingPublishedPageCache $publishedPageCache
    ) {
    }

    public function show(string $slug): Response
    {
        $payload = $this->publishedPageCache->rememberBySlug($slug);
        $page = DynamicLandingPage::query()
            ->select(['id', 'slug', 'status'])
            ->whereKey($payload['page_id'] ?? 0)
            ->where('slug', $slug)
            ->whereHas('publishedVersion')
            ->first();

        if (!$page) {
            $this->publishedPageCache->forgetSlug($slug);
            abort(404);
        }

        return response($this->renderer->renderPublishedSnapshotHtml(
            $payload['snapshot'] ?? [],
            $payload['version_id'] ?? null
        ));
    }
}

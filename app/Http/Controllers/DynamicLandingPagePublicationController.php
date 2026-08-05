<?php

namespace App\Http\Controllers;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Models\DynamicLandingPageVersion;
use App\Services\Landing\LandingComponentRenderer;
use App\Services\Landing\LandingPagePublicationService;
use App\Services\Landing\LandingPageRenderer;
use App\Services\Landing\LandingTheme;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DynamicLandingPagePublicationController extends Controller
{
    public function __construct(
        private LandingPageRenderer $renderer,
        private LandingPagePublicationService $publicationService
    ) {
    }

    public function preview(DynamicLandingPage $page): Response
    {
        return response($this->renderer->renderHtml($page));
    }

    public function componentPreview(
        DynamicLandingPage $page,
        DynamicLandingPageComponent $component,
        LandingComponentRenderer $componentRenderer,
        LandingTheme $theme
    ): Response {
        abort_unless((int) $component->dynamic_landing_page_id === (int) $page->id, 404);

        $page->setRelation('components', new EloquentCollection([$component]));

        return response(view('landing.show', [
            'page' => $page,
            'componentRenderer' => $componentRenderer,
            'themeTokens' => $theme->normalize($page->theme ?? []),
        ])->render());
    }

    public function publish(Request $request, DynamicLandingPage $page): JsonResponse
    {
        $version = $this->publicationService->publish($page, $request->user()?->id);

        return response()->json([
            'success' => true,
            'version_id' => $version->id,
            'version_number' => $version->version_number,
            'published_at' => $version->published_at?->toISOString(),
        ], 201);
    }

    public function restore(Request $request, DynamicLandingPageVersion $version): JsonResponse
    {
        $page = $this->publicationService->restoreToDraft($version, $request->user()?->id);

        return response()->json([
            'success' => true,
            'page_id' => $page->id,
            'status' => $page->status,
        ]);
    }
}

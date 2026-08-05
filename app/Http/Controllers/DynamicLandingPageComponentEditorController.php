<?php

namespace App\Http\Controllers;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\Actions\AddDynamicLandingPageComponent;
use App\Services\Landing\Actions\DeleteDynamicLandingPageComponent;
use App\Services\Landing\Actions\DuplicateDynamicLandingPageComponent;
use App\Services\Landing\Actions\ReorderDynamicLandingPageComponents;
use App\Services\Landing\Actions\SetDynamicLandingPageComponentVisibility;
use App\Services\Landing\Actions\UpdateDynamicLandingPageComponentConfig;
use App\Services\Landing\LandingPublishedPageCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynamicLandingPageComponentEditorController extends Controller
{
    public function __construct(
        private LandingPublishedPageCache $publishedPageCache
    ) {
    }

    public function store(
        Request $request,
        DynamicLandingPage $page,
        AddDynamicLandingPageComponent $action
    ): JsonResponse {
        $data = $request->validate([
            'component_key' => ['required', 'string'],
            'config' => ['nullable', 'array'],
        ]);

        $component = $action->execute($page, $data['component_key'], $data['config'] ?? []);
        $this->markDraftAndForgetCache($page);

        return response()->json([
            'data' => $this->serializeComponent($component),
        ], 201);
    }

    public function update(
        Request $request,
        DynamicLandingPage $page,
        DynamicLandingPageComponent $component,
        UpdateDynamicLandingPageComponentConfig $action
    ): JsonResponse {
        $this->abortUnlessComponentBelongsToPage($page, $component);

        $data = $request->validate([
            'config' => ['required', 'array'],
        ]);

        $component = $action->execute($component, $data['config']);
        $this->markDraftAndForgetCache($page);

        return response()->json([
            'data' => $this->serializeComponent($component),
        ]);
    }

    public function destroy(
        DynamicLandingPage $page,
        DynamicLandingPageComponent $component,
        DeleteDynamicLandingPageComponent $action
    ): JsonResponse {
        $this->abortUnlessComponentBelongsToPage($page, $component);
        $action->execute($component);
        $this->markDraftAndForgetCache($page);

        return response()->json([
            'success' => true,
        ]);
    }

    public function reorder(
        Request $request,
        DynamicLandingPage $page,
        ReorderDynamicLandingPageComponents $action
    ): JsonResponse {
        $data = $request->validate([
            'component_ids' => ['required', 'array'],
            'component_ids.*' => ['integer', 'min:1'],
        ]);

        $action->execute($page, $data['component_ids']);
        $this->markDraftAndForgetCache($page);

        return response()->json([
            'success' => true,
            'data' => $page->fresh(['components'])->components
                ->map(fn ($component) => $this->serializeComponent($component))
                ->values()
                ->all(),
        ]);
    }

    public function duplicate(
        DynamicLandingPage $page,
        DynamicLandingPageComponent $component,
        DuplicateDynamicLandingPageComponent $action
    ): JsonResponse {
        $this->abortUnlessComponentBelongsToPage($page, $component);
        $component = $action->execute($component);
        $this->markDraftAndForgetCache($page);

        return response()->json([
            'data' => $this->serializeComponent($component),
        ], 201);
    }

    public function visibility(
        Request $request,
        DynamicLandingPage $page,
        DynamicLandingPageComponent $component,
        SetDynamicLandingPageComponentVisibility $action
    ): JsonResponse {
        $this->abortUnlessComponentBelongsToPage($page, $component);

        $data = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        $component = $action->execute($component, (bool) $data['is_enabled']);
        $this->markDraftAndForgetCache($page);

        return response()->json([
            'data' => $this->serializeComponent($component),
        ]);
    }

    private function abortUnlessComponentBelongsToPage(
        DynamicLandingPage $page,
        DynamicLandingPageComponent $component
    ): void {
        abort_unless((int) $component->dynamic_landing_page_id === (int) $page->id, 404);
    }

    private function serializeComponent(DynamicLandingPageComponent $component): array
    {
        return [
            'id' => $component->id,
            'component_key' => $component->component_key,
            'instance_scope' => $component->instance_scope,
            'sort_order' => (int) $component->sort_order,
            'config' => $component->config ?? [],
            'is_enabled' => (bool) $component->is_enabled,
            'updated_at' => $component->updated_at?->toISOString(),
        ];
    }

    private function markDraftAndForgetCache(DynamicLandingPage $page): void
    {
        $page->forceFill(['status' => 'draft'])->save();
        $this->publishedPageCache->forgetPage($page);
    }
}

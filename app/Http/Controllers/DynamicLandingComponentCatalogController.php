<?php

namespace App\Http\Controllers;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\Contracts\LandingComponentDefinition;
use App\Services\Landing\LandingComponentRegistry;
use App\Services\Landing\LandingComponentRenderer;
use App\Services\Landing\LandingTheme;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;

class DynamicLandingComponentCatalogController extends Controller
{
    public function __construct(
        private LandingComponentRegistry $registry
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => collect($this->registry->all())
                ->map(fn (LandingComponentDefinition $definition) => $this->serializeDefinition($definition))
                ->values()
                ->all(),
        ]);
    }

    public function show(string $componentKey): JsonResponse
    {
        try {
            $definition = $this->registry->get($componentKey);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return response()->json([
            'data' => $this->serializeDefinition($definition),
        ]);
    }

    public function preview(
        string $componentKey,
        LandingComponentRenderer $componentRenderer,
        LandingTheme $theme
    ): Response {
        try {
            $definition = $this->registry->get($componentKey);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        $page = new DynamicLandingPage([
            'name' => $definition->name(),
            'slug' => 'component-preview',
            'status' => 'draft',
            'theme' => [],
            'seo' => [],
        ]);

        $component = new DynamicLandingPageComponent([
            'dynamic_landing_page_id' => 0,
            'component_key' => $definition->key(),
            'instance_scope' => 'cmp_preview_' . preg_replace('/[^A-Za-z0-9_]/', '_', $definition->key()),
            'sort_order' => 1,
            'config' => $definition->defaults(),
            'is_enabled' => true,
        ]);

        $page->setRelation('components', new EloquentCollection([$component]));

        return response(view('landing.show', [
            'page' => $page,
            'componentRenderer' => $componentRenderer,
            'themeTokens' => $theme->normalize([]),
        ])->render());
    }

    private function serializeDefinition(LandingComponentDefinition $definition): array
    {
        return [
            'key' => $definition->key(),
            'name' => $definition->name(),
            'category' => $definition->category(),
            'schema' => $definition->schema(),
            'defaults' => $definition->defaults(),
            'behaviours' => $definition->behaviours(),
            'data_resolver' => $definition->dataResolver(),
            'preview_image' => asset("images/components/{$definition->key()}.png"),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DynamicLandingPage;
use App\Models\DynamicLandingPageComponent;
use App\Models\DynamicLandingSavedSection;
use App\Services\Landing\LandingComponentConfigService;
use App\Services\Landing\LandingPublishedPageCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DynamicLandingSavedSectionController extends Controller
{
    public function __construct(
        private LandingComponentConfigService $configService,
        private LandingPublishedPageCache $publishedPageCache
    ) {
    }

    public function index(): JsonResponse
    {
        $sections = DynamicLandingSavedSection::query()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $sections->map(fn (DynamicLandingSavedSection $section) => $this->serializeSection($section))
                ->values()
                ->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedSectionData($request);
        $components = $this->validatedComponents($data['components']);

        $section = DynamicLandingSavedSection::create([
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'components' => $components,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'data' => $this->serializeSection($section),
        ], 201);
    }

    public function destroy(DynamicLandingSavedSection $section): JsonResponse
    {
        $section->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function apply(
        Request $request,
        DynamicLandingPage $page,
        DynamicLandingSavedSection $section
    ): JsonResponse {
        $components = $this->appendComponents($page, $section->components ?? [], $request->user()?->id);

        return response()->json([
            'success' => true,
            'data' => $components,
        ], 201);
    }

    public function import(Request $request, DynamicLandingPage $page): JsonResponse
    {
        $data = $request->validate([
            'components' => ['required', 'array', 'min:1'],
            'components.*' => ['required', 'array'],
            'components.*.component_key' => ['required', 'string'],
            'components.*.config' => ['nullable', 'array'],
            'components.*.is_enabled' => ['nullable', 'boolean'],
        ]);

        $components = $this->appendComponents($page, $data['components'], $request->user()?->id);

        return response()->json([
            'success' => true,
            'data' => $components,
        ], 201);
    }

    private function validatedSectionData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'components' => ['required', 'array', 'min:1'],
            'components.*' => ['required', 'array'],
            'components.*.component_key' => ['required', 'string'],
            'components.*.config' => ['nullable', 'array'],
            'components.*.is_enabled' => ['nullable', 'boolean'],
        ]);
    }

    private function appendComponents(DynamicLandingPage $page, array $components, ?int $userId = null): array
    {
        $components = $this->validatedComponents($components);

        $created = DB::transaction(function () use ($page, $components, $userId) {
            $lockedPage = DynamicLandingPage::whereKey($page->getKey())->lockForUpdate()->firstOrFail();
            $nextOrder = (int) $page->components()->max('sort_order');
            $created = [];

            foreach ($components as $component) {
                $created[] = $page->components()->create([
                    'component_key' => $component['component_key'],
                    'instance_scope' => $this->generateInstanceScope(),
                    'sort_order' => ++$nextOrder,
                    'config' => $component['config'],
                    'is_enabled' => $component['is_enabled'],
                ]);
            }

            $lockedPage->forceFill([
                'status' => 'draft',
                'updated_by' => $userId,
            ])->save();

            return collect($created)
                ->map(fn ($component) => $this->serializeComponent($component))
                ->values()
                ->all();
        });

        $this->publishedPageCache->forgetPage($page);

        return $created;
    }

    private function serializeSection(DynamicLandingSavedSection $section): array
    {
        return [
            'id' => $section->id,
            'name' => $section->name,
            'category' => $section->category,
            'components_count' => count($section->components ?? []),
            'components' => $section->components ?? [],
            'updated_at' => $section->updated_at?->toISOString(),
        ];
    }

    private function serializeComponent($component): array
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

    private function validatedComponents(array $components): array
    {
        return collect($components)
            ->map(function ($component, int $index) {
                if (!is_array($component)) {
                    throw ValidationException::withMessages([
                        "components.{$index}" => 'Each component must be a valid object.',
                    ]);
                }

                $componentKey = (string) ($component['component_key'] ?? '');
                $config = is_array($component['config'] ?? null) ? $component['config'] : [];

                try {
                    $validatedConfig = $this->configService->validateForStorage($componentKey, $config);
                } catch (InvalidArgumentException) {
                    throw ValidationException::withMessages([
                        "components.{$index}.component_key" => 'The selected component is not registered.',
                    ]);
                }

                return [
                    'component_key' => $componentKey,
                    'config' => $validatedConfig,
                    'is_enabled' => (bool) ($component['is_enabled'] ?? true),
                ];
            })
            ->values()
            ->all();
    }

    private function generateInstanceScope(): string
    {
        do {
            $scope = 'cmp_' . Str::lower(Str::random(12));
        } while (DynamicLandingPageComponent::where('instance_scope', $scope)->exists());

        return $scope;
    }
}

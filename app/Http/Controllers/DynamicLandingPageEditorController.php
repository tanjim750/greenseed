<?php

namespace App\Http\Controllers;

use App\Models\DynamicLandingPage;
use App\Services\Landing\LandingPublishedPageCache;
use App\Services\Landing\LandingTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class DynamicLandingPageEditorController extends Controller
{
    public function __construct(
        private LandingTheme $theme,
        private LandingPublishedPageCache $publishedPageCache
    ) {
    }

    public function index(): JsonResponse
    {
        $perPage = max(1, min((int) request('per_page', 25), 100));

        $pages = DynamicLandingPage::query()
            ->withCount('components')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $pages->getCollection()
                ->map(fn (DynamicLandingPage $page) => $this->serializePage($page))
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedPageData($request);

        $page = DynamicLandingPage::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'status' => 'draft',
            'theme' => $this->theme->normalize($data['theme'] ?? []),
            'seo' => $data['seo'] ?? [],
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'data' => $this->serializePage($page->fresh(['components'])),
        ], 201);
    }

    public function show(DynamicLandingPage $page): JsonResponse
    {
        return response()->json([
            'data' => $this->serializePage($page->load([
                'components' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'publishedVersion',
            ])),
        ]);
    }

    public function update(Request $request, DynamicLandingPage $page): JsonResponse
    {
        $oldSlug = $page->slug;
        $data = $this->validatedPageData($request, $page);

        $page->fill([
            'name' => $data['name'] ?? $page->name,
            'slug' => $data['slug'] ?? $page->slug,
            'theme' => array_key_exists('theme', $data) ? $this->theme->normalize($data['theme'] ?? []) : $page->theme,
            'seo' => array_key_exists('seo', $data) ? ($data['seo'] ?? []) : $page->seo,
            'updated_by' => $request->user()?->id,
        ]);

        if (($data['status'] ?? null) === 'draft') {
            $page->status = 'draft';
        }

        $page->save();

        if ($oldSlug !== $page->slug || ($data['status'] ?? null) === 'draft') {
            $this->publishedPageCache->forgetSlug($oldSlug);
            $this->publishedPageCache->forgetPage($page);
        }

        return response()->json([
            'data' => $this->serializePage($page->fresh(['components', 'publishedVersion'])),
        ]);
    }

    public function destroy(DynamicLandingPage $page): JsonResponse
    {
        $this->publishedPageCache->forgetPage($page);
        $page->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    private function validatedPageData(Request $request, ?DynamicLandingPage $page = null): array
    {
        return $request->validate([
            'name' => [$page ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => [
                $page ? 'sometimes' : 'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('dynamic_landing_pages', 'slug')->ignore($page?->id),
            ],
            'status' => ['sometimes', 'in:draft'],
            'theme' => ['sometimes', 'nullable', 'array'],
            'theme.layout' => ['sometimes', 'array'],
            'theme.layout.margin' => ['sometimes', 'array', 'size:4'],
            'theme.layout.margin.*' => ['nullable', 'string', 'max:50'],
            'theme.layout.padding' => ['sometimes', 'array', 'size:4'],
            'theme.layout.padding.*' => ['nullable', 'string', 'max:50'],
            'seo' => ['sometimes', 'nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:255'],
            'seo.description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function serializePage(DynamicLandingPage $page): array
    {
        return [
            'id' => $page->id,
            'name' => $page->name,
            'slug' => $page->slug,
            'status' => $page->status,
            'theme' => $page->theme ?? [],
            'seo' => $page->seo ?? [],
            'published_at' => $page->published_at?->toISOString(),
            'updated_at' => $page->updated_at?->toISOString(),
            'preview_url' => URL::temporarySignedRoute(
                'admin.dynamic_landing_pages.preview',
                now()->addMinutes(30),
                ['page' => $page->id]
            ),
            'public_url' => ($page->isPublished() || ($page->relationLoaded('publishedVersion') && $page->publishedVersion))
                ? route('dynamic_landing.public.show', ['slug' => $page->slug])
                : null,
            'published_version' => $page->relationLoaded('publishedVersion') && $page->publishedVersion
                ? [
                    'id' => $page->publishedVersion->id,
                    'version_number' => $page->publishedVersion->version_number,
                    'published_at' => $page->publishedVersion->published_at?->toISOString(),
                ]
                : null,
            'components_count' => $page->components_count ?? ($page->relationLoaded('components') ? $page->components->count() : null),
            'components' => $page->relationLoaded('components')
                ? $page->components->map(fn ($component) => $this->serializeComponent($component))->values()->all()
                : null,
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
}

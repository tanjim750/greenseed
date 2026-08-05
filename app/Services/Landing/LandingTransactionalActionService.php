<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingActionAttempt;
use App\Models\DynamicLandingPageComponent;
use App\Models\DynamicLandingPageVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class LandingTransactionalActionService
{
    public function __construct(
        private LandingTransactionalActionRegistry $registry
    ) {
    }

    public function execute(string $actionKey, array $payload, Request $request): LandingActionResult
    {
        $componentId = (int) ($payload['component_id'] ?? 0);
        $idempotencyKey = trim((string) ($payload['idempotency_key'] ?? ''));
        $publishedVersionId = (int) ($payload['published_version_id'] ?? 0);

        if ($componentId <= 0 || $idempotencyKey === '') {
            throw ValidationException::withMessages([
                'component_id' => ['A valid component id is required.'],
                'idempotency_key' => ['An idempotency key is required.'],
            ]);
        }

        validator(
            [
                'idempotency_key' => $idempotencyKey,
            ],
            [
                'idempotency_key' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9._:-]+$/'],
            ]
        )->validate();

        $handler = $this->registry->get($actionKey);
        $requestHash = hash('sha256', json_encode($this->hashablePayload($payload), JSON_UNESCAPED_UNICODE));

        return DB::transaction(function () use ($actionKey, $payload, $request, $componentId, $idempotencyKey, $handler, $requestHash, $publishedVersionId) {
            $snapshotBacked = $publishedVersionId > 0;
            $sourceComponentId = $componentId;
            $attemptComponentId = $componentId;

            if ($snapshotBacked) {
                $component = $this->componentFromPublishedSnapshot($componentId, $publishedVersionId);
                $attemptComponentId = null;
            } else {
                $component = DynamicLandingPageComponent::query()
                    ->whereKey($componentId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $component->load('page');
            }

            $this->validateContext($component, $handler->supportedComponentKeys(), $snapshotBacked);

            $attemptQuery = DynamicLandingActionAttempt::query()
                ->where('action_key', $actionKey)
                ->where('idempotency_key', $idempotencyKey);

            if ($snapshotBacked) {
                $attemptQuery
                    ->where('dynamic_landing_page_version_id', $publishedVersionId)
                    ->where('source_component_id', $sourceComponentId);
            } else {
                $attemptQuery->where('dynamic_landing_page_component_id', $attemptComponentId);
            }

            $attempt = $attemptQuery->lockForUpdate()->first();

            if ($attempt) {
                if ($attempt->request_hash !== $requestHash) {
                    throw new HttpException(409, 'This idempotency key was already used with different payload.');
                }

                if ($attempt->status === 'succeeded' && is_array($attempt->response)) {
                    return LandingActionResult::success($attempt->response, $attempt->order_id);
                }
            } else {
                $attempt = DynamicLandingActionAttempt::create([
                    'dynamic_landing_page_component_id' => $attemptComponentId,
                    'dynamic_landing_page_version_id' => $snapshotBacked ? $publishedVersionId : null,
                    'source_component_id' => $snapshotBacked ? $sourceComponentId : null,
                    'action_key' => $actionKey,
                    'idempotency_key' => $idempotencyKey,
                    'request_hash' => $requestHash,
                    'status' => 'pending',
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                ]);
            }

            try {
                $result = $handler->handle($component, $payload, $request);
            } catch (ValidationException $exception) {
                $result = LandingActionResult::failure([
                    'success' => false,
                    'message' => collect($exception->errors())->flatten()->first() ?? 'The submitted data is invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            } catch (\Throwable $exception) {
                $attempt->update([
                    'status' => 'failed',
                    'response' => [
                        'success' => false,
                        'message' => $exception->getMessage(),
                    ],
                ]);

                throw $exception;
            }

            $attempt->update([
                'status' => $result->success ? 'succeeded' : 'failed',
                'order_id' => $result->orderId,
                'response' => $result->payload,
            ]);

            return $result;
        });
    }

    private function validateContext(
        DynamicLandingPageComponent $component,
        array $supportedComponentKeys,
        bool $snapshotBacked = false
    ): void {
        if (!$component->is_enabled) {
            throw new HttpException(404, 'Landing component is not available.');
        }

        if (!$snapshotBacked && (!$component->page || !$component->page->isPublished())) {
            throw new HttpException(403, 'Landing page is not published.');
        }

        if (!in_array($component->component_key, $supportedComponentKeys, true)) {
            throw ValidationException::withMessages([
                'component_id' => ['This component does not support the requested action.'],
            ]);
        }
    }

    private function componentFromPublishedSnapshot(
        int $sourceComponentId,
        int $publishedVersionId
    ): DynamicLandingPageComponent {
        $version = DynamicLandingPageVersion::query()
            ->with('page')
            ->whereKey($publishedVersionId)
            ->where('status', 'published')
            ->lockForUpdate()
            ->firstOrFail();

        $snapshotComponent = collect($version->snapshot['components'] ?? [])
            ->first(function ($snapshotComponent) use ($sourceComponentId) {
                $sourceId = $snapshotComponent['source_component_id'] ?? $snapshotComponent['id'] ?? null;

                return (int) $sourceId === $sourceComponentId;
            });

        if (!$snapshotComponent) {
            throw new HttpException(404, 'Published landing component is not available.');
        }

        $currentComponent = DynamicLandingPageComponent::query()
            ->whereKey($sourceComponentId)
            ->lockForUpdate()
            ->first();

        if (
            $currentComponent
            && (int) $currentComponent->dynamic_landing_page_id !== (int) $version->dynamic_landing_page_id
        ) {
            throw new HttpException(404, 'Published landing component is not available.');
        }

        $component = $currentComponent ?: new DynamicLandingPageComponent();
        $component->setRawAttributes(array_merge($component->getAttributes(), [
            'id' => $sourceComponentId,
            'dynamic_landing_page_id' => $version->dynamic_landing_page_id,
            'component_key' => $snapshotComponent['component_key'] ?? '',
            'instance_scope' => $snapshotComponent['instance_scope'] ?? 'cmp_snapshot',
            'sort_order' => (int) ($snapshotComponent['sort_order'] ?? 0),
            'is_enabled' => (bool) ($snapshotComponent['is_enabled'] ?? true),
        ]), true);
        $component->config = $snapshotComponent['config'] ?? [];
        $component->setRelation('page', $version->page);
        $component->setRelation('dynamicLandingPage', $version->page);

        return $component;
    }

    private function hashablePayload(array $payload): array
    {
        unset($payload['_token']);
        ksort($payload);

        return $payload;
    }
}

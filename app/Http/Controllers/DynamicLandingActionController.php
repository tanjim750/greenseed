<?php

namespace App\Http\Controllers;

use App\Services\Landing\LandingTransactionalActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynamicLandingActionController extends Controller
{
    public function __construct(
        private LandingTransactionalActionService $actions
    ) {
    }

    public function store(Request $request, string $actionKey): JsonResponse
    {
        $result = $this->actions->execute($actionKey, $request->all(), $request);

        return response()->json($result->payload, $result->statusCode);
    }
}

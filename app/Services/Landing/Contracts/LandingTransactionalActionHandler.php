<?php

namespace App\Services\Landing\Contracts;

use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\LandingActionResult;
use Illuminate\Http\Request;

interface LandingTransactionalActionHandler
{
    public function key(): string;

    public function supportedComponentKeys(): array;

    public function handle(
        DynamicLandingPageComponent $component,
        array $payload,
        Request $request
    ): LandingActionResult;
}

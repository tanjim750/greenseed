<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DynamicLandingPageBuilderController extends Controller
{
    public function pages(): View
    {
        return view('backend.dynamic_landing_pages.index');
    }

    public function index(): View
    {
        return view('backend.dynamic_landing_pages.builder');
    }

    public function v2(): View
    {
        return view('backend.dynamic_landing_pages.builder_v2');
    }
}

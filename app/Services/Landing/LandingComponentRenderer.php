<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPageComponent;
use Illuminate\Contracts\View\View;

final class LandingComponentRenderer
{
    public function __construct(
        private LandingComponentRegistry $registry,
        private LandingComponentConfigResolver $configResolver,
        private LandingComponentDataService $dataService,
        private LandingStyleResolver $styleResolver,
        private LandingRenderSupport $renderSupport
    ) {
    }

    public function render(
        DynamicLandingPageComponent $component,
        array $pageTheme = []
    ): View {
        $definition = $this->registry->get($component->component_key);
        $config = $this->configResolver->resolve($definition, $component->config ?? []);
        $resolvedData = $this->dataService->resolve($definition, $component, $config);
        $resolvedStyle = $this->styleResolver->resolve($config['style'] ?? [], $pageTheme);

        return view($definition->view(), [
            'component' => $component,
            'definition' => $definition,
            'scope' => $this->renderSupport->scopeClass($component->instance_scope),
            'config' => $config,
            'content' => $config['content'] ?? [],
            'style' => $config['style'] ?? [],
            'resolvedStyle' => $resolvedStyle,
            'settings' => $config['settings'] ?? [],
            'behaviours' => $config['behaviours'] ?? [],
            'dataSource' => $config['data_source'] ?? [],
            'resolvedData' => $resolvedData,
            'pageTheme' => $pageTheme,
        ]);
    }

    public function renderHtml(
        DynamicLandingPageComponent $component,
        array $pageTheme = []
    ): string {
        return $this->render($component, $pageTheme)->render();
    }
}

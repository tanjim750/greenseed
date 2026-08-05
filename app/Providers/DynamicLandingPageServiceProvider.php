<?php

namespace App\Providers;

use App\Services\Landing\Actions\AddDynamicLandingPageComponent;
use App\Services\Landing\Actions\DeleteDynamicLandingPageComponent;
use App\Services\Landing\Actions\DuplicateDynamicLandingPageComponent;
use App\Services\Landing\Actions\ReorderDynamicLandingPageComponents;
use App\Services\Landing\Actions\SetDynamicLandingPageComponentVisibility;
use App\Services\Landing\Actions\SubmitLandingOrderAction;
use App\Services\Landing\Actions\UpdateDynamicLandingPageComponentConfig;
use App\Services\Landing\Components\HeroCountdownV1;
use App\Services\Landing\Components\ProductGridV1;
use App\Services\Landing\Components\SeedBenefitsV1;
use App\Services\Landing\Components\SeedCheckoutV1;
use App\Services\Landing\Components\SeedCheckoutV2;
use App\Services\Landing\Components\SeedFooterV1;
use App\Services\Landing\Components\SeedGalleryV1;
use App\Services\Landing\Components\SeedMobileCheckoutStickyV1;
use App\Services\Landing\Components\SeedMobileFooterV1;
use App\Services\Landing\Components\SeedMobileGalleryV1;
use App\Services\Landing\Components\SeedMobileOfferCountdownV1;
use App\Services\Landing\Components\SeedMobileTrustHeroV1;
use App\Services\Landing\Components\SeedMobileWhyBuyV1;
use App\Services\Landing\Components\SeedMobileWhyGrowV1;
use App\Services\Landing\Components\SeedOfferHeroV1;
use App\Services\Landing\Components\SeedSupportV1;
use App\Services\Landing\DataResolvers\ProductGridResolver;
use App\Services\Landing\LandingComponentConfigResolver;
use App\Services\Landing\LandingComponentConfigService;
use App\Services\Landing\LandingComponentConfigValidator;
use App\Services\Landing\LandingComponentDataResolverRegistry;
use App\Services\Landing\LandingComponentDataService;
use App\Services\Landing\LandingComponentDefaultConfigNormalizer;
use App\Services\Landing\LandingComponentRenderer;
use App\Services\Landing\LandingComponentRegistry;
use App\Services\Landing\LandingPageRenderer;
use App\Services\Landing\LandingPagePublicationService;
use App\Services\Landing\LandingPageSnapshotBuilder;
use App\Services\Landing\LandingPublishedPageCache;
use App\Services\Landing\LandingRenderSupport;
use App\Services\Landing\LandingStyleResolver;
use App\Services\Landing\LandingTheme;
use App\Services\Landing\LandingTransactionalActionRegistry;
use App\Services\Landing\LandingTransactionalActionService;
use Illuminate\Support\ServiceProvider;

class DynamicLandingPageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LandingComponentRegistry::class, function () {
            $registry = new LandingComponentRegistry();

            $registry->register(app(HeroCountdownV1::class));
            $registry->register(app(ProductGridV1::class));
            $registry->register(app(SeedOfferHeroV1::class));
            $registry->register(app(SeedBenefitsV1::class));
            $registry->register(app(SeedGalleryV1::class));
            $registry->register(app(SeedCheckoutV1::class));
            $registry->register(app(SeedCheckoutV2::class));
            $registry->register(app(SeedSupportV1::class));
            $registry->register(app(SeedFooterV1::class));
            $registry->register(app(SeedMobileTrustHeroV1::class));
            $registry->register(app(SeedMobileOfferCountdownV1::class));
            $registry->register(app(SeedMobileWhyGrowV1::class));
            $registry->register(app(SeedMobileGalleryV1::class));
            $registry->register(app(SeedMobileWhyBuyV1::class));
            $registry->register(app(SeedMobileCheckoutStickyV1::class));
            $registry->register(app(SeedMobileFooterV1::class));

            return $registry;
        });

        $this->app->singleton(LandingComponentDataResolverRegistry::class, function () {
            $registry = new LandingComponentDataResolverRegistry();

            $registry->register('product-grid', ProductGridResolver::class);

            return $registry;
        });

        $this->app->singleton(LandingTransactionalActionRegistry::class, function () {
            $registry = new LandingTransactionalActionRegistry();

            $registry->register('order-submission', SubmitLandingOrderAction::class);

            return $registry;
        });

        $this->app->singleton(LandingComponentDefaultConfigNormalizer::class);
        $this->app->singleton(LandingComponentConfigResolver::class);
        $this->app->singleton(LandingComponentConfigValidator::class);
        $this->app->singleton(LandingComponentConfigService::class);
        $this->app->singleton(LandingComponentDataService::class);
        $this->app->singleton(LandingTheme::class);
        $this->app->singleton(LandingStyleResolver::class);
        $this->app->singleton(LandingRenderSupport::class);
        $this->app->singleton(LandingComponentRenderer::class);
        $this->app->singleton(LandingPageRenderer::class);
        $this->app->singleton(LandingPageSnapshotBuilder::class);
        $this->app->singleton(LandingPublishedPageCache::class);
        $this->app->singleton(LandingPagePublicationService::class);
        $this->app->singleton(LandingTransactionalActionService::class);
        $this->app->singleton(AddDynamicLandingPageComponent::class);
        $this->app->singleton(UpdateDynamicLandingPageComponentConfig::class);
        $this->app->singleton(ReorderDynamicLandingPageComponents::class);
        $this->app->singleton(DuplicateDynamicLandingPageComponent::class);
        $this->app->singleton(SetDynamicLandingPageComponentVisibility::class);
        $this->app->singleton(DeleteDynamicLandingPageComponent::class);
        $this->app->singleton(SubmitLandingOrderAction::class);
    }
}

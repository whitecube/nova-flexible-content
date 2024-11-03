<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Wmt\NovaFlexibleContent\Commands\CreateCast;
use Wmt\NovaFlexibleContent\Commands\CreateLayout;
use Wmt\NovaFlexibleContent\Commands\CreatePreset;
use Wmt\NovaFlexibleContent\Commands\CreateResolver;
use Wmt\NovaFlexibleContent\Http\Middleware\InterceptFlexibleAttributes;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->addMiddleware();

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-flexible-content', __DIR__ . '/../dist/js/field.js');
            Nova::style('nova-flexible-content', __DIR__ . '/../dist/css/field.css');
        });
    }

    public function register(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CreateCast::class,
            CreateLayout::class,
            CreatePreset::class,
            CreateResolver::class,
        ]);
    }

    public function addMiddleware(): void
    {
        $router = $this->app['router'];

        if ($router->hasMiddlewareGroup('nova')) {
            $router->pushMiddlewareToGroup('nova', InterceptFlexibleAttributes::class);

            return;
        }

        if (!$this->app->configurationIsCached()) {
            config()->set('nova.middleware', array_merge(
                config('nova.middleware', []),
                [InterceptFlexibleAttributes::class]
            ));
        }
    }
}

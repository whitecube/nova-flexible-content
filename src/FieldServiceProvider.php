<?php

namespace Dwarfhq\NovaFlexibleContent;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Dwarfhq\NovaFlexibleContent\Commands\CreateCast;
use Dwarfhq\NovaFlexibleContent\Commands\CreateLayout;
use Dwarfhq\NovaFlexibleContent\Commands\CreatePreset;
use Dwarfhq\NovaFlexibleContent\Commands\CreateResolver;
use Dwarfhq\NovaFlexibleContent\Http\Middleware\InterceptFlexibleAttributes;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addMiddleware();

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-flexible-content', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-flexible-content', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CreateCast::class,
            CreateLayout::class,
            CreatePreset::class,
            CreateResolver::class,
        ]);
    }

    /**
     * Adds required middleware for Nova requests.
     *
     * @return void
     */
    public function addMiddleware()
    {
        $router = $this->app['router'];

        if ($router->hasMiddlewareGroup('nova')) {
            $router->pushMiddlewareToGroup('nova', InterceptFlexibleAttributes::class);

            return;
        }

        if (! $this->app->configurationIsCached()) {
            config()->set('nova.middleware', array_merge(
                config('nova.middleware', []),
                [InterceptFlexibleAttributes::class]
            ));
        }
    }
}

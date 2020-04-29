<?php

namespace Whitecube\NovaFlexibleContent;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use Whitecube\NovaFlexibleContent\Commands\CreateCast;
use Whitecube\NovaFlexibleContent\Commands\CreateLayout;
use Whitecube\NovaFlexibleContent\Commands\CreatePreset;
use Whitecube\NovaFlexibleContent\Commands\CreateResolver;
use Whitecube\NovaFlexibleContent\Http\Middleware\InterceptFlexibleAttributes;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['router']->pushMiddlewareToGroup('nova', InterceptFlexibleAttributes::class);

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
        if (!$this->app->runningInConsole()) return;

        $this->commands([
            CreateCast::class,
            CreateLayout::class,
            CreatePreset::class,
            CreateResolver::class,
        ]);
    }
}

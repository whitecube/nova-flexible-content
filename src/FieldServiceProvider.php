<?php

namespace Whitecube\NovaFlexibleContent;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
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
        $router = $this->app['router'];
        if ($router->hasMiddlewareGroup('nova')) {
            $this->app['router']->pushMiddlewareToGroup( 'nova', InterceptFlexibleAttributes::class );
        }
        elseif (! $this->app->configurationIsCached()) {
            $this->app['config']->set('nova.middleware', array_merge(
                $this->app['config']->get('nova.middleware', []),
                [InterceptFlexibleAttributes::class]
            ));
        }
        
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
            CreateLayout::class,
            CreatePreset::class,
            CreateResolver::class,
        ]);
    }
}

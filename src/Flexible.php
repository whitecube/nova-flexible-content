<?php

namespace Whitecube\NovaFlexibleContent;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\LayoutInterface;
use Whitecube\NovaFlexibleContent\Value\Resolver;
use Whitecube\NovaFlexibleContent\Value\ResolverInterface;

class Flexible extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-flexible-content';

    /**
     * The available layouts collection
     *
     * @var Illuminate\Support\Collection
     */
    protected $layouts;

    /**
     * The field's value setter & getter
     *
     * @var Whitecube\NovaFlexibleContent\Value\ResolverInterface
     */
    protected $resolver;

    /**
     * Create a fresh flexible field instance
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->button('Add layout');
    }

    /**
     * Set the button's label
     *
     * @param string $label
     * @return this
     */
    public function button($label)
    {
        return $this->withMeta(['button' => $label]);
    }

    /**
     * Set the field's resolver
     *
     * @param string $classname
     * @return this
     */
    public function resolver($classname)
    {
        $resolver = new $classname();

        if(!($resolver instanceof ResolverInterface)) {
            throw new \Exception('Resolver Class "' . get_class($resolver) . '" does not implement ResolverInterface.');
        }

        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Register a new layout
     *
     * @return this
     */
    public function addLayout(...$arguments)
    {
        $count = count($arguments);

        if($count === 3) {
            $this->registerLayout(new Layout($arguments[0], $arguments[1], $arguments[2]));
            return $this;
        }

        if($count !== 1) {
            throw new \Exception('Invalid "addLayout" method call. Expected 1 or 3 arguments, ' . $count . ' given.');
        }

        $layout = $arguments[0];
        $layout = new $layout();

        if(!($layout instanceof LayoutInterface)) {
            throw new \Exception('Layout Class "' . get_class($layout) . '" does not implement LayoutInterface.');
        }

        $this->registerLayout($layout);

        return $this;
    }

    /**
     * Push a layout instance into the layouts collection
     *
     * @param Whitecube\NovaFlexibleContent\Layouts\LayoutInterface $layout
     * @return void
     */
    protected function registerLayout(LayoutInterface $layout)
    {
        if(!$this->layouts) {
            $this->layouts = collect();
            $this->withMeta(['layouts' => $this->layouts]);
        }

        $this->layouts->push($layout);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->attribute;

        if(!$this->resolver) {
            $this->resolver(Resolver::class);
        }

        $this->value = $this->resolver->get($resource, $attribute);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $attribute = $attribute ?? $this->attribute;

        $groups = $this->fillGroups($request, $requestAttribute);

        if(!$this->resolver) {
            $this->resolver(Resolver::class);
        }

        $this->value = $this->resolver->set($model, $attribute, $groups);
    }

    /**
     * Hydrate the underlaying layouts structure & fields
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @return Illuminate\Support\Collection
     */
    protected function fillGroups(NovaRequest $request, $requestAttribute)
    {
        return collect(json_decode($request[$requestAttribute]))->map(function($item, $key) use ($request) {
            return $this->newLayoutForName(
                ScopedRequest::scopeFrom($request, (array) $item->attributes, $item->key),
                $item->layout,
                $item->key
            );
        });
    }

    /**
     * Retrieve a registered layout based on its name and return a new hydrated instance of it
     *
     * @param  Whitecube\NovaFlexibleContent\Http\ScopedRequest  $request
     * @param  string  $name
     * @param  string  $key
     * @return Illuminate\Support\Collection
     */
    protected function newLayoutForName(ScopedRequest $request, $name, $key)
    {
        $layout = $this->layouts->first(function($layout) use ($name) {
            return $layout->name() === $name;
        });

        if(!$layout) return;

        return $layout->getFilled($request, $key);
    }
}

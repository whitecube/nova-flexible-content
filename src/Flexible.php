<?php

namespace Whitecube\NovaFlexibleContent;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Whitecube\NovaFlexibleContent\Value\Resolver;
use Whitecube\NovaFlexibleContent\Value\ResolverInterface;
use Whitecube\NovaFlexibleContent\Layouts\Preset;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\LayoutInterface;
use Whitecube\NovaFlexibleContent\Layouts\Collection as LayoutsCollection;

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
     * @var Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    protected $layouts;

    /**
     * The currently defined layout groups
     *
     * @var Illuminate\Support\Collection
     */
    protected $groups;

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
     * @return $this
     */
    public function button($label)
    {
        return $this->withMeta(['button' => $label]);
    }

    /**
     * Set the field's resolver
     *
     * @param string $classname
     * @return $this
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
     * @param array $arguments
     * @return $this
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
     * Apply a field configuration preset
     *
     * @param string $classname
     * @return $this
     */
    public function preset($classname)
    {
        $preset = resolve($classname);

        $preset->handle($this);

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
            $this->layouts = new LayoutsCollection();
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

        $this->buildGroups($resource, $attribute);

        $this->value = $this->resolveGroups($this->groups);
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
        if (!$request->exists($requestAttribute)) return;

        $attribute = $attribute ?? $this->attribute;

        $this->buildGroups($model, $attribute);

        $this->syncAndFillGroups($request, $requestAttribute);

        $this->value = $this->resolver->set($model, $attribute, $this->groups);
    }

    /**
     * Process an incoming POST Request
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @return void
     */
    protected function syncAndFillGroups(NovaRequest $request, $requestAttribute)
    {
        $raw = json_decode($request[$requestAttribute]);

        if(!is_array($raw)) {
            throw new \Exception("Unable to parse incoming Flexible content, data should be an array.");
        }

        $this->groups = collect($raw)->map(function($item, $key) use ($request) {
            $layout = $item->layout;
            $key = $item->key;
            $attributes = (array) $item->attributes;

            $group = $this->findGroup($key) ?? $this->newGroup($layout, $key);

            if(!$group) return;

            $group->fill(ScopedRequest::scopeFrom($request, $attributes, $key));

            return $group;
        });
    }

    /**
     * Resolve all contained groups and their fields
     *
     * @param  Illuminate\Support\Collection  $groups
     * @return Illuminate\Support\Collection
     */
    protected function resolveGroups($groups)
    {
        return $groups->map(function($group) {
            return $group->getResolved();
        });
    }

    /**
     * Define the field's actual layout groups (as "base models") based
     * on the field's current model & attribute
     *
     * @param  mixed  $resource
     * @param  string $attribute
     * @return Illuminate\Support\Collection
     */
    protected function buildGroups($resource, $attribute)
    {
        if(!$this->resolver) {
            $this->resolver(Resolver::class);
        }

        return $this->groups = $this->resolver->get($resource, $attribute, $this->layouts);
    }

    /**
     * Find an existing group based on its key
     *
     * @param  string $key
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    protected function findGroup($key)
    {
        return $this->groups->first(function($group) use ($key) {
            return $group->matches($key);
        });
    }

    /**
     * Create a new group based on its key and layout
     *
     * @param  string $layout
     * @param  string $key
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    protected function newGroup($layout, $key)
    {
        $layout = $this->layouts->find($layout);

        if(!$layout) return;

        return $layout->duplicate($key);
    }
}

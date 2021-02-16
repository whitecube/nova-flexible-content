<?php

namespace Whitecube\NovaFlexibleContent;

use Illuminate\Support\Str;
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
     * All the validated attributes
     *
     * @var array
     */
    protected static $validatedKeys = [];

    /**
     * All the validated attributes
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public static $model;

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

        $this->button(__('Add layout'));

        // The original menu as default
        $this->menu('flexible-drop-menu');

        $this->hideFromIndex();
    }

    /**
     * @param string $component The name of the component to use for the menu
     *
     * @param array  $data
     *
     * @return $this
     */
    public function menu($component, $data = [])
    {
        return $this->withMeta(['menu' => compact('component', 'data')]);
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
     * Make the flexible content take up the full width
     * of the form. Labels will sit above
     *
     * @return mixed
     */
    public function fullWidth()
    {
        return $this->withMeta(['fullWidth' => true]);
    }

    /**
     *  Prevent the 'Add Layout' button from appearing more than once
     *
     * @return $this
     */
    public function limit($limit = 1)
    {
        return $this->withMeta(['limit' => $limit]);
    }

    /**
     * Confirm remove
     *
     * @return $this
     */
    public function confirmRemove($label = '', $yes = 'Delete', $no = 'Cancel')
    {
        return $this->withMeta([
            'confirmRemove'         => true,
            'confirmRemoveMessage'  => $label,
            'confirmRemoveYes'      => $yes,
            'confirmRemoveNo'       => $no,
        ]);
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

        if($count === 6) {
            $this->registerLayout(new Layout($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]));
            return $this;
        }

        if($count !== 1) {
            throw new \Exception('Invalid "addLayout" method call. Expected 1 or 3 arguments, ' . $count . ' given.');
        }

        $layout = $arguments[0];

        if(!($layout instanceof LayoutInterface)) {
            $layout = new $layout();
        }

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
     * @param array $params
     * @return $this
     */
    public function preset($classname, $params = [])
    {
        $preset = resolve($classname, $params);

        $preset->handle($this);

        return $this;
    }

    public function collapsed(bool $value = true)
    {
        $this->withMeta(['collapsed' => $value]);
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

        $this->registerOriginModel($resource);

        $this->buildGroups($resource, $attribute);

        $this->value = $this->resolveGroups($this->groups);
    }

    /**
     * Resolve the field's value for display on index and detail views.
     *
     * @param mixed $resource
     * @param string|null $attribute
     * @return void
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->attribute;

        $this->registerOriginModel($resource);

        $this->buildGroups($resource, $attribute);

        $this->value = $this->resolveGroupsForDisplay($this->groups);
    }

    /**
     * Check showing on detail.
     *
     * @param NovaRequest $request
     * @param $resource
     * @return bool
     */
    public function isShownOnDetail(NovaRequest $request, $resource): bool
    {
        $this->layouts = $this->layouts->each(function($layout) use ($request, $resource) {
            $layout->filterForDetail($request, $resource);
        });

        return parent::isShownOnDetail($request, $resource);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return null|Closure
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if (!$request->exists($requestAttribute)) return;

        $attribute = $attribute ?? $this->attribute;

        $this->registerOriginModel($model);

        $this->buildGroups($model, $attribute);

        $callbacks = collect($this->syncAndFillGroups($request, $requestAttribute));

        $this->value = $this->resolver->set($model, $attribute, $this->groups);

        if($callbacks->isEmpty()) {
            return;
        }

        return function() use ($callbacks) {
            $callbacks->each->__invoke();
        };
    }

    /**
     * Process an incoming POST Request
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @return array
     */
    protected function syncAndFillGroups(NovaRequest $request, $requestAttribute)
    {
        if(!($raw = $this->extractValue($request, $requestAttribute))) {
            $this->fireRemoveCallbacks(collect());
            $this->groups = collect();
            return;
        }

        $callbacks = [];

        $new_groups  = collect($raw)->map(function($item, $key) use ($request, &$callbacks) {
            $layout = $item['layout'];
            $key = $item['key'];
            $attributes = $item['attributes'];

            $group = $this->findGroup($key) ?? $this->newGroup($layout, $key);

            if(!$group) return;

            $scope = ScopedRequest::scopeFrom($request, $attributes, $key);
            $callbacks = array_merge($callbacks, $group->fill($scope));

            return $group;
        })->filter();

        $this->fireRemoveCallbacks($new_groups);

        $this->groups = $new_groups;

        return $callbacks;
    }

    /**
     * Fire's the remove callbacks on the layouts
     *
     * @param $new_groups This should be (all) the new groups to bne compared against to find the removed groups
     */
    protected function fireRemoveCallbacks($new_groups) {
        $new_group_keys = $new_groups->map(function($item) {
            return $item->inUseKey();
        });
        $removed_groups = $this->groups->filter(function ($item) use ($new_group_keys) {
            return !$new_group_keys->contains($item->inUseKey());
        })->each(function ($group) {
            if (method_exists($group, 'fireRemoveCallback')) {
                $group->fireRemoveCallback($this);
            }
        });
    }

    /**
     * Find the flexible's value in given request
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $attribute
     * @return null|array
     */
    protected function extractValue(NovaRequest $request, $attribute)
    {
        $value = $request[$attribute];

        if(!$value) return;

        if(!is_array($value)) {
            throw new \Exception("Unable to parse incoming Flexible content, data should be an array.");
        }

        return $value;
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
     * Resolve all contained groups and their fields for display on index and
     * detail views.
     *
     * @param Illuminate\Support\Collection $groups
     * @return Illuminate\Support\Collection
     */
    protected function resolveGroupsForDisplay($groups)
    {
        return $groups->map(function ($group) {
            return $group->getResolvedForDisplay();
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
     * @return \Whitecube\NovaFlexibleContent\Layouts\Layout
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
     * @return \Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    protected function newGroup($layout, $key)
    {
        $layout = $this->layouts->find($layout);

        if(!$layout) return;

        return $layout->duplicate($key);
    }

    /**
     * Get the validation rules for this field & its contained fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getRules(NovaRequest $request)
    {
        return parent::getRules($request);
    }

    /**
     * Get the creation rules for this field & its contained fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array|string
     */
    public function getCreationRules(NovaRequest $request)
    {
        return array_merge_recursive(
            parent::getCreationRules($request),
            $this->getFlexibleRules($request, 'creation')
        );
    }

    /**
     * Get the update rules for this field & its contained fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getUpdateRules(NovaRequest $request)
    {
        return array_merge_recursive(
            parent::getUpdateRules($request),
            $this->getFlexibleRules($request, 'update')
        );
    }

    /**
     * Retrieve contained fields rules and assign them to nested array attributes
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string $specificty
     * @return array
     */
    protected function getFlexibleRules(NovaRequest $request, $specificty)
    {
        if(!($value = $this->extractValue($request, $this->attribute))) {
            return [];
        }

        $rules = $this->generateRules($request, $value, $specificty);

        if(!is_a($request, ScopedRequest::class)) {
            // We're not in a nested flexible, meaning we're
            // assuming the field is located at the root of
            // the model's attributes. Therefore, we should now
            // register all the collected validation rules for later
            // reference (see Http\TransformsFlexibleErrors).
            static::registerValidationKeys($rules);

            // Then, transform the rules into an array that's actually
            // usable by Laravel's Validator.
            $rules = $this->getCleanedRules($rules);
        }

        return $rules;
    }

    /**
     * Format all contained fields rules and return them.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array $value
     * @param  string $specificty
     * @return array
     */
    protected function generateRules(NovaRequest $request, $value, $specificty)
    {
        return collect($value)->map(function ($item, $key) use ($request, $specificty) {
                    $group = $this->newGroup($item['layout'], $item['key']);

                    if(!$group) return [];

                    $scope = ScopedRequest::scopeFrom($request, $item['attributes'], $item['key']);
                    return $group->generateRules($scope, $specificty, $this->attribute . '.' . $key);
                })
                ->collapse()
                ->all();
    }

    /**
     * Transform Flexible rules array into an actual validator rules array
     *
     * @param  array $rules
     * @return array
     */
    protected function getCleanedRules(array $rules)
    {
        return array_map(function($field) {
            return $field['rules'];
        }, $rules);
    }

    /**
     * Add validation keys to the valdiatedKeys register, which will be
     * used for transforming validation errors later in the request cycle.
     *
     * @param  array $rules
     * @return void
     */
    protected static function registerValidationKeys(array $rules)
    {
        $validatedKeys = array_map(function($field) {
            return $field['attribute'];
        }, $rules);

        static::$validatedKeys = array_merge(
            static::$validatedKeys, $validatedKeys
        );
    }

    /**
     * Return a previously registered validation key
     *
     * @param  string $key
     * @return null|\Whitecube\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public static function getValidationKey($key)
    {
        return static::$validatedKeys[$key] ?? null;
    }

    /**
     * Registers a reference to the origin model for nested & contained fields
     *
     * @param  mixed $model
     * @return void
     */
    protected function registerOriginModel($model)
    {
        if (is_a($model, \Laravel\Nova\Resource::class)) {
            $model = $model->model();
        } else if (is_a($model, \Whitecube\NovaPage\Pages\Template::class)) {
            $model = $model->getOriginal();
        }

        if(!is_a($model, \Illuminate\Database\Eloquent\Model::class)) {
            return;
        }

        static::$model = $model;
    }

    /**
     * Return the previously registered origin model
     *
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public static function getOriginModel()
    {
        return static::$model;
    }
}

<?php

namespace Whitecube\NovaFlexibleContent;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Whitecube\NovaFlexibleContent\Layouts\Collection as LayoutsCollection;
use Whitecube\NovaFlexibleContent\Value\Resolver;
use Whitecube\NovaFlexibleContent\Value\ResolverInterface;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\LayoutInterface;

class Flexible extends Field
{
    const CLASSIC_RULES = 'getRules';
    const CREATION_RULES = 'getCreationRules';
    const UPDATE_RULES = 'getUpdateRules';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-flexible-content';

    /**
     * The available layouts collection
     *
     * @var \Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    protected $layouts;

    /**
     * The currently defined layout groups
     *
     * @var \Illuminate\Support\Collection
     */
    protected $groups;

    /**
     * The field's value setter & getter
     *
     * @var \Whitecube\NovaFlexibleContent\Value\ResolverInterface
     */
    protected $resolver;

    protected $populate;

    /**
     * Create a fresh flexible field instance
     *
     * @param string $name
     * @param string|null $attribute
     * @param mixed|null $resolveCallback
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
     *  Prevent the 'Add Layout' button from appearing more than once
     *
     * @return $this
     */
    public function limit($limit = 1)
    {
        return $this->withMeta(['limit' => $limit]);
    }

    /**
     * Set the field's resolver
     *
     * @param string $classname
     * @return $this
     * @throws \Exception
     */
    public function resolver($classname)
    {
        $resolver = new $classname();

        if (!($resolver instanceof ResolverInterface)) {
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
     * @throws \Exception
     */
    public function addLayout(...$arguments)
    {
        $count = count($arguments);

        if ($count === 3) {
            $this->registerLayout(new Layout($arguments[0], $arguments[1], $arguments[2]));
            return $this;
        }

        if ($count !== 1) {
            throw new \Exception('Invalid "addLayout" method call. Expected 1 or 3 arguments, ' . $count . ' given.');
        }

        $layout = $arguments[0];
        $layout = new $layout();

        if (!($layout instanceof LayoutInterface)) {
            throw new \Exception('Layout Class "' . get_class($layout) . '" does not implement LayoutInterface.');
        }

        $this->registerLayout($layout);

        return $this;
    }

    public function getLayouts(): LayoutsCollection
    {
        return $this->layouts;
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
     * @param \Whitecube\NovaFlexibleContent\Layouts\LayoutInterface $layout
     * @return void
     */
    protected function registerLayout(LayoutInterface $layout)
    {
        if (!$this->layouts) {
            $this->layouts = new LayoutsCollection();
            $this->withMeta(['layouts' => $this->layouts]);
        }

        $this->layouts->push($layout);
    }

    /**
     * Resolve the field's value.
     *
     * @param mixed $resource
     * @param string|null $attribute
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
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     * @return void
     * @throws \Exception
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
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param string $requestAttribute
     * @return void
     * @throws \Exception
     */
    protected function syncAndFillGroups(NovaRequest $request, $requestAttribute)
    {
        $raw = json_decode($request[$requestAttribute]);

        if (!is_array($raw)) {
            throw new \Exception("Unable to parse incoming Flexible content, data should be an array.");
        }

        $this->groups = collect($raw)->map(function ($item, $key) use ($request) {
            $layout = $item->layout;
            $key = $item->key;
            $attributes = (array)$item->attributes;

            $group = $this->findGroup($key) ?? $this->newGroup($layout, $key);

            if (!$group) return;

            $group->fill(ScopedRequest::scopeFrom($request, $attributes, $key));

            return $group;
        });
    }

    /**
     * Resolve all contained groups and their fields
     *
     * @param Illuminate\Support\Collection $groups
     * @return Illuminate\Support\Collection
     */
    protected function resolveGroups($groups)
    {
        return $groups->map(function ($group) {
            return $group->getResolved();
        });
    }

    /**
     * Define the field's actual layout groups (as "base models") based
     * on the field's current model & attribute
     *
     * @param mixed $resource
     * @param string $attribute
     * @return Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function buildGroups($resource, $attribute)
    {
        if (!$this->resolver) {
            $this->resolver(Resolver::class);
        }

        $this->groups = $this->resolver->get($resource, $attribute, $this->layouts);

        return $this->doPopulate();
    }

    /**
     * Find an existing group based on its key
     *
     * @param string $key
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    protected function findGroup($key)
    {
        return $this->groups->first(function ($group) use ($key) {
            return $group->matches($key);
        });
    }

    /**
     * Create a new group based on its key and layout
     *
     * @param string $layout
     * @param string $key
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    protected function newGroup($layout, $key)
    {
        $layout = $this->layouts->find($layout);

        if (!$layout) return;

        return $layout->duplicate($key);
    }

    public function populate()
    {
        $this->withMeta(['populate' => true]);
        $this->populate = true;
        return $this;
    }

    public function doPopulate()
    {
        if (!$this->populate)
            return $this->groups;


        $groups = $this->groups->map(function (Layout $layout) {
            return $layout->name();
        })->toArray();

        $layouts = $this->layouts->reject(function (Layout $layout) use ($groups) {
            return in_array($layout->name(), $groups);
        })->map(function ($layout) {
            return $layout->duplicate(Str::random(10));
        });

        return $this->groups = $this->groups->merge($layouts);
    }

    /**
     * Get rules for inner fields.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param string|null $type
     * @return array
     */
    public function getFlexibleRules(NovaRequest $request, string $type = null): array
    {
        $type = !in_array($type, [
            static::CLASSIC_RULES,
            static::CREATION_RULES,
            static::UPDATE_RULES
        ])
            ? static::CLASSIC_RULES
            : $type;

        $rules = [];

        foreach ($this->layouts as $layout) {
            /** @var \Whitecube\NovaFlexibleContent\Layouts\LayoutInterface $layout */
            $fields = $layout->fields();
            if (is_iterable($fields)) {
                $rules[$layout->name()] = collect($fields)->mapWithKeys(function ($field) use ($request, $type) {
                    $rules = data_get($field->{$type}($request), $field->attribute, []);

                    if ($field instanceof Flexible) {
                        $rules += $field->getFlexibleRules($request, $type);
                    }

                    return [$field->attribute => $rules];
                })->toArray();
            }
        }

        return $rules;
    }

    /**
     * Get creation for inner fields.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function getFlexibleCreationRules(NovaRequest $request)
    {
        return $this->getFlexibleRules($request, static::CREATION_RULES);
    }

    /**
     * Get update for inner fields.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function getFlexibleUpdateRules(NovaRequest $request)
    {
        return $this->getFlexibleRules($request, static::UPDATE_RULES);
    }

    /**
     * Validate request for creation.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    public function validateForCreation(NovaRequest $request, ValidatorContract &$validator): void
    {
        $this->validate($request, $validator, $this->getFlexibleCreationRules($request));
    }

    /**
     * Validate request for update.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    public function validateForUpdate(NovaRequest $request, ValidatorContract &$validator): void
    {
        $this->validate($request, $validator, $this->getFlexibleUpdateRules($request));
    }

    /**
     * Validate request..
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @param array|null $rules
     * @return void
     */
    public function validate(NovaRequest $request, ValidatorContract &$validator, array $rules = null): void
    {
        if (empty($rules)) {
            $rules = $this->getFlexibleRules($request);
        }

        $contents = json_decode($request->input($this->attribute), true);

        if (empty($contents)) {
            return;
        }

        $this->validateData($contents, $rules, $validator);
    }

    /**
     * Validate flexible data.
     *
     * @param array $contents
     * @param array $rules
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @param string|null $prefix
     * @return void
     */
    public function validateData(array $contents, array $rules, ValidatorContract &$validator, string $prefix = null): void
    {
        $layouts = $this->transformData($contents);

        foreach ($layouts as $layoutName => $groups) {
            /** @var \Whitecube\NovaFlexibleContent\Layouts\LayoutInterface $layout */
            $layout = $this->layouts->find($layoutName);
            if (empty($layout)) {
                continue;
            }

            $flexibleFields = $this->getFlexibleFields($layout);
            $flexibleFieldsNames = array_keys($flexibleFields);

            foreach ($groups as $key => $inputs) {
                $inputsRules = array_map(function ($rules) {
                    return array_values(array_filter($rules, function ($rule) {
                        return !is_array($rule);
                    }));
                }, data_get($rules, $layoutName, []));

                $inputsValidator = Validator::make($inputs, $inputsRules);

                if ($inputsValidator->fails()) {
                    foreach ($inputsValidator->errors()->toArray() as $attribute => $messages) {
                        foreach ($messages as $message) {
                            $validator->errors()->add("{$prefix}{$this->attribute}.{$key}__{$attribute}", $message);
                        }
                    }
                }

                $others = array_filter($inputs, function ($attribute) use ($flexibleFieldsNames) {
                    return in_array($attribute, $flexibleFieldsNames);
                }, ARRAY_FILTER_USE_KEY);

                foreach ($others as $attribute => $raw) {
                    $value = json_decode($raw, true);

                    if (empty($value) || !isset($flexibleFields[$attribute])) {
                        continue;
                    }

                    $flexibleFields[$attribute]->validateData(
                        $value,
                        data_get($rules, "{$layoutName}.{$attribute}", []),
                        $validator,
                        "{$prefix}{$this->attribute}.{$key}__"
                    );
                }
            }
        }
    }

    /**
     * Transform json decoded raw data to a group of layouts.
     *
     * @param array $raw
     * @return array
     */
    protected function transformData(array $raw): array
    {
        return collect($raw)->mapToGroups(function ($content) {
            return [
                $content['layout'] => $content,
            ];
        })
            ->map(function ($layout) {
                return collect($layout)->mapWithKeys(function ($group) {
                    return [
                        $group['key'] => collect($group['attributes'])
                            ->mapWithKeys(function ($value, $attribute) use ($group) {
                                return [str_replace("{$group['key']}__", '', $attribute) => $value];
                            })->toArray(),
                    ];
                })->toArray();
            })
            ->toArray();
    }

    /**
     * Get Flexible fields of a layout.
     *
     * @param \Whitecube\NovaFlexibleContent\Layouts\LayoutInterface $layout
     * @return array
     */
    protected function getFlexibleFields(LayoutInterface $layout): array
    {
        return collect($layout->fields())->mapWithKeys(function ($field) {
            return [$field->attribute => $field instanceof Flexible ? $field : null];
        })
            ->filter()
            ->toArray();
    }
}

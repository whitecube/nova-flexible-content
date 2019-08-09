<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use ArrayAccess;
use JsonSerializable;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Contracts\Support\Arrayable;

class Layout implements LayoutInterface, JsonSerializable, ArrayAccess, Arrayable
{
    use HasAttributes;
    use HidesAttributes;
    use HasFlexible;

    /**
     * The layout's name
     *
     * @var string
     */
    protected $name;

    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $key;

    /**
     * The layout's temporary identifier
     *
     * @var string
     */
    protected $_key;

    /**
     * The layout's title
     *
     * @var string
     */
    protected $title;

    /**
     * The layout's registered fields
     *
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Create a new base Layout instance
     *
     * @param string $title
     * @param string $name
     * @param array $fields
     * @param string $key
     * @return void
     */
    public function __construct($title = null, $name = null, $fields = null, $key = null, $attributes = [])
    {
        $this->title = $title ?? $this->title();
        $this->name = $name ?? $this->name();
        $this->fields = collect($fields ?? $this->fields());
        $this->key = is_null($key) ? null : $this->getProcessedKey($key);
        $this->setRawAttributes($this->cleanAttributes($attributes));
    }

    /**
     * Retrieve the layout's name (identifier)
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Retrieve the layout's title
     *
     * @return string
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Retrieve the layout's fields
     *
     * @return array
     */
    public function fields()
    {
        return $this->fields ? $this->fields->all() : [];
    }

    /**
     * Retrieve the layout's unique key
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Retrieve the key currently in use in the views
     *
     * @return string
     */
    public function inUseKey()
    {
        return $this->_key ?? $this->key();
    }

    /**
     * Check if this group matches the given key
     *
     * @param string $key
     * @return bool
     */
    public function matches($key)
    {
        return ($this->key === $key || $this->_key === $key);
    }

    /**
     * Resolve and return the result
     *
     * @return array
     */
    public function getResolved()
    {
        $this->resolve();

        return $this->getResolvedValue();
    }

    /**
     * Get an empty cloned instance
     *
     * @param  string  $key
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    public function duplicate($key)
    {
        return $this->duplicateAndHydrate($key);
    }

    /**
     * Get a cloned instance with set values
     *
     * @param  string  $key
     * @param  array  $attributes
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    public function duplicateAndHydrate($key, array $attributes = [])
    {
        return new static(
            $this->title,
            $this->name,
            $this->fields->all(),
            $key,
            $attributes
        );
    }

    /**
     * Resolve fields using given attributes
     *
     * @param  boolean $empty
     * @return void
     */
    public function resolve($empty = false)
    {
        $this->fields->each(function($field) use ($empty) {
            $field->resolve($empty ? [] : $this);
        });
    }

    /**
     * Get the layout's resolved representation. Best used
     * after a resolve() call
     *
     * @param  boolean $empty
     * @return void
     */
    public function getResolvedValue()
    {
        return [
            'layout' => $this->name,

            // The (old) temporary key is preferred to the new one during
            // field resolving because we need to keep track of the current
            // attributes during the next fill request that will override
            // the key with a new, stronger & definitive one.
            'key' => $this->inUseKey(),

            // The layout's fields now temporarily contain the resolved
            // values from the current group's attributes. If multiple
            // groups use the same layout, the current values will be lost
            // since each group uses the same fields by reference. That's
            // why we need to serialize the field's current state.
            'attributes' => $this->fields->jsonSerialize()
        ];
    }

    /**
     * Fill attributes using underlaying fields and incoming request
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return void
     */
    public function fill(ScopedRequest $request)
    {
        $this->fields->each(function($field) use ($request) {
            $field->fill($request, $this);
        });
    }

    /**
     * Get validation rules for fields concerned by given request
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string $specificty
     * @param  string $key
     * @return array
     */
    public function generateRules(ScopedRequest $request, $specificty, $key)
    {
        return  $this->fields->map(function($field) use ($request, $specificty, $key) {
                    return $this->getScopedFieldRules($field, $request, $specificty, $key);
                })
                ->collapse()
                ->all();
    }

    /**
     * Get validation rules for fields concerned by given request
     *
     * @param  \Laravel\Nova\Fields\Field $field
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param  null|string $specificty
     * @param  string $key
     * @return array
     */
    protected function getScopedFieldRules($field, ScopedRequest $request, $specificty, $key)
    {
        $method = 'get' . ucfirst($specificty) . 'Rules';

        $rules = call_user_func([$field, $method], $request);
        
        return  collect($rules)->mapWithKeys(function($validatorRules, $attribute) use ($key, $field) {
                    $key = $key . '.attributes.' . $attribute;
                    return [$key => $this->wrapScopedFieldRules($field, $validatorRules)];
                })
                ->filter()
                ->all();
    }

    /**
     * Wrap the rules in an array containing field information for later use
     *
     * @param  \Laravel\Nova\Fields\Field $field
     * @param  array $rules
     * @return null|array
     */
    protected function wrapScopedFieldRules($field, array $rules)
    {
        if(!$rules) {
            return;
        }

        if(is_a($rules['attribute'] ?? null, FlexibleAttribute::class)) {
            return $rules;
        }

        return [
            'attribute' => FlexibleAttribute::make($field->attribute, $this->inUseKey()),
            'rules' => $rules,
        ];
    }

    /**
     * Dynamically retrieve attributes on the layout.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the layout.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Transform empty attribute values to null
     *
     * @param  array $attributes
     * @return array
     */
    protected function cleanAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            if(!is_string($value) || strlen($value)) continue;
            $attributes[$key] = null;
        }

        return $attributes;
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    protected function getDates()
    {
        return $this->dates ?? [];
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat ?: 'Y-m-d H:i:s';
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts ?? [];
    }

    /**
     * Check if relation exists. Layouts do not have relations.
     *
     * @return bool
     */
    protected function relationLoaded()
    {
        return false;
    }

    /**
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        // Calling an empty "resolve" first in order to empty all fields
        $this->resolve(true);

        return [
            'name' => $this->name,
            'title' => $this->title,
            'fields' => $this->fields->jsonSerialize()
        ];
    }

    /**
     * Returns an unique key for this group if it's not already the case
     *
     * @param  string  $key
     * @return string
     */
    protected function getProcessedKey($key)
    {
        if(strpos($key, '-') === false && strlen($key) === 16) return $key;

        // The key is either generated by Javascript or not strong enough.
        // Before assigning a new valid key we'll keep track of this one
        // in order to keep it usable in a Flexible::findGroup($key) search.
        $this->_key = $key;

        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil(16/2));
        }
        elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil(16/2));
        }
        else {
            throw new \Exception("No cryptographically secure random function available");
        }

        return substr(bin2hex($bytes), 0, 16);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

}

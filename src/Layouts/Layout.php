<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use JsonSerializable;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class Layout implements LayoutInterface, JsonSerializable
{
    use HasAttributes;
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
        $this->setRawAttributes($attributes);
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
        return $this->resolve($this->getAttributes());
    }

    /**
     * Resolve the field for display and return the result.
     *
     * @return array
     */
    public function getResolvedForDisplay()
    {
        return $this->resolveForDisplay($this->getAttributes());
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
     * @param  array  $attributes
     * @return void
     */
    public function resolve(array $attributes = [])
    {
        $this->fields->each(function($field) use ($attributes) {
            $field->resolve($attributes);
        });

        return $this->getResolvedValue();
    }

    /**
     * Resolve fields for display using given attributes.
     *
     * @param array $attributes
     * @return array
     */
    public function resolveForDisplay(array $attributes = [])
    {
        $this->fields->each(function ($field) use ($attributes) {
            $field->resolveForDisplay($attributes);
        });

        return $this->getResolvedValue();
    }

    /**
     * Returns the final value of the layout.
     *
     * Should only be called after all the fields are resolved.
     *
     * @return array
     **/
    protected function getResolvedValue()
    {
        return [
            'layout' => $this->name,

            // The (old) temporary key is preferred to the new one during
            // field resolving because we need to keep track of the current
            // attributes during the next fill request that will override
            // the key with a new, stronger & definitive one.
            'key' => $this->_key ?? $this->key,

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
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    protected function getDates()
    {
        return $this->dates ?? [];
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
        // Calling an empty "resolve" call first in order to empty all fields
        $this->resolve();

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
}

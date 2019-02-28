<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use JsonSerializable;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class Layout implements LayoutInterface, JsonSerializable
{
    use HasAttributes;

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
     * Resolve and return the result
     *
     * @return array
     */
    public function getResolved()
    {
        return $this->resolve($this->getAttributes());
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

        return [
            'layout' => $this->name,
            'key' => $this->key,
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
     * Determine if the given attribute is a date or date castable.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isDateAttribute($key)
    {
        return false;
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
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
        if(strpos($key, '_') === false) return $key;

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

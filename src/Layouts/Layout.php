<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use JsonSerializable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class Layout implements LayoutInterface, JsonSerializable
{
    use HasAttributes;

    /**
     * The layout's identifier
     *
     * @var string
     */
    protected $name;

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
     * @param string $fields
     * @return void
     */
    public function __construct($title, $name, $fields = null)
    {
        $this->title = $title;
        $this->name = $name;
        $this->fields = collect($fields ?? $this->fields());
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
     * Get a cloned & hydrated instance
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  object  $attributes
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    public function getFilled(NovaRequest $request, $attributes)
    {
        $instance = new static(
            $this->title(),
            $this->name(),
            $this->fields->all()
        );

        $instance->hydrate($request, (array) $attributes);

        return $instance;
    }

    /**
     * fill attributes using underlaying fields
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $attributes
     * @return void
     */
    public function hydrate(NovaRequest $request, array $attributes = [])
    {
        $scopedRequest = NovaRequest::createFrom($request);
        $scopedRequest->replace($attributes);

        $this->fields->each(function($field) use ($scopedRequest) {
            $field->fill($scopedRequest, $this);
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
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name(),
            'title' => $this->title(),
            'fields' => $this->fields()
        ];
    }
}

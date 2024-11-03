<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Concerns;

use Illuminate\Support\Collection as BaseCollection;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Nova\Support\Fluent;
use Wmt\NovaFlexibleContent\Layouts\Collection;
use Wmt\NovaFlexibleContent\Layouts\Layout;
use Wmt\NovaFlexibleContent\Value\FlexibleCast;

trait HasFlexible
{
    /**
     * Parse a Flexible Content attribute
     *
     * @param string $attribute
     * @param array $layoutMapping
     * @return \Wmt\NovaFlexibleContent\Layouts\Collection
     */
    public function flexible($attribute, $layoutMapping = [])
    {
        $flexible = data_get($this->attributes, $attribute);

        return $this->cast($flexible, $layoutMapping);
    }

    /**
     * Cast a Flexible Content value
     *
     * @param mixed $value
     * @param array $layoutMapping
     * @return \Wmt\NovaFlexibleContent\Layouts\Collection
     */
    public function cast($value, $layoutMapping = [])
    {
        if (app()->getProvider(NovaServiceProvider::class) && !app()->runningInConsole()) {
            return $value;
        }

        return $this->toFlexible($value ?: null, $layoutMapping);
    }

    /**
     * Parse a Flexible Content from value
     *
     * @param array|string|\Illuminate\Support\Collection|null $value
     * @param array $layoutMapping
     * @return \Wmt\NovaFlexibleContent\Layouts\Collection
     */
    public function toFlexible($value, $layoutMapping = [])
    {
        $flexible = $this->getFlexibleArrayFromValue($value);

        if (is_null($flexible)) {
            return new Collection;
        }

        return new Collection(
            array_filter($this->getMappedFlexibleLayouts($flexible, $layoutMapping))
        );
    }

    /**
     * Transform incoming value into an array of usable layouts
     *
     * @param array|string|\Illuminate\Support\Collection|null $value
     * @return array|null
     */
    protected function getFlexibleArrayFromValue($value)
    {
        if (is_string($value)) {
            $value = json_decode($value);

            return is_array($value) ? $value : null;
        }

        if (is_a($value, BaseCollection::class)) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Map array with Flexible Content Layouts
     *
     * @return array
     */
    protected function getMappedFlexibleLayouts(array $flexible, array $layoutMapping)
    {
        return array_map(function ($item) use ($layoutMapping) {
            return $this->getMappedLayout($item, $layoutMapping);
        }, $flexible);
    }

    /**
     * Transform given layout value into a usable Layout instance
     *
     * @param mixed $item
     * @return null|\Wmt\NovaFlexibleContent\Layouts\LayoutInterface
     */
    protected function getMappedLayout($item, array $layoutMapping)
    {
        $name = null;
        $key = null;
        $attributes = [];

        if (is_string($item)) {
            $item = json_decode($item);
        }

        if (is_array($item)) {
            $name = $item['layout'] ?? null;
            $key = $item['key'] ?? null;
            $attributes = (array) ($item['attributes'] ?? []);
        } elseif (is_a($item, \stdClass::class) || is_a($item, Fluent::class)) {
            $name = $item->layout ?? null;
            $key = $item->key ?? null;
            $attributes = (array) ($item->attributes ?? []);
        } elseif (is_a($item, Layout::class)) {
            $name = $item->name();
            $key = $item->key();
            $attributes = $item->getAttributes();
        }

        if (is_null($name)) {
            return null;
        }

        return $this->createMappedLayout($name, $key, $attributes, $layoutMapping);
    }

    /**
     * Transform given layout value into a usable Layout instance
     *
     * @param string $name
     * @param string $key
     * @param array $attributes
     * @return \Wmt\NovaFlexibleContent\Layouts\LayoutInterface
     */
    protected function createMappedLayout($name, $key, $attributes, array $layoutMapping)
    {
        $classname = array_key_exists($name, $layoutMapping)
            ? $layoutMapping[$name]
            : Layout::class;

        $layout = new $classname($name, $name, [], $key, $attributes);

        $model = is_a($this, FlexibleCast::class)
            ? $this->model
            : $this;

        $layout->setModel($model);

        return $layout;
    }
}

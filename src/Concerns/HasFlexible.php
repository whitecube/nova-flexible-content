<?php

namespace Whitecube\NovaFlexibleContent\Concerns;

use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Nova\NovaServiceProvider;
use Whitecube\NovaFlexibleContent\Value\FlexibleCast;

trait HasFlexible {

    /**
     * Parse a Flexible Content attribute
     *
     * @param string $attribute
     * @param array  $layoutMapping
     * @return \Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function flexible($attribute, $layoutMapping = [])
    {
        $flexible = data_get($this->attributes, $attribute);

        return $this->cast($flexible, $layoutMapping);
    }

    /**
     * Cast a Flexible Content value
     *
     * @param array $value
     * @param array $layoutMapping
     * @return \Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function cast($value, $layoutMapping = [])
    {
        if(app()->getProvider(NovaServiceProvider::class) && !app()->environment('testing')) {
            return $value;
        }

        return $this->toFlexible($value ?: null, $layoutMapping);
    }

    /**
     * Parse a Flexible Content from value
     *
     * @param mixed $value
     * @param array $layoutMapping
     * @return \Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function toFlexible($value, $layoutMapping = [])
    {
        $flexible = $this->getFlexibleArrayFromValue($value);

        if(is_null($flexible)) {
            return new Collection();
        }

        return new Collection(
            array_filter($this->getMappedFlexibleLayouts($flexible, $layoutMapping))
        );
    }

    /**
     * Transform incoming value into an array of usable layouts
     *
     * @param mixed $value
     * @return array|null
     */
    protected function getFlexibleArrayFromValue($value)
    {
        if(is_string($value)) {
            $value = json_decode($value);
            return is_array($value) ? $value : null;
        }

        if(is_a($value, BaseCollection::class)) {
            return $value->toArray();
        }

        if(is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Map array with Flexible Content Layouts
     *
     * @param array $flexible
     * @param array $layoutMapping
     * @return array
     */
    protected function getMappedFlexibleLayouts(array $flexible, array $layoutMapping)
    {
        return array_map(function($item) use ($layoutMapping) {
            return $this->getMappedLayout($item, $layoutMapping);
        }, $flexible);
    }

    /**
     * Transform given layout value into a usable Layout instance
     *
     * @param mixed $item
     * @param array $layoutMapping
     * @return null|Whitecube\NovaFlexibleContent\Layouts\LayoutInterface
     */
    protected function getMappedLayout($item, array $layoutMapping)
    {
        $name = null;
        $key = null;
        $attributes = [];

        if(is_string($item)) {
            $item = json_decode($item);
        }

        if(is_array($item)) {
            $name = $item['layout'] ?? null;
            $key = $item['key'] ?? null;
            $attributes = (array) $item['attributes'] ?? [];
        }
        elseif(is_a($item, \stdClass::class)) {
            $name = $item->layout ?? null;
            $key = $item->key ?? null;
            $attributes = (array) ($item->attributes ?? []);
        }
        elseif(is_a($item, Layout::class)) {
            $name = $item->name();
            $key = $item->key();
            $attributes = $item->getAttributes();
        }

        if(is_null($name)) {
            return;
        }

        return $this->createMappedLayout($name, $key, $attributes, $layoutMapping);
    }

    /**
     * Transform given layout value into a usable Layout instance
     *
     * @param string $name
     * @param string $key
     * @param array  $attributes
     * @param array  $layoutMapping
     * @return \Whitecube\NovaFlexibleContent\Layouts\LayoutInterface
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

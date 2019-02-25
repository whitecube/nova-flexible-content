<?php

namespace Whitecube\NovaFlexibleContent\Value;

class Resolver implements ResolverInterface
{

    /**
     * Set the field's value
     *
     * @param  mixed  $model
     * @param  string $attribute
     * @param  Illuminate\Support\Collection $groups
     * @return string
     */
    public function set($model, $attribute, $groups)
    {
        return $model->$attribute = json_encode($groups->map(function($group) {
            return [
                'layout' => $group->name(),
                'key' => $group->key(),
                'attributes' => $group->getAttributes()
            ];
        }), JSON_PRETTY_PRINT);
    }

    /**
     * get the field's value
     *
     * @param  mixed  $resource
     * @param  string $attribute
     * @param  Illuminate\Support\Collection $layouts
     * @return Illuminate\Support\Collection
     */
    public function get($resource, $attribute, $layouts)
    {
        $value = $resource->$attribute ?? [];

        if(!is_array($value)) $value = json_decode($value);

        return collect($value)->map(function($item) use ($layouts) {
            $layout = $this->findLayout($item->layout, $layouts);

            if(!$layout) return;

            return $layout->duplicateAndHydrate($item->key, (array) $item->attributes);
        })->filter();
    }

    /**
     * Find a layout based on its name
     *
     * @param  string  $name
     * @param  Illuminate\Support\Collection $layouts
     * @return Whitecube\NovaFlexibleContent\Layouts\Layout
     */
    protected function findLayout($name, $layouts)
    {
        return $layouts->first(function($layout) use ($name) {
            return $layout->name() === $name;
        });
    }

}
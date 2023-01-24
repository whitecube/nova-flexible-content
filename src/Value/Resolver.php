<?php

namespace Whitecube\NovaFlexibleContent\Value;

use Illuminate\Support\Collection;

class Resolver implements ResolverInterface
{
    /**
     * Set the field's value
     *
     * @param  mixed  $model
     * @param  string  $attribute
     * @param  \Illuminate\Support\Collection  $value
     * @return string
     */
    public function set($model, $attribute, $value)
    {
        $groups = $value; // to avoid Named Arguments issue but keep using better var name;

        return $model->$attribute = $groups->map(function ($group) {
            return [
                'layout' => $group->name(),
                'key' => $group->key(),
                'attributes' => $group->getAttributes(),
            ];
        });
    }

    /**
     * get the field's value
     *
     * @param  mixed  $model
     * @param  string  $attribute
     * @param  \Whitecube\NovaFlexibleContent\Layouts\Collection  $layouts
     * @return \Illuminate\Support\Collection|null
     */
    public function get($model, $attribute, $layouts)
    {
        $resource = $model; // to avoid Named Arguments issue but keep using better var name;

        $value = $this->extractValueFromResource($resource, $attribute);

        return collect($value)->map(function ($item) use ($layouts) {
            $layout = $layouts->find($item->layout);

            if (! $layout) {
                return null;
            }

            return $layout->duplicateAndHydrate($item->key, (array) $item->attributes);
        })->filter()->values();
    }

    /**
     * Find the attribute's value in the given resource
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @return array
     */
    protected function extractValueFromResource($resource, $attribute)
    {
        $value = data_get($resource, str_replace('->', '.', $attribute)) ?? [];

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif (is_string($value)) {
            $value = json_decode($value) ?? [];
        }

        // Fail silently in case data is invalid
        if (! is_array($value)) {
            return [];
        }

        return array_map(function ($item) {
            return is_array($item) ? (object) $item : $item;
        }, $value);
    }
}

<?php

namespace Whitecube\NovaFlexibleContent\Value;

use Illuminate\Support\Collection;

class Resolver implements ResolverInterface
{
    /**
     * Save the Flexible field's content somewhere the get method will be able to access it.
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @param  \Illuminate\Support\Collection  $groups
     * @return string
     */
    public function set($resource, $attribute, $groups)
    {
        return $resource->$attribute = $groups->map(function ($group) {
            return [
                'layout' => $group->name(),
                'key' => $group->key(),
                'attributes' => $group->getAttributes(),
            ];
        });
    }

    /**
     * Resolve the Flexible field's content.
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @param  \Whitecube\NovaFlexibleContent\Layouts\Collection  $layouts
     * @return \Illuminate\Support\Collection<int, \Whitecube\NovaFlexibleContent\Layouts\Layout>
     */
    public function get($resource, $attribute, $layouts)
    {
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

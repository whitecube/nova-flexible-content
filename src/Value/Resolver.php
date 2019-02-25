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
     * @return void
     */
    public function set($model, $attribute, $groups)
    {
        $model->$attribute = json_encode($groups->map(function($group) {
            return [
                'layout' => $group->name(),
                'attributes' => $group->getAttributes()
            ];
        }), JSON_PRETTY_PRINT);
    }

    /**
     * get the field's value
     *
     * @param  mixed  $resource
     * @param  string $attribute
     * @return array
     */
    public function get($resource, $attribute)
    {
        $value = $resource->$attribute ?? [];

        if(is_string($value)) $value = json_decode($value);

        return $value;
    }

}
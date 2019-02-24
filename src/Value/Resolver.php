<?php

namespace Whitecube\NovaFlexibleContent\Value;

class Resolver implements ResolverInterface
{

    /**
     * Set the field's value
     *
     * @param Model $model
     * @param string $attribute
     * @param array $value
     * @return void
     */
    public function set($model, $attribute, $value)
    {
        $model->$attribute = json_encode($value);
    }

    /**
     * get the field's value
     *
     * @param Model $model
     * @param string $attribute
     * @return array
     */
    public function get($model, $attribute)
    {
        $value = $model->$attribute ?? [];

        if(is_string($value)) $value = json_decode($value);

        return $value;
    }

}
<?php

namespace Whitecube\NovaFlexibleContent\Collections;

use Laravel\Nova\Fields\FieldCollection as NovaFieldCollection;

class FieldCollection extends NovaFieldCollection
{
    /**
     * Find a given field by its attribute.
     *
     * @param  string  $attribute
     * @param  mixed  $default
     * @return \Laravel\Nova\Fields\Field|null
     */
    public function findFieldByAttribute($attribute, $default = null)
    {
        $array = explode("__", $attribute);
        if (isset($array[1])) {
            $attribute = $array[1];
        }
        return $this->first(function ($field) use ($attribute) {
            return isset($field->attribute) &&
                $field->attribute == $attribute;
        }, $default);
    }
}

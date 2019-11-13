<?php

namespace Whitecube\NovaFlexibleContent\Collections;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection as NovaFieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;

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
        if (strpos($attribute, '__') !== false) {
            $request = resolve(NovaRequest::class);
            $resource = $request->findResourceOrFail();
            $fields = $resource->updateFields($request);

            $attribute_parts = explode('__', $attribute, 2);

            $groups = [];
            foreach ($fields as $field) {
                if ($field instanceof Flexible) {
                    $groups = array_merge($groups, $this->flattenGroups($field));
                }
            }

            foreach ($groups as $group) {
                if ($group->inUseKey() !== $attribute_parts[0]) {
                    continue;
                }

                $field = $group->collectionFields()->first(function ($field) use ($attribute_parts, $group) {
                    $field->group = $group;

                    return isset($field->attribute) &&
                        $field->attribute == $attribute_parts[1];
                }, $default);

                return $this->addDeleteCallback($field);
            }
        }

        return $this->first(function ($field) use ($attribute) {
            return isset($field->attribute) &&
                $field->attribute == $attribute;
        }, $default);
    }

    /**
     * Flatten all groups into a single array
     *
     * @param  Field  $field
     * @param  $parentGroup
     * @return array
     */
    private function flattenGroups(Field $field, $parentGroup = null)
    {
        $flattened = [];
        foreach ($field->groups() as $i => $group) {
            $group->originalField = ($parentGroup ? $group->originalField . '.attributes.' : '') . $field->attribute;

            foreach ($group->fields() as $groupField) {
                if ($groupField instanceof Flexible) {
                    $flattened = array_merge($flattened, $this->flattenGroups($groupField, $group));
                }
            }

            $flattened[] = $group;
        }
        return $flattened;
    }

    /**
     * Add the delete callback helper
     *
     * @param Field|null $field
     * @return  Field
     */
    private function addDeleteCallback(?Field $field)
    {
        if (!$field || !isset($field->deleteCallback)) {
            return $field;
        }

        $callback = false;
        if (is_callable($field->deleteCallback)) {
            $callback = $field->deleteCallback;
        }

        $field->delete(function(NovaRequest $request, $model) use ($callback) {
            if ($callback && $callback->call($this, ...func_get_args())) {
                return true;
            }

            return \Whitecube\NovaFlexibleContent\deleteFile($request, $model, $this);
        });

        return $field;
    }
}

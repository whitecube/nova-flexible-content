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
     * @param string $attribute
     * @param mixed  $default
     *
     * @return null|\Laravel\Nova\Fields\Field
     */
    public function findFieldByAttribute($attribute, $default = null)
    {
        if (false !== strpos($attribute, '__')) {
            $request = resolve(NovaRequest::class);
            $resource = $request->findResourceOrFail();
            $fields = $resource->updateFields($request);

            $attribute_parts = explode('__', $attribute, 2);

            $groups = [];
            foreach ($fields as $i => $field) {
                if ($field instanceof Flexible) {
                    $field->index = $i;
                    $groups = array_merge($groups, $this->flattenGroups($field));
                }
            }

            foreach ($groups as $group) {
                if ($group->inUseKey() !== $attribute_parts[0]) {
                    continue;
                }

                $field = $group->collectionFields()->first(function ($field) use ($attribute_parts, $group) {
                    $field->group = $group;

                    return isset($field->attribute)
                        && $field->attribute == $attribute_parts[1];
                }, $default);

                return $this->addDeleteCallback($field);
            }
        }

        return $this->first(function ($field) use ($attribute) {
            return isset($field->attribute)
                && $field->attribute == $attribute;
        }, $default);
    }

    /**
     * Flatten all groups into a single array.
     *
     * @param $parentGroup
     *
     * @return array
     */
    private function flattenGroups(Field $field, $parentGroup = null)
    {
        if (!$field->groups()) {
            return [];
        }

        $flattened = [];

        foreach ($field->groups() as $groupIndex => $group) {
            $group->originalField = ($parentGroup ? $parentGroup->originalField.(isset($field->index) ? '.'.$field->index : '').'.attributes.' : '').$field->attribute.'.'.$groupIndex;

            foreach ($group->collectionFields() as $groupField) {
                if ($groupField instanceof Flexible) {
                    $flattened = array_merge($flattened, $this->flattenGroups($groupField, $group));
                }
            }

            $flattened[] = $group;
        }

        return $flattened;
    }

    /**
     * Add the delete callback helper.
     *
     * @return null|Field
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

        $field->delete(function (NovaRequest $request, $model) use ($callback, $field) {
            if ($callback && true === $callback->call($field, ...func_get_args())) {
                return true;
            }

            return Flexible::deleteFile($request, $model, $field);
        });

        return $field;
    }
}

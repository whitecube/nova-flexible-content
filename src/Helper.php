<?php

namespace Whitecube\NovaFlexibleContent;

use Illuminate\Support\Arr;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Correctly removes a file inside of a flexible layout
 *
 * @param  Laravel\Nova\Http\Requests\NovaRequest $request
 * @param  $model
 * @return boolean
 */
function deleteFile(NovaRequest $request, $model, $field)
{
    $path = explode('.', $field->group->originalField);
    $path[] = 'attributes';
    $path[] = $field->attribute;
    $mainField = array_shift($path);
    $data = $model->{$mainField};

    foreach ($data as $i => $group) {
        if ($field->group->inUseKey() !== $group['key']) {
            continue;
        }

        Arr::set($group, implode('.', $path), '');

        $data[$i] = $group;
        $model->{$mainField} = $data;
    }

    $model->save();
    return true;
}

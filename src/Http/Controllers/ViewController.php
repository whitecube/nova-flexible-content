<?php

namespace Whitecube\NovaFlexibleContent\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Http\Controllers\ActionController as NovaActionController;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Arr;

class ViewController extends NovaActionController
{
    public function __invoke(
        NovaRequest $request,
        $resource,
        $resourceId,
        $fieldName,
        $layoutName
    ) {
        // No idea if this is right
        $novaResource = $request->resource();
        $novaResource::authorizeToCreate($request);

        // Find the layout. Is there a better way?
        $layout = $request
            ->findResource($resourceId)
            ->resolveFieldForAttribute($request, urldecode($fieldName))
            ->meta["layouts"]->find($layoutName);

        // Not sure if there's a way of finding the layout that does this automatically.
        // Needed to give us access to $layout->model, e.g. in accessors.

        $model = $request->findModel($resourceId);
        $layout->setModel($model);

        $values = $request->except(["__key"]);

        // Set attributes on our layout
        foreach (
            $this->removeKeyPrefixFromFields($values, $request->__key)
            as $key => $value
        ) {
            if ($request->file($request->__key . "__" . $key)) {
                $field = $this->findFieldInLayout($key, $layout);

                if (get_class($field) == "Laravel\Nova\Fields\Image") {
                    $images = $field->storageCallback->__invoke(
                        $request,
                        $layout,
                        $key,
                        $request->__key . "__" . $key,
                        null,
                        "public/temporary_flexible_uploads/"
                    );

                    $values[$request->__key . "__" . $key] = $images;

                    $layout->setAttribute($key, $images);
                } else {
                    $layout->setAttribute($key, json_decode($value) ?? $value); // json_decode needed for simple repeater field
                }
            } elseif (is_array($value)) {
                //
                $layout->setAttribute(
                    $key,
                    json_decode(json_encode($value), false)
                );
                // $layout->setAttribute($key, $value);
            } else {
                $layout->setAttribute($key, json_decode($value) ?? $value); // json_decode needed for simple repeater field
            }
        }

        return response()->json([
            "view" => view("flexible." . $layoutName, [
                "layout" => (object) $layout,
            ])->render(),
            "data" => $values,
            "has_uploads" => !!count($request->files),
        ]);
    }

    private function removeKeyPrefixFromFields($array, $key)
    {
        $unprefixed_keys = str_replace($key . "__", "", array_keys($array));
        return array_combine($unprefixed_keys, $array);
    }

    private function findFieldInLayout($key, $layout)
    {
        return Arr::first($layout->fields(), function ($field) use ($key) {
            return $field->attribute == $key;
        });
    }
}

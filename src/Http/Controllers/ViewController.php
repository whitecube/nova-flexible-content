<?php

namespace Whitecube\NovaFlexibleContent\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Http\Controllers\ActionController as NovaActionController;
use Laravel\Nova\Http\Requests\NovaRequest;

class ViewController extends NovaActionController
{
    public function __invoke(
        NovaRequest $request,
        $resource,
        $resourceId,
        $fieldName,
        $layoutName
    ) {
        $layout = $request
            ->findResource($resourceId)
            ->resolveFieldForAttribute($request, urldecode($fieldName))
            ->meta['layouts']->find($layoutName);

        

        $values = $request->except(["__key"]);

        foreach ($request->files as $fieldName => $file) {
            $filename = $request->file($fieldName)->store("assets", "public");
            $values[$fieldName] = $filename;
        }

        return response()->json([
            'view' => view('flexible.' . $layoutName, $this->getDataForViewTemplate($layout, $values, $request->__key))->render(),
            'data' =>  $values,
            'has_uploads' => !!count($request->files)
        ]);
    }

    private function removeKeyPrefixFromFields($array, $key)
    {
        $unprefixed_keys = str_replace($key.'__', '', array_keys($array));
        return array_combine($unprefixed_keys, $array);
    }

    private function getDataForViewTemplate($layout, $values, $key) {
         $valuesWithoutKey = $this->removeKeyPrefixFromFields(
            $values,
            $key
        );

        $additionalPreviewdata = method_exists($layout, "previewData")
            ? $layout->previewData($valuesWithoutKey)
            : [];

        return array_merge($additionalPreviewdata, $valuesWithoutKey);
    }
}

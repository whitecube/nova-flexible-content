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

        // No idea if this is right
        $novaResource = $request->resource();
        $novaResource::authorizeToCreate($request);
        
        // Find the layout. Is there a better way?
        $layout = $request
            ->findResource($resourceId)
            ->resolveFieldForAttribute($request, urldecode($fieldName))
            ->meta['layouts']->find($layoutName);

        // Not sure if there's a way of finding the layout that does this automatically. 
        // Needed to give us access to $layout->model, e.g. in accessors.
        $layout->setModel($request->findModel($resourceId));

        $values = $request->except(["__key"]);

        // Upload all files. This is very rough at the minute.
        // At a minimum need to think about deletion
        foreach ($request->files as $fieldName => $file) {
            $filename = $request->file($fieldName)->store("flexible_preview_temporary_uploads", "public");
            $values[$fieldName] = $filename;
        }

        // Set attributes on our layout
        foreach($this->removeKeyPrefixFromFields($values, $request->__key) as $key => $value) {
            $layout->setAttribute($key, json_decode($value) ?? $value); // json_decode needed for simple repeater field
        }
        
        return response()->json([
            'view' => view('flexible.' . $layoutName, ['layout' => $layout])->render(),
            'data' =>  $values,
            'has_uploads' => !!count($request->files)
        ]);

    }

    private function removeKeyPrefixFromFields($array, $key)
    {
        $unprefixed_keys = str_replace($key.'__', '', array_keys($array));
        return array_combine($unprefixed_keys, $array);
    }
}

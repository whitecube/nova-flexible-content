<?php

namespace Whitecube\NovaFlexibleContent\Http\Controllers;

use Laravel\Nova\Http\Requests\ResourceUpdateOrUpdateAttachedRequest;
use Laravel\Nova\Http\Resources\UpdateViewResource;
use Laravel\Nova\Http\Controllers\UpdateFieldController as NovaUpdateFieldController;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

/**
 * This is the patched UpdateFieldController to make dependent fields work within
 * a flexible content container. Until Laravel nova offers a solution to resolve fields
 * natively, this is the only plausible work-around.
 */
class UpdateFieldController extends NovaUpdateFieldController {

    /**
     * @param ResourceUpdateOrUpdateAttachedRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sync(ResourceUpdateOrUpdateAttachedRequest $request)
    {
        // we only act on flexible fields concerning this package
        if(! FlexibleAttribute::hasFlexibleGeneratedPart($request->query('field'))) {
            // return native response
            return parent::sync($request);
        }

        $resource = UpdateViewResource::make()->newResourceWith($request);

        return response()->json($resource->updateFields($request)
            // we first need to extract all fields from a given group/layout
            ->map(function($field) use ($resource) {
                if($field instanceof Flexible) {
                    $resolved = $field->jsonSerialize()['layouts']->map(function($layout) {
                        return $layout->fields();
                    })->flatten();

                    return $resolved;
                }
                return $field;
            })
            // we need to flatten the collection with all nested collections given previously
            ->flatten()
            // here is everything as usual
            ->filter(function ($field) use ($request) {
                return $request->input('field') === $field->attribute &&
                    $request->input('component') === $field->dependentComponentKey();
            })->each->syncDependsOn($request)->first());

    }

}
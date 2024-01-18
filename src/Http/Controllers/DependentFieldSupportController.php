<?php

namespace Whitecube\NovaFlexibleContent\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\ResourceCreateOrAttachRequest;
use Laravel\Nova\Http\Requests\ResourceUpdateOrUpdateAttachedRequest;
use Laravel\Nova\Http\Resources\CreateViewResource;
use Laravel\Nova\Http\Resources\ReplicateViewResource;
use Laravel\Nova\Http\Resources\UpdateViewResource;
use Laravel\Nova\Http\Controllers\UpdateFieldController;
use Laravel\Nova\Http\Controllers\CreationFieldSyncController;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

use Illuminate\Support\Facades\App;

/**
 * Controller collecting methods for supporting dependent fields in update and creation mode.
 */
class DependentFieldSupportController extends Controller {

    /**
     * @param ResourceUpdateOrUpdateAttachedRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateFieldSync(ResourceUpdateOrUpdateAttachedRequest $request)
    {
        // we only act on flexible fields concerning this package
        if(! FlexibleAttribute::hasFlexibleGeneratedPart($request->query('field'))) {
            // return native response
            return App::makeWith(UpdateFieldController::class, [$request]);
        }

        $resource = UpdateViewResource::make()->newResourceWith($request);

        return response()->json(
            $this->findFlexibleField($request, $resource)
        );

    }

    /**
     * Synchronize the field for creation view.
     *
     * @param  \Laravel\Nova\Http\Requests\ResourceCreateOrAttachRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function creationFieldSync(ResourceCreateOrAttachRequest $request)
    {
        // we only act on flexible fields concerning this package
        if(! FlexibleAttribute::hasFlexibleGeneratedPart($request->query('field'))) {
            // return native response (using __invoke)
            return App::call(CreationFieldSyncController::class, [$request]);
        }

        $resource = $request->has('fromResourceId')
            ? ReplicateViewResource::make($request->fromResourceId)->newResourceWith($request)
            : CreateViewResource::make()->newResourceWith($request);

        return response()->json(
            $this->findFlexibleField($request, $resource)
        );
    }

    protected function findFlexibleField($request, $resource) {
        return $resource->creationFields($request)
            ->map(function($field) use ($resource) {
                if($field instanceof Flexible) {
                    $resolved = $field->jsonSerialize()['layouts']->map(function($layout) {
                        return $layout->fields();
                    })->flatten();

                    return $resolved;
                }
                return $field;
            })
            ->flatten()
            ->filter(function ($field) use ($request) {
                return $request->input('field') === $field->attribute &&
                    $request->input('component') === $field->dependentComponentKey();
            })->each->syncDependsOn($request)
            ->first();
    }

}
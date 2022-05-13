<?php

use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;

trait HasFlexibleDependsOn {

    public function updateFields(NovaRequest $request)
    {
        $fields = $this->resolveFields($request, function ($fields) use ($request) {
            return $this->removeNonUpdateFields($request, $fields);
        });

        if ($request->getMethod() === "PATCH") {
            $fields = $this->flattenFields($fields);
        }

        return $fields->each->applyDependsOn($request);
    }

    public function creationFields(NovaRequest $request)
    {
        $fields = $this->removeNonCreationFields(
            $request,
            $this->availableFields($request)->authorized($request)
        )->resolve($this->resource);
        
        if ($request->getMethod() === "PATCH") {
            $fields = $this->flattenFields($fields);
        }

        return tap(
            $request->viaRelationship()
                ? $this->withPivotFields($request, $fields->all())
                : $fields, function ($fields) use ($request) {
                    $fields->each->applyDependsOn($request);
                });
    }

    protected function flattenFields ($fields) {
        $fields->each(function($item, $key) use (&$fields) {
            if ($item instanceof Flexible) {
                $fields->forget($key);
                $item->meta['layouts']->each(function($layout) use (&$fields) {
                    $fields = $fields->merge($layout->fields());
                });
            }
        });
        return $fields;
    }

}
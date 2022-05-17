<?php

namespace Whitecube\NovaFlexibleContent\Concerns;

use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;

trait HasFlexibleDependsOn {

    public function updateFields(NovaRequest $request) {
        $fields = parent::updateFields($request);
        if ($request->getMethod() === "PATCH") {
            $fields = $this->flattenFields($fields);
        }
        return $fields;
    }

    public function creationFields(NovaRequest $request) {
        $fields = parent::creationFields($request);
        if ($request->getMethod() === "PATCH") {
            $fields = $this->flattenFields($fields);
        }
        return $fields;
    }

    protected function flattenFields($fields) {
        if ($fields instanceof FieldCollection) {
            $fields->each(function ($item, $key) use (&$fields) {
                if ($item instanceof Flexible) {
                    $fields->forget($key);
                    $item->meta['layouts']->each(function ($layout) use (&$fields) {
                        $fields = $fields->merge($this->flattenFields(new FieldCollection($layout->fields())));
                    });
                }
            });
        }
        return $fields;
    }
}

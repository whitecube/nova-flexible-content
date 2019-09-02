<?php

namespace Whitecube\NovaFlexibleContent\Traits;

use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Collections\FieldCollection;
use Whitecube\NovaFlexibleContent\Flexible;

trait AvailableFields
{

    /**
     * Get the fields that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function availableFields(NovaRequest $request)
    {
        if ($request->method() === "DELETE") {
            $fields = $this->fields($request);
            $flattened = [];

            foreach ($fields as $field) {
                if ($field instanceof Flexible) {
                    $flattened = array_merge($flattened, $this->flattenFlexibleField($field));
                } else {
                    $flattened[] = $field;
                }
            }
        } else {
            $flattened = $this->fields($request);
        }

        return new FieldCollection(array_values($this->filter($flattened)));
    }

    /**
     * Flatten the fields
     *
     * @param Flexible $field
     * @return array
     */
    private function flattenFlexibleField($field)
    {
        $flattened = [];
        foreach ($field->layouts() as $layout) {
            foreach ($layout->fields() as $field) {
                if ($field instanceof Flexible) {
                    $flattened = array_merge($flattened, $this->flattenFlexibleField($field));
                } else {
                    $flattened[] = $field;
                }
            }
        }

        return $flattened;
    }
}

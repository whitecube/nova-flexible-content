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
        return new FieldCollection(array_values($this->filter($this->fields($request))));
    }
}

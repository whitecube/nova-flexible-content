<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Laravel\Nova\Http\Requests\NovaRequest;

interface LayoutInterface
{
    public function name();
    public function title();
    public function fields();
    public function getFilled(NovaRequest $request, $attributes);
}
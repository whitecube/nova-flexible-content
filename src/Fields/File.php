<?php

namespace Whitecube\NovaFlexibleContent\Fields;

use Laravel\Nova\Fields\File as NovaFile;
use Laravel\Nova\Http\Requests\NovaRequest;

class File extends NovaFile
{
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->delete(function(NovaRequest $request, $model) {
            return \Whitecube\NovaFlexibleContent\deleteFile($request, $model, $this);
        });

    }
}
<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Http\ScopedRequest;

interface LayoutInterface
{
    public function name();
    public function title();
    public function fields();
    public function key();
    public function getResolved(array $attributes, $key);
    public function getFilled(ScopedRequest $request, $key);
    public function resolvedValue();
}
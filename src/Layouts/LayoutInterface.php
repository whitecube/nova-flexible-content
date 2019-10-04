<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Http\ScopedRequest;

interface LayoutInterface
{
    public function name();
    public function title();
    public function fields();
    public function key();
    public function getResolved();
    public function resolve($empty = false);
    public function fill(ScopedRequest $request);
    public function duplicate($key);
    public function duplicateAndHydrate($key, array $attributes = []);
}

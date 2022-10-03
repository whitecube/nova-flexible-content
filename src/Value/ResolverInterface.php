<?php

namespace Whitecube\NovaFlexibleContent\Value;

interface ResolverInterface
{
    public function set($model, $attribute, $value);

    public function get($model, $attribute, $layouts);
}

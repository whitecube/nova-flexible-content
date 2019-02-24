<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

interface LayoutInterface
{
    public function name();
    public function title();
    public function fields();
}
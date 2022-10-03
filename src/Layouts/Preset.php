<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;

abstract class Preset
{
    /**
     * Execute the preset configuration
     *
     * @return void
     */
    abstract public function handle(Flexible $field);
}

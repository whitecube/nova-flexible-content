<?php

namespace Dwarfhq\NovaFlexibleContent\Layouts;

use Dwarfhq\NovaFlexibleContent\Flexible;

abstract class Preset
{
    /**
     * Execute the preset configuration
     *
     * @return void
     */
    abstract public function handle(Flexible $field);
}

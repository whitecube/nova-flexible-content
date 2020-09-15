<?php

namespace Kraenkvisuell\NovaFlexibleContent\Layouts;

use Kraenkvisuell\NovaFlexibleContent\Flexible;

abstract class Preset
{
    /**
     * Execute the preset configuration
     *
     * @return void
     */
    abstract public function handle(Flexible $field);

}

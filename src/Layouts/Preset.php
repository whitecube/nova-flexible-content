<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Layouts;

use Wmt\NovaFlexibleContent\Flexible;

abstract class Preset
{
    /**
     * Execute the preset configuration
     *
     * @return void
     */
    abstract public function handle(Flexible $field);
}

<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Layouts;

use Wmt\NovaFlexibleContent\Http\ScopedRequest;

interface LayoutInterface
{
    public function name();

    public function title();

    public function fields();

    public function key();

    /**
     * @return bool
     */
    public function show();

    public function getResolved();

    public function resolve($empty = false);

    public function fill(ScopedRequest $request);

    public function duplicate($key);

    public function duplicateAndHydrate($key, array $attributes, bool $show);
}

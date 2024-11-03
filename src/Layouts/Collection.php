<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Layouts;

use Illuminate\Support\Collection as BaseCollection;

/**
 * @template TKey of array-key
 * @template TLayout of \Wmt\NovaFlexibleContent\Layouts\Layout
 *
 * @extends \Illuminate\Support\Collection<TKey, TLayout>
 */
class Collection extends BaseCollection
{
    /**
     * Find a layout based on its name
     *
     * @param string $name
     * @return \Wmt\NovaFlexibleContent\Layouts\Layout|null
     *
     * @psalm-return TLayout|null
     */
    public function find($name)
    {
        return $this->first(function ($layout) use ($name) {
            return $layout->name() === $name;
        });
    }
}

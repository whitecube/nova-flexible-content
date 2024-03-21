<?php

namespace Whitecube\NovaFlexibleContent\Value;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

/**
 * @template TGet of string
 * @template TSet of \Whitecube\NovaFlexibleContent\Layouts\Layout
 * @implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes<TGet, TSet>
 */
class FlexibleCast implements CastsAttributes
{
    use HasFlexible;

    /**
     * @var array<string, class-string<\Whitecube\NovaFlexibleContent\Layouts\LayoutInterface>>
     */
    protected $layouts = [];

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @return \Whitecube\NovaFlexibleContent\Layouts\Collection|array<\Whitecube\NovaFlexibleContent\Layouts\Layout>
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $this->model = $model;

        return $this->cast($value, $this->getLayoutMapping());
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    protected function getLayoutMapping()
    {
        return $this->layouts;
    }
}

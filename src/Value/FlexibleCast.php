<?php

namespace Whitecube\NovaFlexibleContent\Value;

use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class FlexibleCast implements CastsAttributes
{
    use HasFlexible;

    /**
     * @var array
     */
    protected $layouts = [];

    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

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

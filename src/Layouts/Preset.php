<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;

abstract class Preset
{

    /**
     * The flexible field that will be configured
     *
     * @var Whitecube\NovaFlexibleContent\Flexible
     */
    protected $field;

    /**
     * Create a new preset instance
     *
     * @param Whitecube\NovaFlexibleContent\Flexible $field
     * @return void
     */
    public function __construct(Flexible $field)
    {
        $this->field = $field;
    }

    /**
     * Execute the preset configuration
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Forward method calls to the field
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->field, $method], $parameters);
    }

    /**
     * Retrieve an attribute on the field
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->field->$attribute;
    }

    /**
     * Define an attribute on the field
     *
     * @param string $attribute
     * @param mixed $value
     * @return void
     */
    public function __set($attribute, $value)
    {
        return $this->field->$attribute = $value;
    }
}

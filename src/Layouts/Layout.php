<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use JsonSerializable;

class Layout implements LayoutInterface, JsonSerializable
{
    /**
     * The layout's identifier
     *
     * @var string
     */
    protected $name;

    /**
     * The layout's title
     *
     * @var string
     */
    protected $title;

    /**
     * The layout's registered fields
     *
     * @var array
     */
    protected $fields;

    /**
     * Create a new base Layout instance
     *
     * @param string $title
     * @param string $name
     * @param string $fields
     * @return void
     */
    public function __construct($title, $name, $fields)
    {
        $this->title = $title;
        $this->name = $name;
        $this->fields = $fields;
    }

    /**
     * Retrieve the layout's name (identifier)
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Retrieve the layout's title
     *
     * @return string
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Retrieve the layout's fields
     *
     * @return array
     */
    public function fields()
    {
        return $this->fields ?? [];
    }

    /**
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name(),
            'title' => $this->title()
        ];
    }
}

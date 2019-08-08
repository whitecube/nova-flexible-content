<?php

namespace Whitecube\NovaFlexibleContent\Http;

class FlexibleAttribute
{
    /**
     * The Register attribute
     *
     * @var string
     */
    const REGISTER = '_nova_flexible_content_fields';

    /**
     * The string between the group identifier 
     * and the actual attribute.
     *
     * @var string
     */
    const GROUP_SEPARATOR = '__';

    /**
     * The string that identifies an "upload" value
     *
     * @var string
     */
    const FILE_INDICATOR = '___upload-';

    /**
     * The original attribute name
     *
     * @var string
     */
    public $original;

    /**
     * The layout group identifier part
     *
     * @var string
     */
    public $group;

    /**
     * The layout group identifier part
     *
     * @var string
     */
    public $name;

    /**
     * The aggregate key (true = increment)
     *
     * @var bool|string
     */
    public $key;

    /**
     * Create a new attribute instance
     *
     * @param  string $original
     * @param  mixed $group
     * @return void
     */
    public function __construct($original, $group = null)
    {
        $this->original = $original;
        $this->setGroup($group);
        $this->setKey();
        $this->setName();
    }

    /**
     * Build an attribute from its components
     *
     * @param  string $name
     * @param  string $group
     * @param  mixed $key
     * @return \Whitecube\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public static function make($name, $group = null, $key = null)
    {
        $original = $group ? $group . static::GROUP_SEPARATOR : '';
        $original .= $name;
        $original .= $key ? '[' . ($key !== true ? $key : '') . ']' : '';

        return new static($original, $group);
    }

    /**
     * Check if attribute is a flexible fields register
     *
     * @return bool
     */
    public function isFlexibleFieldsRegister()
    {
        return $this->name === static::REGISTER;
    }

    /**
     * Check if attribute and value match a probable file
     *
     * @param mixed $value
     * @return bool
     */
    public function isFlexibleFile($value)
    {
        if(!$value || !is_string($value)) {
            return false;
        }

        return $value === static::FILE_INDICATOR . $this->original;
    }

    /**
     * Check if attribute represents an array item
     *
     * @return bool
     */
    public function isAggregate()
    {
        return !is_null($this->key);
    }

    /**
     * Check if given group identifier is included in original
     * attribute. If so, set it as the group property.
     *
     * @param  mixed $group
     * @return void
     */
    protected function setGroup($group = null)
    {
        if(!$group) {
            return;
        }

        $group = strval($group);

        if(strpos($this->original, $group . static::GROUP_SEPARATOR) !== false) {
            $this->group = $group;
        }
    }

    /**
     * Check if the original attribute contains an aggregate syntax.
     * If so, extract the aggregate key and assign it to the key property.
     *
     * @return void
     */
    protected function setKey()
    {
        preg_match('/^.+?(\[.*\])?$/', $this->original, $matches);

        if(!isset($matches[1])) return;

        $key = trim($matches[1], "[]'\" \t\n\r\0\x0B");

        $this->key = strlen($key) ? $key : true;
    }

    /**
     * Extract the attribute's final name
     *
     * @return void
     */
    protected function setName()
    {
        $name = trim($this->original);

        if($this->group) {
            $position = strpos($name, $this->group) + strlen($this->group . static::GROUP_SEPARATOR);
            $name = substr($name, $position);
        }

        if($this->isAggregate()) {
            $position = strpos($name, '[');
            $name = substr($name, 0, $position);
        }

        $this->name = $name;
    }
}

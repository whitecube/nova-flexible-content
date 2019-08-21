<?php

namespace Whitecube\NovaFlexibleContent\Http;

class FlexibleAttribute
{
    /**
     * The Register attribute
     *
     * @var string
     */
    const REGISTER = '___nova_flexible_content_fields';

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
        $original = static::formatGroupPrefix($group) ?? '';
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

        return strpos($value, static::FILE_INDICATOR) === 0;
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
     * Check if the found group key is used in the attribute's name
     *
     * @return bool
     */
    public function hasGroupInName()
    {
        return !is_null($this->group) && strpos($this->original, $this->groupPrefix()) === 0;
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

        if(strpos($this->original, $this->groupPrefix($group)) !== false) {
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
        preg_match('/^.+?(\[.*\])?$/', $this->original, $arrayMatches);

        if(!isset($arrayMatches[1])) return;

        preg_match_all('/(?:\[([^\[\]]*)\])+?/', $arrayMatches[1], $keyMatches);

        $key = implode('.', array_map(function($segment) {
            return $this->getCleanKeySegment($segment);
        }, $keyMatches[1]));

        $this->key = strlen($key) ? $key : true;
    }

    /**
     * Formats a key segment (removes unwanted characters, removes
     * group references from).
     *
     * @param string $segment
     * @return string
     */
    protected function getCleanKeySegment($segment)
    {
        $segment = trim($segment, "'\" \t\n\r\0\x0B");

        if($this->group && strpos($segment, $this->groupPrefix()) === 0) {
            return (new static($segment, $this->group))->name;
        }

        return $segment;
    }

    /**
     * Extract the attribute's final name
     *
     * @return void
     */
    protected function setName()
    {
        $name = trim($this->original);

        if($this->hasGroupInName()) {
            $position = strpos($name, $this->group) + strlen($this->groupPrefix());
            $name = substr($name, $position);
        }

        if($this->isAggregate()) {
            $position = strpos($name, '[');
            $name = substr($name, 0, $position);
        }

        $this->name = $name;
    }

    /**
     * Get the group prefix string
     *
     * @param string $group
     * @return null|string
     */
    public function groupPrefix($group = null)
    {
        return static::formatGroupPrefix($group ?? $this->group);
    }

    /**
     * Get a group prefix string
     *
     * @param string $group
     * @return null|string
     */
    static public function formatGroupPrefix($group)
    {
        if(!$group) {
            return;
        }

        return $group . static::GROUP_SEPARATOR;
    }
}

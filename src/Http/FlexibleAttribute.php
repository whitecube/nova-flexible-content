<?php

namespace Whitecube\NovaFlexibleContent\Http;

use Illuminate\Support\Arr;

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
     * Whether the attribute begins with the file indicator
     *
     * @var bool
     */
    public $upload;

    /**
     * Create a new attribute instance
     *
     * @param  string  $original
     * @param  mixed  $group
     * @return void
     */
    public function __construct($original, $group = null)
    {
        $this->original = $original;
        $this->setUpload();
        $this->setGroup($group);
        $this->setKey();
        $this->setName();
    }

    /**
     * Build an attribute from its components
     *
     * @param  string  $name
     * @param  string  $group
     * @param  mixed  $key
     * @param  bool  $upload
     * @return \Whitecube\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public static function make($name, $group = null, $key = null, $upload = false)
    {
        $original = $upload ? static::FILE_INDICATOR : '';
        $original .= static::formatGroupPrefix($group) ?? '';
        $original .= $name;
        $original .= $key ? '['.($key !== true ? $key : '').']' : '';

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
     * Check if attribute or given value match a probable file
     *
     * @param  mixed  $value
     * @return bool
     */
    public function isFlexibleFile($value = null)
    {
        if (! is_null($value) && ! is_string($value)) {
            return false;
        } elseif (is_null($value)) {
            return $this->upload;
        }

        return strpos($value, static::FILE_INDICATOR) === 0;
    }

    /**
     * Return a FlexibleAttribute instance matching the target upload field
     *
     * @param  mixed  $value
     * @return \Whitecube\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public function getFlexibleFileAttribute($value)
    {
        return new static($value, $this->group);
    }

    /**
     * Check if attribute represents an array item
     *
     * @return bool
     */
    public function isAggregate()
    {
        return ! is_null($this->key);
    }

    /**
     * Check if the found group key is used in the attribute's name
     *
     * @return bool
     */
    public function hasGroupInName()
    {
        if (is_null($this->group)) {
            return false;
        }

        $position = strpos($this->original, $this->groupPrefix());
        $index = $this->isFlexibleFile() ? strlen(static::FILE_INDICATOR) : 0;

        return $position === $index;
    }

    /**
     * Get the group prefix string
     *
     * @param  string  $group
     * @return null|string
     */
    public function groupPrefix($group = null)
    {
        return static::formatGroupPrefix($group ?? $this->group);
    }

    /**
     * Get a group prefix string
     *
     * @param  string  $group
     * @return null|string
     */
    public static function formatGroupPrefix($group)
    {
        if (! $group) {
            return;
        }

        return $group.static::GROUP_SEPARATOR;
    }

    /**
     * Set given value in given using the current attribute definition
     *
     * @param  array  $attributes
     * @param  string  $value
     * @return array
     */
    public function setDataIn(&$attributes, $value)
    {
        $value = is_string($value) && $value === '' ? null : $value;

        if (! $this->isAggregate()) {
            $attributes[$this->name] = $value;

            return $attributes;
        }

        if (! isset($attributes[$this->name])) {
            $attributes[$this->name] = [];
        } elseif (! is_array($attributes[$this->name])) {
            $attributes[$this->name] = [$attributes[$this->name]];
        }

        if ($this->key === true) {
            $attributes[$this->name][] = $value;
        } else {
            data_set($attributes[$this->name], $this->key, $value);
        }

        return $attributes;
    }

    /**
     * Remove current attribute from given array
     *
     * @param  array  $attributes
     * @return array
     */
    public function unsetDataIn(&$attributes)
    {
        if (! $this->isAggregate() || ! is_array($attributes[$this->name])) {
            unset($attributes[$this->name]);

            return $attributes;
        }

        if ($this->key === true) {
            array_shift($attributes[$this->name]);
        } else {
            Arr::forget($attributes[$this->name], $this->key);
        }

        return $attributes;
    }

    /**
     * Return a new instance with appended key
     *
     * @param  string  $key
     * @return \Whitecube\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public function nest($key)
    {
        $append = implode('', array_map(function ($segment) {
            return '['.$segment.']';
        }, explode('.', $key)));

        return new static($this->original.$append, $this->group);
    }

    /**
     * Check attribute is an "upload" attribute and define it on the object
     *
     * @param  mixed  $group
     * @return void
     */
    protected function setUpload()
    {
        $this->upload = $this->isFlexibleFile($this->original);
    }

    /**
     * Check if given group identifier is included in original
     * attribute. If so, set it as the group property.
     *
     * @param  mixed  $group
     * @return void
     */
    protected function setGroup($group = null)
    {
        if (! $group) {
            return;
        }

        $group = strval($group);

        if (strpos($this->original, $this->groupPrefix($group)) !== false) {
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

        if (! isset($arrayMatches[1])) {
            return;
        }

        preg_match_all('/(?:\[([^\[\]]*)\])+?/', $arrayMatches[1], $keyMatches);

        $key = implode('.', array_map(function ($segment) {
            return $this->getCleanKeySegment($segment);
        }, $keyMatches[1]));

        $this->key = strlen($key) ? $key : true;
    }

    /**
     * Formats a key segment (removes unwanted characters, removes
     * group references from).
     *
     * @param  string  $segment
     * @return string
     */
    protected function getCleanKeySegment($segment)
    {
        $segment = trim($segment, "'\" \t\n\r\0\x0B");

        if ($this->group && strpos($segment, $this->groupPrefix()) === 0) {
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

        if ($this->isFlexibleFile()) {
            $position = strpos($name, static::FILE_INDICATOR) + strlen(static::FILE_INDICATOR);
            $name = substr($name, $position);
        }

        if ($this->hasGroupInName()) {
            $position = strpos($name, $this->group) + strlen($this->groupPrefix());
            $name = substr($name, $position);
        }

        if ($this->isAggregate()) {
            $position = strpos($name, '[');
            $name = substr($name, 0, $position);
        }

        $this->name = $name;
    }
}

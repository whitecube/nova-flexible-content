<?php

namespace Whitecube\NovaFlexibleContent\Http;

use Laravel\Nova\Http\Requests\NovaRequest;

class ScopedRequest extends NovaRequest
{

    /**
     * Create a copy of the given request, only containing the group's input
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $from
     * @param  array  $attributes
     * @param  string  $key
     * @return Whitecube\NovaFlexibleContent\Http\ScopedRequest
     */
    public static function scopeFrom(NovaRequest $from, $attributes, $key)
    {
        return parent::createFrom($from)->scopeInto($key, $attributes);
    }

    /**
     * Alter the request's input for given group key & attributes
     *
     * @param  string  $key
     * @param  array  $attributes
     * @return $this
     */
    public function scopeInto($key, $attributes)
    {
        $scope = $this->getScopeState($key, $attributes);
        
        $scope['input']['_method'] = $this->input('_method');
        $scope['input']['_retrieved_at'] = $this->input('_retrieved_at');

        $this->replace($scope['input']);

        $this->files->replace($this->getScopeFiles(
            $scope['files'],
            $scope['keep']
        ));

        return $this;
    }

    /**
     * Get the target scope configuration array
     *
     * @param  string  $key
     * @param  array  $attributes
     * @return array
     */
    protected function getScopeState($key, $attributes)
    {
        $scope = [
            'input' => [],
            'files' => [],
            'keep' => []
        ];

        foreach ($attributes as $originalAttribute => $value) {
            $attribute = $this->parseAttribute($originalAttribute, $key);
            $value = $this->parseValue($value);

            // Sub-objects should remain JSON strings for further fields resolving
            if(is_array($value) || is_object($value)) {
                $scope['keep'] = array_merge($scope['keep'], $this->getNestedFiles($value));
                $scope['input'] = $this->addAttributeToArray($scope['input'], $attribute, json_encode($value));
                continue;
            }

            // Register Files
            if($this->isFlexibleFileAttribute($originalAttribute, $value)) {
                $scope['files'] = $this->addAttributeToArray($scope['files'], $attribute, $value);
                continue;
            }

            // Register regular attributes
            $scope['input'] = $this->addAttributeToArray($scope['input'], $attribute, $value);
        }

        return $scope;
    }

    /**
     * Add a parsed attribute and its value to an array
     *
     * @param  array  $array
     * @param  string $attribute
     * @param  mixed  $value
     * @return array
     */
    protected function addAttributeToArray($array, $attribute, $value)
    {
        if(!$attribute['key']) {
            // It is a simple array attribute, we can just add its value
            // to the array without nesting it in another array.
            $array[$attribute['name']] = $value;

            return $array;
        }

        if(!is_array($array[$attribute['name']] ?? null)) {
            // It is a nested array attribute and there are no other
            // sibling values yet, so we have to create the nested array first.
            $array[$attribute['name']] = [];
        }

        if($attribute['key'] === true) {
            // The nested attribute doesn't have a particular key, meaning
            // we can just append its value to the nested array.
            $array[$attribute['name']][] = $value;

            return $array;
        }

        // The nested attribute has a defined key, we'll register its
        // value in the array using its key.
        $array[$attribute['name']][$attribute['key']] = $value;

        return $array;
    }

    /**
     * Analyse and clean up the raw attribute
     *
     * @param  string  $attribute
     * @param  string  $key
     * @return array
     */
    protected function parseAttribute($attribute, $key)
    {
        $analysis = [];
        $analysis['original'] = $attribute;
        $analysis['raw'] = $this->getAttributeRawKey($attribute, $key);
        $analysis['key'] = $this->getAttributeAggregateKey($analysis['raw']);
        $analysis['name'] = $analysis['key'] ? $this->getAttributeAggregateName($analysis['raw']) : $analysis['raw'];
        return $analysis;
    }

    /**
     * Retrieves original field name from layout attribute name
     *
     * @param string $attribute
     * @param string $key
     * @return string
     */
    protected function getAttributeRawKey($attribute, $key)
    {
        $position = strpos($attribute, strval($key)) + strlen($key . '__');
        return substr($attribute, $position);
    }

    /**
     * Look for an aggregate syntax in an attribute name and return its key
     * or true if its an incrementing numeric key
     *
     * @param  string  $attribute
     * @return mixed
     */
    protected function getAttributeAggregateKey($attribute)
    {
        preg_match('/^.+?(\[.*\])?$/', $attribute, $matches);

        if(!isset($matches[1])) return false;

        $key = trim($matches[1], "[]'\" \t\n\r\0\x0B");

        return strlen($key) ? $key : true;
    }

    /**
     * Look for an aggregate syntax in an attribute name and return its key
     * or true if its an incrementing numeric key
     *
     * @param  string  $attribute
     * @return mixed
     */
    protected function getAttributeAggregateName($attribute)
    {
        $end = strpos($attribute, '[');

        if($end === false) return $attribute;

        return substr($attribute, 0, $end);
    }


    /**
     * Transforms JSON strings to objects if needed
     *
     * @param mixed $value
     * @return mixed
     */
    protected function parseValue($value)
    {
        if(!is_string($value)) {
            return $value;
        }

        $parsed = json_decode($value);

        if(!is_array($parsed) && !is_object($parsed)) {
            return $value;
        }

        return $parsed;
    }

    /**
     * Get nested file attributes
     *
     * @param  string  $key
     * @param  array  $attributes
     * @return array
     */
    protected function getNestedFiles($iterable)
    {
        $files = [];

        foreach ($iterable as $attribute => $value) {
            if(is_array($value) || is_object($value)) {
                $files = array_merge($files, $this->getNestedFiles($value));
                continue;
            }

            if(!$this->isFlexibleFileAttribute($attribute, $value)) continue;

            $files[] = $value;
        }

        return $files;
    }

    /**
     * Check if the given key/value pair represents a file upload
     *
     * @param  string  $key
     * @param  array  $attributes
     * @return array
     */
    protected function isFlexibleFileAttribute($attribute, $value)
    {
        return ($value === '___upload-' . $attribute);
    }

    /**
     * Get all useful files from current files list
     *
     * @param  array  $rename
     * @param  array  $keep
     * @return array
     */
    protected function getScopeFiles($rename, $keep)
    {
        $files = [];

        foreach($this->files->all() as $attribute => $file) {
            if(in_array($attribute, $rename)) $files[array_search($attribute, $rename)] = $file;
            else if(in_array($attribute, $keep)) $files[$attribute] = $file;
        }

        return $files;
    }

}

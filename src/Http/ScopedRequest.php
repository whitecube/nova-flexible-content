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
     * @return this
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
            $attribute = $this->getCleanAttributeName($originalAttribute, $key);

            // Sub-objects should remain JSON strings for further fields resolving
            if(is_array($value) || is_object($value)) {
                $scope['keep'] = array_merge($scope['keep'], $this->getNestedFiles($value));
                $scope['input'][$attribute] = json_encode($value);
                continue;
            }

            // Register Files
            if($this->isFlexibleFileAttribute($originalAttribute, $value)) {
                $scope['files'][$attribute] = $value;
                continue;
            }

            // Register regular attributes
            $scope['input'][$attribute] = $value;
        }

        return $scope;
    }

    /**
     * Remove the prepended random key from the attribute name
     *
     * @param  string  $attribute
     * @param  string  $key
     * @return string
     */
    protected function getCleanAttributeName($attribute, $key)
    {
        return substr($attribute, strpos($attribute, $key) + strlen($key . '__'));
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
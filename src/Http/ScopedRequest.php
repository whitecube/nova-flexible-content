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
     * @param  string  $group
     * @return Whitecube\NovaFlexibleContent\Http\ScopedRequest
     */
    public static function scopeFrom(NovaRequest $from, $attributes, $group)
    {
        return parent::createFrom($from)->scopeInto($group, $attributes);
    }

    /**
     * Alter the request's input for given group key & attributes
     *
     * @param  string  $group
     * @param  array  $attributes
     * @return $this
     */
    public function scopeInto($group, $attributes)
    {
        $scope = $this->getScopeState($group, $attributes);
        
        $scope['input']['_method'] = $this->input('_method');
        $scope['input']['_retrieved_at'] = $this->input('_retrieved_at');

        $this->replace($scope['input']);

        $this->files->replace(
            $this->getScopeFiles($scope['files'], $scope['keep'])
        );

        return $this;
    }

    /**
     * Get the target scope configuration array
     *
     * @param  string  $group
     * @param  array  $attributes
     * @return array
     */
    protected function getScopeState($group, $attributes)
    {
        $scope = [
            'input' => [],
            'files' => [],
            'keep' => []
        ];

        foreach ($attributes as $attribute => $value) {
            $attribute = FlexibleAttribute::make($attribute, $group, is_array($value));

            // Sub-objects could contain files that need to be kept
            if($attribute->isAggregate()) {
                $scope['keep'] = array_merge($scope['keep'], $this->getNestedFiles($value));
                $scope['input'][$attribute->name] = $value;
                continue;
            }

            // Register Files
            if($attribute->isFlexibleFile($value)) {
                $scope['files'][$attribute->name] = $value;
                continue;
            }

            // Register regular attributes
            $scope['input'][$attribute->name] = $value;
        }

        return $scope;
    }

    /**
     * Get nested file attributes
     *
     * @param  array  $iterable
     * @param  null|string  $group
     * @return array
     */
    protected function getNestedFiles($iterable, $group = null)
    {
        $files = [];
        $key = $this->isFlexibleStructure($iterable) ? $iterable['key'] : null;

        foreach ($iterable as $attribute => $value) {
            if(is_array($value)) {
                $files = array_merge($files, $this->getNestedFiles($value, $key));
                continue;
            }

            $attribute = FlexibleAttribute::make($attribute, $group);

            if(!$attribute->isFlexibleFile($value)) continue;

            $files[] = $value;
        }

        return $files;
    }

    /**
     * Get all useful files from current files list
     *
     * @param  array  $toRename
     * @param  array  $toKeep
     * @return array
     */
    protected function getScopeFiles($toRename, $toKeep)
    {
        $files = [];

        foreach($this->files->all() as $attribute => $file) {
            if(in_array($attribute, $toRename)) {
                $files[array_search($attribute, $toRename)] = $file;
                continue;
            }
            
            if(in_array($attribute, $toKeep)) {
                $files[$attribute] = $file;
            }
        }

        return $files;
    }

    /**
     * Check if the given array represents a flexible group
     *
     * @param  array  $iterable
     * @return bool
     */
    protected function isFlexibleStructure($iterable)
    {
        $keys = array_keys($iterable);

        if (count($keys) !== 3) {
            return false;
        }

        return  in_array('layout', $keys) 
                && in_array('key', $keys)
                && in_array('attributes', $keys);
    }
}

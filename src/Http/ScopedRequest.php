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
            $this->getScopeFiles($scope['files'], $group)
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
        ];

        foreach ($attributes as $attribute => $value) {
            $attribute = FlexibleAttribute::make($attribute, $group, is_array($value));

            // Sub-objects could contain files that need to be kept
            if($attribute->isAggregate()) {
                $scope['files'] = array_merge($scope['files'], $this->pullNestedFiles($value, $attribute->group));
                $scope['input'][$attribute->name] = $value;
                continue;
            }

            // Register Files
            if($attribute->isFlexibleFile($value)) {
                $scope['files'][] = $attribute->getFlexibleFileAttribute($value);
                continue;
            }

            // Register regular attributes
            $scope['input'][$attribute->name] = $value;
        }

        return $scope;
    }

    /**
     * Get & remove nested file attributes from given array
     *
     * @param  array  $iterable
     * @param  null|string  $group
     * @return array
     */
    protected function pullNestedFiles(&$iterable, $group = null)
    {
        $files = [];
        $key = $this->isFlexibleStructure($iterable) ? $iterable['key'] : $group;

        foreach ($iterable as $original => $value) {
            if(is_array($value)) {
                $files = array_merge($files, $this->pullNestedFiles($value, $key));
                $iterable[$original] = $value ? $value : null;
                continue;
            }

            $attribute = FlexibleAttribute::make($original, $group);

            if(!$attribute->isFlexibleFile($value)) {
                continue;
            }

            $files[] = $attribute->getFlexibleFileAttribute($value);

            $iterable[$original] = null;
        }

        $iterable = array_filter($iterable);

        return $files;
    }

    /**
     * Get all useful files from current files list
     *
     * @param  array  $files
     * @param  string  $group
     * @return array
     */
    protected function getScopeFiles($files, $group)
    {
        $scope = [];
        $files = collect($files)->keyBy('original');

        foreach ($this->getFlattenedFiles() as $attribute => $file) {
            $attribute = FlexibleAttribute::makeFromUpload($attribute, $group);

            if(!($target = $files->get($attribute->original))) {
                continue;
            }

            if(!$target->group || $target->group !== $group) {
                $scope[$target->original] = $file;
                continue;
            }

            $target->setDataIn($scope, $file);
        }

        return $scope;
    }

    /**
     * Get the request's files as a "flat" (1 dimension) array
     *
     * @return array
     */
    protected function getFlattenedFiles($iterable = null, FlexibleAttribute $original = null)
    {
        $files = [];

        foreach ($iterable ?? $this->files->all() as $key => $value) {
            $attribute = $original ? $original->nest($key) : FlexibleAttribute::make($key);

            if(!is_array($value)) {
                $files[$attribute->original] = $value;
                continue;
            }

            $files = array_merge($files, $this->getFlattenedFiles($value, $attribute));
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

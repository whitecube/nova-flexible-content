<?php

namespace Whitecube\NovaFlexibleContent\Value;

interface ResolverInterface
{
    /**
     * Resolve the Flexible field's content.
     * @see https://whitecube.github.io/nova-flexible-content/#/?id=resolving-the-field
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @param  \Whitecube\NovaFlexibleContent\Layouts\Collection  $layouts
     * @return  \Illuminate\Support\Collection<array-key, \Whitecube\NovaFlexibleContent\Layouts\Layout>
     */
    public function get($resource, $attribute, $layouts);

    /**
     * Save the Flexible field's content somewhere the get method will be able to access it.
     * @see https://whitecube.github.io/nova-flexible-content/#/?id=filling-the-field
     *
     * @param  mixed  $resource
     * @param  string  $attribute Attribute name set for a Flexible field.
     * @param  \Illuminate\Support\Collection<int, \Whitecube\NovaFlexibleContent\Layouts\Layout>  $groups
     * @return  mixed
     */
    public function set($resource, $attribute, $groups);
}

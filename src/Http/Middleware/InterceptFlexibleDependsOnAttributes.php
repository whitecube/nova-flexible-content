<?php

namespace Whitecube\NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

class InterceptFlexibleDependsOnAttributes
{
    /**
     * Handle the given request and get the response.
     *
     * This middleware transforms flexible content field names in dependsOn requests
     * by stripping the group key prefix so that the dependsOn callbacks receive
     * the original field names they expect.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldTransformRequest($request)) {
            return $next($request);
        }

        $this->transformFlexibleFieldNames($request);

        return $next($request);
    }

    /**
     * Determine if the request should be transformed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldTransformRequest(Request $request): bool
    {
        // Only intercept Nova's dependent field requests
        if (! $this->isDependentFieldRequest($request)) {
            return false;
        }

        // Only transform if the request contains flexible content field names
        return $this->hasFlexibleFieldNames($request);
    }

    /**
     * Check if this is a Nova dependent field request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isDependentFieldRequest(Request $request): bool
    {
        $path = $request->path();

        // Match Nova API routes for creation-fields and update-fields
        return preg_match('/nova-api\/[^\/]+(?:\/\d+)?\/(?:creation-fields|update-fields)/', $path) === 1;
    }

    /**
     * Check if the request contains flexible content field names.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasFlexibleFieldNames(Request $request): bool
    {
        // Check if the 'field' parameter or any input contains the flexible group separator
        $field = $request->get('field');

        if ($field && strpos($field, FlexibleAttribute::GROUP_SEPARATOR) !== false) {
            return true;
        }

        foreach ($request->all() as $key => $value) {
            if (strpos($key, FlexibleAttribute::GROUP_SEPARATOR) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Transform flexible field names by stripping the group key prefix.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function transformFlexibleFieldNames(Request $request): void
    {
        // Transform the 'field' query parameter if present
        $field = $request->get('field');

        if ($field && strpos($field, FlexibleAttribute::GROUP_SEPARATOR) !== false) {
            $transformedField = $this->stripGroupPrefix($field);
            $request->query->set('field', $transformedField);
        }

        // Transform all input field names
        $transformedInput = $this->transformInputFields($request->all());

        if (! empty($transformedInput)) {
            $request->merge($transformedInput);
        }
    }

    /**
     * Transform input fields by stripping flexible group prefixes.
     *
     * @param  array  $input
     * @return array
     */
    protected function transformInputFields(array $input): array
    {
        $transformed = [];

        foreach ($input as $key => $value) {
            if (strpos($key, FlexibleAttribute::GROUP_SEPARATOR) !== false) {
                $newKey = $this->stripGroupPrefix($key);
                $transformed[$newKey] = $value;
            }
        }

        return $transformed;
    }

    /**
     * Strip the flexible group prefix from a field name.
     *
     * @param  string  $fieldName
     * @return string
     */
    protected function stripGroupPrefix(string $fieldName): string
    {
        $separatorPosition = strpos($fieldName, FlexibleAttribute::GROUP_SEPARATOR);

        if ($separatorPosition === false) {
            return $fieldName;
        }

        return substr($fieldName, $separatorPosition + strlen(FlexibleAttribute::GROUP_SEPARATOR));
    }
}

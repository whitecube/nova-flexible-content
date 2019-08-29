<?php

namespace Whitecube\NovaFlexibleContent\Http;

use Illuminate\Support\Str;
use Whitecube\NovaFlexibleContent\Flexible;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait TransformsFlexibleErrors
{
    /**
     * Checks whether the given response's flexible errors can and should be transformed
     *
     * @param Response $response
     * @return bool
     */
    protected function shouldTransformFlexibleErrors(Response $response)
    {
        return ($response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY && is_a($response, JsonResponse::class));
    }

    /**
     * Updates given response's errors for the concerned flexible fields
     *
     * @param Response $response
     * @return \Illuminate\Http\Response
     */
    protected function transformFlexibleErrors(Response $response)
    {
        $response->setData($this->updateResponseErrors($response->original));
        return $response;
    }

    /**
     * Run response errors parsing if necessary
     *
     * @param  array  $data
     * @return array
     */
    protected function updateResponseErrors($data)
    {
        if(!($data['errors'] ?? null)) {
            return $data;
        }

        $data['errors'] = $this->getTransformedErrors($data['errors']);

        return $data;
    }

    /**
     * Transforms the original errors array in a nested
     * array structure.
     *
     * @param  array  $errors
     * @return array
     */
    protected function getTransformedErrors($errors)
    {
        $parsed = [];

        foreach($errors as $key => $messages) {
            $attribute = Flexible::getValidationKey($key);

            if(!$attribute) {
                $parsed[$key] = $messages;
                continue;
            }

            $parsed[$attribute->original] = $this->transformMessages($messages, $key, $attribute);
        }

        return $parsed;
    }

    /**
     * Update human error messages with correct field names
     *
     * @param  array  $messages
     * @param  string $key
     * @param  \Whitecube\NovaFlexibleContent\Http\FlexibleAttribute  $attribute
     * @return array
     */
    protected function transformMessages($messages, $key, $attribute)
    {
        $search = str_replace('_', ' ', Str::snake($key));
        $attribute = str_replace('_', ' ', Str::snake($attribute->name));

        return array_map(function($message) use ($search, $attribute) {
            return str_replace(
                [$search, Str::upper($search), Str::ucfirst($search)], 
                [$attribute, Str::upper($attribute), Str::ucfirst($attribute)],
                $message
            );
        }, $messages);
    }
}

<?php

namespace Whitecube\NovaFlexibleContent\Http;

use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

trait TransformsFlexibleErrors
{
    /**
     * Updates given response's errors for the concerned flexible fields
     *
     * @param  \Illuminate\Http\JsonResponse  $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function transformFlexibleErrors(JsonResponse $response)
    {
        if ($response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY) {
            $response->setData($this->updateResponseErrors($response->original));
        }

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
        // TODO
        return $messages;
    }
}

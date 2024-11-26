<?php

namespace Whitecube\NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;
use Whitecube\NovaFlexibleContent\Http\ParsesFlexibleAttributes;
use Whitecube\NovaFlexibleContent\Http\TransformsFlexibleErrors;

class InterceptFlexibleAttributes
{
    use ParsesFlexibleAttributes;
    use TransformsFlexibleErrors;

    /**
     * Parse any flexible inputs within the given request
     *
     * @param  array  $inputs
     * @return array
     */
    protected function parseRequestInput($inputs)
    {
        if ($this->hasParsableFlexibleInputs($inputs)) {
            foreach ($this->getParsedFlexibleInputs($inputs) as $input => $value) {
                $inputs[$input] = $value;
            }
        }

        foreach ($inputs as $input => $value) {
            if (is_array($value)) {
                $inputs[$input] = $this->parseRequestInput($value);
            }
            if ($input === FlexibleAttribute::REGISTER) {
                unset($inputs[$input]);
            }
        }

        return $inputs;
    }

    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->requestHasParsableFlexibleInputs($request)) {
            return $next($request);
        }

        $request->request->replace($this->parseRequestInput($request->input()));

        $response = $next($request);

        if (! $this->shouldTransformFlexibleErrors($response)) {
            return $response;
        }

        return $this->transformFlexibleErrors($response);
    }
}

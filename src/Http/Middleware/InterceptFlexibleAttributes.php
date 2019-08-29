<?php

namespace Whitecube\NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Whitecube\NovaFlexibleContent\Http\ParsesFlexibleAttributes;
use Whitecube\NovaFlexibleContent\Http\TransformsFlexibleErrors;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

class InterceptFlexibleAttributes
{
    use ParsesFlexibleAttributes;
    use TransformsFlexibleErrors;

    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        if ($this->requestHasParsableFlexibleInputs($request)) {
            $request->merge($this->getParsedFlexibleInputs($request));
            $request->request->remove(FlexibleAttribute::REGISTER);

            $response = $next($request);
            if ($this->shouldTransformFlexibleErrors($response)) {
                return $this->transformFlexibleErrors($response);
            }
            return $response;
        }

        return $next($request);
    }
}

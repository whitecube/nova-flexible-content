<?php

namespace Kraenkvisuell\NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kraenkvisuell\NovaFlexibleContent\Http\ParsesFlexibleAttributes;
use Kraenkvisuell\NovaFlexibleContent\Http\TransformsFlexibleErrors;
use Kraenkvisuell\NovaFlexibleContent\Http\FlexibleAttribute;

class InterceptFlexibleAttributes
{
    use ParsesFlexibleAttributes;
    use TransformsFlexibleErrors;

    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        if (!$this->requestHasParsableFlexibleInputs($request)) {
            return $next($request);
        }

        $request->merge($this->getParsedFlexibleInputs($request));
        $request->request->remove(FlexibleAttribute::REGISTER);

        $response = $next($request);

        if (!$this->shouldTransformFlexibleErrors($response)) {
            return $response;
        }

        return $this->transformFlexibleErrors($response);
    }
}

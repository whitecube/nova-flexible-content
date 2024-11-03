<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Wmt\NovaFlexibleContent\Http\FlexibleAttribute;
use Wmt\NovaFlexibleContent\Http\ParsesFlexibleAttributes;
use Wmt\NovaFlexibleContent\Http\TransformsFlexibleErrors;

class InterceptFlexibleAttributes
{
    use ParsesFlexibleAttributes;
    use TransformsFlexibleErrors;

    /**
     * Handle the given request and get the response.
     */
    public function handle(Request $request, Closure $next): Response
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

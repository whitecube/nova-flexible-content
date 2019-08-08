<?php

namespace Whitecube\NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Whitecube\NovaFlexibleContent\Http\ParsesFlexibleAttributes;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

class InterceptFlexibleAttributes
{
    use ParsesFlexibleAttributes;

    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        if($this->requestHasParsableFlexibleInputs($request)) {
            $request->merge($this->getParsedFlexibleInputs($request));
            $request->request->remove(FlexibleAttribute::REGISTER);
        }

        return $next($request);
    }
}

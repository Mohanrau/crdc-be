<?php
namespace App\Http\Middleware;

use Closure;

class JsonApiMiddleware
{
    private $methods = ['POST', 'PUT', 'PATCH'];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->getMethod(),$this->methods)) {

            if (json_decode($request->getContent(),true) === null) {
                return response(['error' => 'your json format is not well-formed, please fix your json request body'], 422);
            }

            $request->merge(json_decode($request->getContent(),true));
        }

        return $next($request);
    }
}

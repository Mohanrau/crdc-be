<?php
namespace App\Http\Middleware;

use Closure;

class CheckRequestAgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //TODO check the user agent for live to disable agents like postman.
        //dump($request->server('HTTP_USER_AGENT'));

        //\dd($request->header('USER-AGENT'));

        return $next($request);
    }
}

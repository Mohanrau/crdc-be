<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class CheckApiKey
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
        if(Config::get('setting.api-key') == $request->header('api-key'))
        {
            return $next($request);
        }

        return response(['error' => 'Un authorized to access api'], 401);
    }
}

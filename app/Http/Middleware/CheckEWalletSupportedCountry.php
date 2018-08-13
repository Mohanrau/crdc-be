<?php
namespace App\Http\Middleware;

use App\Models\Users\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CheckEWalletSupportedCountry
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
        $eWalletSupportedCountries = Config::get('ewallet.supported_countries');

        $user = User::find(Auth::id());

        if($user->member)
        {
            $userCountry = $user->member->country->code_iso_2;

            if(in_array($userCountry, $eWalletSupportedCountries))
            {
                return $next($request);
            }
        }

        return response(['error' => trans('message.user.un_authorized')], 403);
    }
}

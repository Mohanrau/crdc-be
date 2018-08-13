<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /* @eval($var++) */
        \Blade::extend(function($view)
        {
            return preg_replace('/\@define(.+)/', '<?php ${1}; ?>', $view);
        });
    }

    public function register()
    {
        //
    }
}
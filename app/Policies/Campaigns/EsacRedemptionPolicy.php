<?php

namespace App\Policies\Campaigns;

use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EsacRedemptionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}

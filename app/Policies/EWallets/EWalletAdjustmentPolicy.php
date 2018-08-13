<?php
namespace App\Policies\EWallets;

use App\Helpers\Traits\AccessControl;
use App\Helpers\Traits\Policy;
use App\Models\EWallets\EWalletAdjustment;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class EWalletAdjustmentPolicy
{
    use HandlesAuthorization, AccessControl, Policy {
        update as oldUpdate;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * EWalletAdjustmentPolicy constructor.
     *
     * @param EWalletAdjustment $eWalletAdjustment
     */
    public function __construct(EWalletAdjustment $eWalletAdjustment)
    {
        $this->modelObj = $eWalletAdjustment;

        $this->requestObj = Request::all();

        $this->moduleName = 'adjustment';

        $this->modelId = 'adjustment_id';
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @param User $user
     * @param EWalletAdjustment $eWalletAdjustment
     * @param int $countryId
     * @return mixed
     */
    public function update(User $user, EWalletAdjustment $eWalletAdjustment, int $countryId)
    {
        if (! Gate::allows($this->moduleName.'.update', $countryId)){
            return false;
        }

        return true;
    }

    /**
     * get country id
     *
     * @param string $section
     * @return mixed
     */
    private function getCountryId(string $section = null)
    {
        return $this->requestObj['country_id'];
    }
}

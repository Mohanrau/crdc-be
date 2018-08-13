<?php
namespace App\Policies\EWallets;

use App\Helpers\Traits\AccessControl;
use App\Helpers\Traits\Policy;
use App\Models\EWallets\EWallet;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class EWalletPolicy
{
    use HandlesAuthorization, AccessControl, Policy{
        view as oldView;
        update as oldUpdate;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * EWalletPolicy constructor.
     *
     * @param EWallet $eWallet
     */
    public function __construct(EWallet $eWallet)
    {
        $this->modelObj = $eWallet;

        $this->requestObj = Request::all();

        $this->moduleName = 'ewallet';

        $this->modelId = 'ewallet_id';
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @param User $user
     * @param $model
     * @return mixed
     */
    public function view(User $user, EWallet $model)
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $model->user->member->country_id)){
            return false;
        }

        //check if resource belongs to user
        $this->resourceBelongToMe($model->user_id);

        return true;
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @param User $user
     * @param EWallet $model
     * @return mixed
     */
    public function update(User $user, EWallet $model)
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.update', $model->user->member->country_id)){
            return false;
        }

        //check if resource belongs to user
        $this->resourceBelongToMe($model->user_id);

        return true;
    }
}

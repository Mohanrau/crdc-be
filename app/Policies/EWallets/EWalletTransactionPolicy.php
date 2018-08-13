<?php
namespace App\Policies\EWallets;

use App\Helpers\Traits\AccessControl;
use App\Helpers\Traits\Policy;
use App\Models\EWallets\EWalletTransaction;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class EWalletTransactionPolicy
{
    use HandlesAuthorization, AccessControl, Policy {
        create as oldCreate;
        view as oldView;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * EWalletTransactionPolicy constructor.
     *
     * @param EWalletTransaction $eWalletTransaction
     */
    public function __construct(EWalletTransaction $eWalletTransaction)
    {
        $this->modelObj = $eWalletTransaction;

        $this->requestObj = Request::all();

        $this->moduleName = 'ewallet.transaction';

        $this->modelId = 'ewallet_transaction_id';
    }

    /**
     * Determine whether the user can create resource.
     *
     * @param User $user
     * @param EWalletTransaction $eWalletTransaction
     * @param int $countryId
     * @return mixed
     */
    public function create(User $user, EWalletTransaction $eWalletTransaction, int $countryId)
    {
        if (! Gate::allows($this->moduleName.'.create', $countryId)){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @return mixed
     */
    public function view()
    {
        $model = $this->modelObj->find($this->requestObj[$this->modelId]);

        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $model->ewallet->user->member->country_id)){
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
        if(!$this->isUser('member'))
        {
            return $this->requestObj['country_id'];
        }

        return 0;
    }
}

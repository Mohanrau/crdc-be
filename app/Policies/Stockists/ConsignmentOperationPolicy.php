<?php
namespace App\Policies\Stockists;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\Policy,
    Models\Stockists\ConsignmentDepositRefund,
    Models\Stockists\Stockist,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class ConsignmentOperationPolicy
{
    use HandlesAuthorization, AccessControl, Policy{
        create as oldCreate;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId,
        $stockistObj
    ;

    /**
     * ConsignmentOperationPolicy constructor.
     *
     * @param ConsignmentDepositRefund $model
     * @param Stockist $stockist
     */
    public function __construct(
        ConsignmentDepositRefund $model,
        Stockist $stockist
    )
    {
        $this->modelObj = $model;

        $this->stockistObj = $stockist;

        $this->requestObj = Request::all();

        $this->moduleName = 'stockist.consignment.deposit';

        $this->modelId = 'stockist_id';
    }

    /**
     * Determine whether the user can create resource.
     *
     * @param User $user
     * @param int $stockistId
     * @return bool
     */
    public function create(User $user, int $stockistId)
    {
        $stockist =  $this->stockistObj->find($stockistId);

        if (! Gate::allows($this->moduleName.'.create',$stockist->country_id)){
            return false;
        }

        $this->resourceBelongToMe($stockist->stockist_user_id, 'stockist');

        return true;
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @param User $user
     * @param ConsignmentDepositRefund $model
     * @return bool
     */
    public function view(User $user, ConsignmentDepositRefund $model)
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $model->stockist->country_id)){
            return false;
        }

        $this->resourceBelongToMe($model->stockist->stockist_user_id, 'stockist');

        return true;
    }

    /**
     * Determine whether the user can update resource.
     *
     * @param User $user
     * @param ConsignmentDepositRefund $model
     * @return bool
     */
    public function update(User $user, ConsignmentDepositRefund $model)
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.update', $model->stockist->country_id)){
            return false;
        }

        $this->resourceBelongToMe($model->stockist->stockist_user_id, 'stockist');

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

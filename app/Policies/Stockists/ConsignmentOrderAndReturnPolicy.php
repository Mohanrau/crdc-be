<?php
namespace App\Policies\Stockists;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\Policy,
    Models\Stockists\ConsignmentOrderReturn,
    Models\Stockists\Stockist,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};
class ConsignmentOrderAndReturnPolicy
{
    use HandlesAuthorization, AccessControl;

    private
        $modelObj,
        $stockistObj,
        $requestObj,
        $consignmentReturnModule,
        $consignmentOrderModule,
        $consignmentStatus,
        $modelId
    ;

    /**
     * ConsignmentOrderReturnPolicy constructor.
     *
     * ConsignmentOrderAndReturnPolicy constructor.
     * @param ConsignmentOrderReturn $model
     * @param Stockist $stockist
     */
    public function __construct(ConsignmentOrderReturn $model, Stockist $stockist)
    {
        $this->modelObj = $model;

        $this->stockistObj = $stockist;

        $this->requestObj = Request::all();

        $this->consignmentReturnModule = 'stockist.consignment.return';

        $this->consignmentOrderModule = 'stockist.consignment.orders';

        $this->consignmentStatus = config('mappings.consignment_order_and_return_type');

        $this->modelId = '';
    }

    /**
     * check if the user is superAdmin then no need to check the authorization
     *
     * @param $user
     * @param $ability
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->isRootUser()) {
            return true;
        }
    }

    /**
     * check the authorization for the listing page
     *
     * @return bool
     */
    public function listing()
    {
        if (strtolower($this->requestObj['type']) == strtolower($this->consignmentStatus['order'])){
            if (! Gate::allows($this->consignmentOrderModule.'.list', $this->getCountryId())){
                return false;
            }
        }else if (strtolower($this->requestObj['type']) == strtolower($this->consignmentStatus['return'])){
            if (! Gate::allows($this->consignmentReturnModule.'.list', $this->getCountryId())){
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can create resource.
     *
     * @return mixed
     */
    public function create()
    {
        $stockist = $this->stockistObj->find($this->requestObj['consignment_order_return']['stockist_id']);

        if(strtolower($this->requestObj['consignment_order_return']['type']) == strtolower($this->consignmentStatus['order'])){
            if (! Gate::allows(
                $this->consignmentOrderModule.'.create', $stockist->country_id)){
                return false;
            }
        }elseif(strtolower($this->requestObj['consignment_order_return']['type']) == strtolower($this->consignmentStatus['return'])){
            if (! Gate::allows(
                $this->consignmentReturnModule.'.create', $stockist->country_id)){
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @param User $user
     * @param ConsignmentOrderReturn $model
     * @return bool
     */
    public function view(User $user, ConsignmentOrderReturn $model)
    {
        $type = $model->consignmentOrderReturnType()->first();

        if (strtolower($type->title) == strtolower($this->consignmentStatus['order'])){
            //check if user has access
            if (! Gate::allows($this->consignmentOrderModule.'.view', $model->stockist->country_id)){
                return false;
            }
        }else if(strtolower($type->title) == strtolower($this->consignmentStatus['return'])){
            //check if user has access
            if (! Gate::allows($this->consignmentReturnModule.'.view', $model->stockist->country_id)){
                return false;
            }
        }

        $this->resourceBelongToMe($model->stockist->stockist_user_id, 'stockist');

        return true;
    }

    /**
     * check permission for download Consignment Note
     *
     * @param User $user
     * @param ConsignmentOrderReturn $model
     * @return bool
     */
    public function downloadConsignmentNote(User $user, ConsignmentOrderReturn $model)
    {
        //check if user has access to this resource
        if (! Gate::allows('consignment.deposit.files.download', $model->stockist->country_id)){
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

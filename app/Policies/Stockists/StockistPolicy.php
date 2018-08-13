<?php
namespace App\Policies\Stockists;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\Policy,
    Models\Stockists\Stockist
};
use App\Models\Users\User;
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class StockistPolicy
{
    use HandlesAuthorization, AccessControl, Policy{
        view as oldView;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * StockistPolicy constructor.
     *
     * @param Stockist $model
     */
    public function __construct(Stockist $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'stockists';

        $this->modelId = 'stockist_id';
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @param User $user
     * @param Stockist $model
     * @return bool
     */
    public function view(User $user, Stockist $model)
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $model->country_id)){
            return false;
        }

        $this->resourceBelongToMe($model->stockist_user_id, 'stockist');

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
        if ($section == 'create' || $section == 'update')
            return $this->requestObj['stockist_data']['details']['country_id'];
        else{
            return (
            isset($this->requestObj['country_id']) ?
                $this->requestObj['country_id'] :
                null
            );
        }
    }
}

<?php
namespace App\Policies\Campaigns;

use App\{
    Helpers\Traits\Policy,
    Models\Campaigns\EsacVoucherType,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class EsacVoucherTypePolicy
{
    use HandlesAuthorization, Policy{
        view as oldView;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * EsacVoucherTypePolicy constructor.
     *
     * @param EsacVoucherType $model
     */
    public function __construct(EsacVoucherType $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'esac.voucher.types';

        $this->modelId = 'id';
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param EsacVoucherType $model
     * @return bool
     */
    public function view(User $user, EsacVoucherType $model)
    {
        if (! Gate::allows($this->moduleName.'.view', $model->country_id)){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the resource.
     *
     * @param User $user
     * @param EsacVoucherType $model
     * @return bool
     */
    public function delete(User $user, EsacVoucherType $model)
    {
        if (! Gate::allows($this->moduleName.'.view', $model->country_id)){
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

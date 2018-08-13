<?php
namespace App\Policies\Campaigns;

use App\{
    Helpers\Traits\Policy,
    Models\Campaigns\EsacVoucherSubType,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};


class EsacVoucherTypeSubPolicy
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
     * EsacVoucherTypeSubPolicy constructor.
     *
     * @param EsacVoucherSubType $model
     */
    public function __construct(EsacVoucherSubType $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'esac.voucher.sub.types';

        $this->modelId = 'id';
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param EsacVoucherSubType $model
     * @return bool
     */
    public function view(User $user, EsacVoucherSubType $model)
    {
        if (! Gate::allows($this->moduleName.'.view', $model->esacVoucherType->country_id)){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the resource.
     *
     * @param User $user
     * @param EsacVoucherSubType $model
     * @return bool
     */
    public function delete(User $user, EsacVoucherSubType $model)
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

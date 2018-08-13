<?php
namespace App\Policies\Campaigns;

use App\{
    Helpers\Traits\Policy,
    Models\Campaigns\EsacVoucher,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class EsacVoucherPolicy
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
     * EsacVoucherPolicy constructor.
     *
     * @param EsacVoucher $model
     */
    public function __construct(EsacVoucher $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'esac.vouchers';

        $this->modelId = 'id';
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param EsacVoucher $model
     * @return bool
     */
    public function view(User $user, EsacVoucher $model)
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
     * @param EsacVoucher $model
     * @return bool
     */
    public function delete(User $user, EsacVoucher $model)
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

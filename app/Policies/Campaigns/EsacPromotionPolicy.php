<?php
namespace App\Policies\Campaigns;

use App\{
    Helpers\Traits\Policy,
    Models\Campaigns\EsacPromotion,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};
class EsacPromotionPolicy
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
     * EsacPromotionPolicy constructor.
     *
     * @param EsacPromotion $model
     */
    public function __construct(EsacPromotion $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'esac.promotions';

        $this->modelId = 'id';
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param EsacPromotion $model
     * @return bool
     */
    public function view(User $user, EsacPromotion $model)
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
     * @param EsacPromotion $model
     * @return bool
     */
    public function delete(User $user, EsacPromotion $model)
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

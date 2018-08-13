<?php
namespace App\Policies\Campaigns;

use App\{
    Helpers\Traits\Policy,
    Models\Campaigns\Campaign,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};
class CampaignPolicy
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
     * CampaignPolicy constructor.
     *
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        $this->modelObj = $campaign;

        $this->requestObj = Request::all();

        $this->moduleName = 'campaigns';

        $this->modelId = 'id';
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param Campaign $model
     * @return bool
     */
    public function view(User $user, Campaign $model)
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
     * @param Campaign $model
     * @return bool
     */
    public function delete(User $user, Campaign $model)
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

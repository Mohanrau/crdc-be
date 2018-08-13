<?php
namespace App\Policies\Members;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\Policy,
    Models\Members\Member,
    Models\Users\User,
    Interfaces\Members\MemberTreeInterface
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class MemberPolicy
{
    use HandlesAuthorization, AccessControl;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId,
        $memberTreeRepository
    ;

    /**
     * MemberPolicy constructor.
     *
     * @param Member $model
     * @param MemberTreeInterface $memberTreeRepository
     */
    public function __construct(Member $model, MemberTreeInterface $memberTreeRepository)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'members';

        $this->modelId = 'country_id';

        $this->memberTreeRepository = $memberTreeRepository;
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
        if (! Gate::allows($this->moduleName.'.list')){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for the listing page
     *
     * @return bool
     */
    public function search()
    {
        if (! Gate::allows($this->moduleName.'.search')){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @return bool
     */
    public function view()
    {
        $model = $this->getMemberModel();

        if($this->checkUserTypeSelfResource('member', 'view', $model)){
            $this->resourceBelongToMyDownLine($model->user_id);

            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can create resource.
     *
     * @return mixed
     */
    public function create()
    {
        if (! Gate::allows($this->moduleName.'.create', $this->getCountryId('create'))){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @return mixed
     */
    public function update()
    {
        //check if user is has access to given permission
        $this->checkUserTypeSelfResource('member', 'view');

        $this->resourceBelongToMe($this->requestObj['member_data']['details']['user_id']);

        return true;
    }

    /**
     * check the authorization for the member rank listing
     *
     * @return bool
     */
    public function memberRankListing()
    {
        if (! Gate::allows($this->moduleName.'.rank.list')){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for member rank update
     *
     * @return bool
     */
    public function memberRankUpdate()
    {
        if (! Gate::allows($this->moduleName.'.rank.update')){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for the member status listing
     *
     * @return bool
     */
    public function memberStatusListing()
    {
        if (! Gate::allows($this->moduleName.'.status.list')){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for member status update
     *
     * @return bool
     */
    public function memberStatusUpdate()
    {
        if (! Gate::allows($this->moduleName.'.status.update')){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for the member migrate listing
     *
     * @return bool
     */
    public function memberMigrateListing()
    {
        if (! Gate::allows($this->moduleName.'.migration.list')){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for member migrate update
     *
     * @return bool
     */
    public function memberMigrateUpdate()
    {
        if (! Gate::allows($this->moduleName.'.migration.update')){
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
        if ($section == 'create' || $section == 'update')
            return $this->requestObj['member_data']['details']['country_id'];
        else
            return $this->requestObj['country_id'];
    }

    /**
     * get member model
     *
     * @return mixed
     */
    private function getMemberModel()
    {
        if (isset($this->requestObj['user_id'])){
            $model = $this->modelObj
                ->where('user_id',$this->requestObj['user_id'])
                ->first();
        }else{
            $user = User::where('old_member_id', $this->requestObj['old_member_id'])
                ->first();

            $model = $this->modelObj
                ->where('user_id', $user->id)
                ->first();
        }

        return $model;
    }
}

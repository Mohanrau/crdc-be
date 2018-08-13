<?php
namespace App\Policies\Members;

use App\Models\Members\MemberTree;
use App\Helpers\Traits\AccessControl;

use Illuminate\{
    Auth\Access\HandlesAuthorization, Support\Facades\Auth, Support\Facades\Gate
};

class MemberTreePolicy
{
    use HandlesAuthorization, AccessControl;

    private
        $modelObj,
        $moduleName
    ;

    /**
     * MemberTreePolicy constructor.
     *
     * @param MemberTree $model
     */
    public function __construct(MemberTree $model)
    {
        $this->modelObj = $model;

        $this->moduleName = 'members';
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
     * Ability to assign a member to member tree
     */
    public function assignMemberTree()
    {
        if(
            Auth::user()->isRootUser() ||
            ($this->isUser('back_office') && Gate::allows($this->moduleName.'.create'))
        ){
            return true;
        }

        return false;
    }

    /**
     * check the if the user authorize for accessing placement tree listing
     *
     * @return bool
     */
    public function memberPlacementTree()
    {
        // we allow member to see placement tree because it will be filtered later on
        if($this->isUser('member')){
            return true;
        }

        if (! Gate::allows($this->moduleName.'.placement.tree.list')){
            return false;
        }

        return true;
    }

    /**
     * check the if the user authorize for accessing sponsor Tree tree listing
     *
     * @return bool
     */
    public function memberSponsorTree()
    {
        if (! Gate::allows($this->moduleName.'.sponsor.tree.list')){
            return false;
        }

        return true;
    }
}

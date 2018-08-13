<?php
namespace App\Http\Controllers\V1\Members;

use App\{
    Helpers\Traits\AccessControl,
    Interfaces\Members\MemberTreeInterface,
    Http\Controllers\Controller,
    Models\Members\MemberTree
};
use Illuminate\Http\Request;

class MemberTreeController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel;

    /**
     * MemberTreeController constructor.
     *
     * @param MemberTreeInterface $memberTreeRepository
     * @param MemberTree $model
     */
    public function __construct(MemberTreeInterface $memberTreeRepository, MemberTree $model)
    {
        $this->middleware('auth');

        $this->obj = $memberTreeRepository;

        $this->authorizedModel = $model;
    }

    /**
     * get member's placement tree with given member id and depth
     *
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function memberPlacementTree(Request $request)
    {
        $this->authorize('memberPlacementTree', [$this->authorizedModel]);

        request()->validate([
            'user_id' => 'required|integer|exists:member_trees,user_id',
            'depth' => 'required|integer'
        ]);

        return $this->obj->getPlacementTree(
            $request->input('user_id'),$request->input('depth')
        );
    }

    /**
     * get member's tree with given member id, depth, tree_type
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function memberTree(Request $request)
    {
        request()->validate([
            'user_id' => 'required|integer|exists:member_trees,user_id',
            'depth' => 'required|integer',
            'tree_type' => 'required|in:sponsor,placement'
        ]);

        if($request->input('tree_type') == 'placement'){
            $this->authorize('memberPlacementTree', [$this->authorizedModel]);

            //check if this resource belong to the give user
            $this->resourceBelongToMe($request->input('user_id'));

            return response($this->obj
                ->getPlacementNetworkTuned($request->input('user_id'),$request->input('depth')));
        }

        if($request->input('tree_type') == 'sponsor'){
            $this->authorize('memberSponsorTree', [$this->authorizedModel]);

            //check if this resource belong to the give user
            $this->resourceBelongToMe($request->input('user_id'));

            return response($this->obj
                ->getSponsorNetworkTuned($request->input('user_id'),$request->input('depth'))
            );
        }
    }

    /**
     * Allows to do an insertion to a member tree
     */
    public function assignMemberTree(Request $request)
    {
        //only back-office user and has access to member tree
        $this->authorize('assignMemberTree', [$this->authorizedModel]);

        request()->validate([
            'user_id' => 'required|integer|exists:members,user_id', // must be a member
            'sponsor_user_id' => 'required|integer|exists:member_trees,user_id', // must exists in tree
            'placement_user_id' => 'integer|exists:members,user_id',
            'position' => 'required|integer|in:0,1,2' // 0 = auto, 1 = left, 2 = right
        ]);

        return response(
            $this->obj->assignMemberTree(
                [
                    'user_id' => $request->input('user_id'),
                    'sponsor_user_id' => $request->input('sponsor_user_id'),
                    'placement_user_id' => $request->input('placement_user_id', $request->input('sponsor_user_id')),
                    'position' => $request->input('position')
                ]
            )
        );
    }

    /**
     * get member's placement tree outer with given member id , depth and outer left or right
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function memberPlacementTreeOuter(Request $request)
    {
        $this->authorize('memberPlacementTree', [$this->authorizedModel]);

        request()->validate([
            'user_id' => 'required|integer|exists:member_trees,user_id',
            'depth' => 'required|integer',
            'outer' => 'required|in:left,right'
        ]);

        // we do not check if the resource belongs to this user or not, member can always see if this guy
        // is in his sponsor tree.

        return response($this->obj->getPlacementTreeOuter(
            $request->input('user_id'),
            $request->input('depth'),
            $request->input('outer')
        ));
    }

    /**
     * Lightweight search for member sponsor uplines with the entire downline sponsor network. If exists,
     * will return the name, user_id and the old_member_id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function searchSponsorNetwork(Request $request)
    {
        request()->validate([
            'user_id' => 'required_without:old_member_id|integer|exists:users,id|exists:members,user_id',
            'old_member_id' => 'required_without:user_id|exists:users,old_member_id'
        ]);

        //member can bypass this, as the search is limited in the repository
        if(!$this->isUser('member')){
            //check the authorization
            $this->applyListingOrSearchPermission($this->authorizedModel);
        }

        return response($this->obj->searchSponsorNetwork(
            [
                'old_member_id' => $request->input('old_member_id'),
                'user_id' => $request->input('user_id')
            ]
        ));
    }

    /**
     * verify member's downline with given member id , downline member id and tree_type
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function memberTreeDownlineVerify(Request $request)
    {
        request()->validate([
            'upline_user_id' => 'required|integer|exists:users,id',
            'downline_user_id' => 'required|integer|exists:users,id',
            'tree_type' => 'required|in:sponsor,placement'
        ]);

        return response($this->obj->verifyMemberTreeDownline(
            $request->input('tree_type'),
            $request->input('upline_user_id'),
            $request->input('downline_user_id')
        ));
    }

    //TODO implement RNP
    /**
     * verify same member network with given tree type, first user id, and second user id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function sameMemberTreeNetworkVerify(Request $request)
    {
        // Either second_user_id or ibo_id must be populated. Higher precedence : second_user_id
        request()->validate([
            'first_user_id' => 'required|integer|exists:users,id',
            'second_user_id' => 'required_without:ibo_id|integer|exists:users,id',
            'ibo_id' => 'required_without:second_user_id',
            'tree_type' => 'required|in:sponsor,placement'
        ]);

        return response($this->obj->verifySameMemberTreeNetwork(
            $request->input('tree_type'),
            $request->input('first_user_id'),
            $request->input('second_user_id') ? $request->input('second_user_id') : 0,
            $request->input('ibo_id')
        ));
    }

    /**
     * verify placement by sponsor userId and placementUserId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\SyverifyPlacementmfony\Component\HttpFoundation\Response
     */
    public function verifyPlacement(Request $request)
    {
        request()->validate([
            'sponsor_id' => 'required|integer|exists:users,id',
            'placement_member_id' => 'required|integer|exists:users,old_member_id',
            'placement_position' => 'required|integer|in:0,1,2'
        ]);

        return response($this->obj->validatePlacement(
            $request->input('sponsor_id'),
            $request->input('placement_member_id'),
            $request->input('placement_position')
        ));
    }

    /**
     * get sponsor downLine listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSponsorDownlineListing(Request $request)
    {
        //TODO implement RNP
        
        request()->validate([
            'user_id' => 'required|integer|exists:member_trees,user_id',
            'depth' => 'sometimes|nullable|integer|min:0'
        ]);

        return response($this->obj->getSponsorDownlineListing(
            $request->input('user_id'),
            ($request->has('depth') && $request->input('depth') != null) ? $request->input('depth') : 0
        ));
    }
}

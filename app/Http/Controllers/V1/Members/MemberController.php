<?php
namespace App\Http\Controllers\V1\Members;

use App\{Helpers\Traits\AccessControl,
    Http\Requests\Members\MemberEmailVerificationRequest,
    Http\Requests\Members\MemberReqeust,
    Http\Requests\Members\MemberRequest,
    Http\Requests\Members\MemberRanksUpdateRequest,
    Http\Requests\Members\MemberStatusUpdateRequest,
    Http\Requests\Members\MemberMigrateRequest,
    Interfaces\Members\MemberInterface,
    Interfaces\Members\MemberTreeInterface,
    Rules\Members\DownlineUserValidation,
    Http\Controllers\Controller,
    Models\Members\Member};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    use AccessControl;

    private
        $obj,
        $memberTreeRepositoryObj,
        $authorizedModel;

    /**
     * MemberController constructor.
     *
     * @param MemberInterface $memberInterface
     * @param MemberTreeInterface $memberTreeInterface
     * @param Member $model
     */
    public function __construct(MemberInterface $memberInterface, MemberTreeInterface $memberTreeInterface, Member $model)
    {
        $this->middleware('auth');

        $this->obj = $memberInterface;

        $this->memberTreeRepositoryObj = $memberTreeInterface;

        $this->authorizedModel = $model;
    }

    /**
     * get members filtered by a given vars
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterMembers(Request $request)
    {
        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        // overwrite sponsor id as logged in user if the logged in user is member
        if ($this->isUser('member')) {
            $sponsorId = Auth::id();
        } else {
            $sponsorId = $request->has('sponsor_id') ? $request->input('sponsor_id') :  0;
        }

        return response(
            $this->obj->getMembersByFilters(
                ($request->has('country_id') ? $request->input('country_id') : 0),
                ($request->has('verified') ? $request->input('verified') : 3),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0),
                $sponsorId,
                ($request->has('exact_text') ? $request->input('exact_text') : ''),
                ($request->has('mixed_filters') ? $request->input('mixed_filters') :  0)
            )
        );
    }

    /**
     * get member details for a given userId(MemberId)
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function memberDetails(Request $request)
    {
        request()->validate([
            'user_id' => 'required_without:old_member_id|integer|exists:users,id|exists:members,user_id',
            'old_member_id' => 'required_without:user_id||integer|exists:users,old_member_id',
            'upline_id' => 'sometimes|nullable|integer|exists:members,user_id'
        ]);

        //check if user authorized to access this resource
        $this->applySearchOrViewPermission($this->authorizedModel);

        return response($this->obj->memberDetails(
            ($request->has('user_id')? $request->input('user_id') : $request->input('old_member_id')),
            ($request->has('upline_id') ? (($request->input('upline_id') != null)? $request->input('upline_id') : 0)  : 0)
        ));
    }

    /**
     * update member details for a given userId(MemberId)
     *
     * @param MemberRequest $request
     * @param int $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateMember(MemberRequest $request, int $userId)
    {
        $this->authorize('update', [$this->authorizedModel]);

        return response($this->obj->update($request->all(), $userId));
    }

    /**
     * Display a member rank listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMemberRanksList(Request $request)
    {
        $this->authorize('memberRankListing', [$this->authorizedModel]);

        $this->validate($request, [
           'user_id' => 'required|integer|exists:users,id',
        ]);

        $param = array(
            'user_id' => $request->input('user_id'),
            'limit' => ($request->has('limit') ? $request->input('limit') : 0),
            'sort' => ($request->has('sort') ? $request->input('sort') :  'id'),
            'order' => ($request->has('order') ? $request->input('order') : 'asc'),
            'offset' => ($request->has('offset') ? $request->input('offset') :  0)
        );

        return response($this->obj->getMemberRanksList($param));
    }

    /**
     * Display the specified member rank resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMemberRanks($id)
    {
        return response($this->obj->getMemberRanks($id));
    }

    /**
     * Store a newly created member ranks resource in storage.
     *
     * @param MemberRanksUpdateRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function memberRanksStore(MemberRanksUpdateRequest $request)
    {
        $this->authorize('memberRankUpdate', [$this->authorizedModel]);

        return response($this->obj->memberRanksStore(
            $request
                ->only(['user_id', 'cw_id', 'enrollment_rank_id', 'highest_rank_id', 'case_reference_number'])));
    }

    /**
     * Display a member status listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMemberStatusList(Request $request)
    {
        $this->validate($request, [
           'user_id' => 'required|integer|exists:users,id',
        ]);

        $this->authorize('memberStatusListing', [$this->authorizedModel]);

        $param = array(
            'user_id' => $request->input('user_id'),
            'status_id' => ($request->has('status_id') ? $request->input('status_id') : NULL),
            'limit' => ($request->has('limit') ? $request->input('limit') : 0),
            'sort' => ($request->has('sort') ? $request->input('sort') :  'id'),
            'order' => ($request->has('order') ? $request->input('order') : 'asc'),
            'offset' => ($request->has('offset') ? $request->input('offset') :  0)
        );

        return response($this->obj->getMemberStatusList($param));
    }

    /**
     * Display the specified member status resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMemberStatus($id)
    {
        return response($this->obj->getMemberStatus($id));
    }

    /**
     * Store a newly created member status resource in storage.
     *
     * @param MemberStatusUpdateRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function memberStatusStore(MemberStatusUpdateRequest $request)
    {
        $this->authorize('memberStatusUpdate', [$this->authorizedModel]);

        return response($this->obj->memberStatusStore(
            $request->only([
                'user_id',
                'status_id',
                'effective_date',
                'bonus_payout_deferment',
                'cw_id',
                'case_reference_number',
                'reason_id'
            ])
        ));
    }

    /**
     * Display a member migrate listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMemberMigrateList(Request $request)
    {
        $this->authorize('memberMigrateListing', [$this->authorizedModel]);

        $this->validate($request, [
           'user_id' => 'required|integer|exists:users,id',
        ]);

        $param = array(
            'user_id' => $request->input('user_id'),
            'limit' => ($request->has('limit') ? $request->input('limit') : 0),
            'sort' => ($request->has('sort') ? $request->input('sort') :  'id'),
            'order' => ($request->has('order') ? $request->input('order') : 'asc'),
            'offset' => ($request->has('offset') ? $request->input('offset') :  0)
        );

        return response(
            $this->obj->getMemberMigrateList($param)
        );
    }

    /**
     * Display the specified member migrate resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMemberMigrate($id)
    {
        return response($this->obj->getMemberMigrate($id));
    }

    /**
     * Store a newly created member migrate resource in storage.
     *
     * @param MemberMigrateRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function memberMigrateStore(MemberMigrateRequest $request)
    {
        $this->authorize('memberMigrateUpdate', [$this->authorizedModel]);

        return response($this->obj
            ->memberMigrateStore($request->only([
                'user_id',
                'country_id',
                'cw_id',
                'case_reference_number',
                'reason_id'
            ]))
        );
    }

    /**
     * verify classic member given user id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyClassicMember(Request $request)
    {
        request()->validate([
            'national_id' => 'required'
        ]);

        $data = $this->obj->verifyClassicMember(
            $request->input('national_id')
        );

        return response($data);
    }

    /**
     * get placement network performance
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPlacementNetworkPerformance(Request $request)
    {
        request()->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:members,user_id',
                new DownlineUserValidation($this->memberTreeRepositoryObj, $request->input('user_id'))
            ]
        ]);

        $data = $this->obj->getPlacementNetworkPerformance(
            $request->input('user_id')
        );

        return response($data);
    }

    /**
     * get member campaign report
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getMemberCampaignReport(Request $request)
    {
        request()->validate([
            "campaign_id" => "required|integer|exists:campaigns,id",
            'user_id' => [
                'required',
                'integer',
                'exists:members,user_id',
                new DownlineUserValidation($this->memberTreeRepositoryObj, $request->input('user_id'))
            ]
        ]);

        return response($this->obj->memberCampaignReport(
            $request->input('campaign_id'),
            $request->input('user_id')
        ));
    }

    /**
     * get member dashboard
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getMemberDashboard()
    {
        return response($this->obj->memberDashboard());
    }

    /**
     * Member Email Verification
     *
     * @param MemberEmailVerificationRequest $request
     * @return \Illuminate\Support\Collection|mixed
     */
    public function memberEmailVerification(MemberEmailVerificationRequest $request)
    {
        if ($request->has('code'))
        {
            return $this->obj->validateEmail($request->all());
        }
        else
        {
            return $this->obj->generateEmailVerificationCode($request->all());
        }
    }
}

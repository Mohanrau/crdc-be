<?php
namespace App\Repositories\Campaigns;

use App\{
    Interfaces\Campaigns\EsacVoucherInterface,
    Helpers\Traits\AccessControl,
    Models\Locations\Country,
    Models\Campaigns\Campaign,
    Models\Campaigns\EsacPromotion,
    Models\Campaigns\EsacPromotionVoucherSubType,
    Models\Campaigns\EsacVoucher,
    Models\Campaigns\EsacVoucherType,
    Models\Campaigns\EsacVoucherSubType,
    Models\Masters\MasterData,
    Models\Users\User,
    Interfaces\Masters\MasterInterface,
    Interfaces\Members\MemberTreeInterface,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EsacVoucherRepository extends BaseRepository implements EsacVoucherInterface
{
    use AccessControl;

    private $countryObj, 
        $campaignObj, 
        $esacPromotionObj,
        $esacPromotionVoucherSubTypeObj,
        $esacVoucherTypeObj, 
        $esacVoucherSubTypeObj,
        $masterDataObj,
        $userObj,
        $masterInterfaceObj,
        $memberTreeRepositoryObj; 

    /**
     * EsacVoucherRepository constructor.
     *
     * @param EsacVoucher $model
     * @param Country $country
     * @param Campaign $campaign
     * @param EsacPromotion $esacPromotion
     * @param EsacPromotionVoucherSubType $esacPromotionVoucherSubType
     * @param EsacVoucherType $esacVoucherType
     * @param EsacVoucherSubType $esacVoucherSubType
     * @param MasterData $masterData
     * @param User $user
     * @param MasterInterface $masterInterface
     * @param MemberTreeInterface $memberTreeInterface
     */
    public function __construct(
        EsacVoucher $model,
        Country $country,
        Campaign $campaign,
        EsacPromotion $esacPromotion, 
        EsacPromotionVoucherSubType $esacPromotionVoucherSubType,
        EsacVoucherType $esacVoucherType,
        EsacVoucherSubType $esacVoucherSubType,
        MasterData $masterData,
        User $user,
        MasterInterface $masterInterface,
        MemberTreeInterface $memberTreeInterface
    )
    {
        parent::__construct($model);

        $this->countryObj = $country;

        $this->campaignObj = $campaign;

        $this->esacPromotionObj = $esacPromotion;
        
        $this->esacPromotionVoucherSubTypeObj = $esacPromotionVoucherSubType;
        
        $this->esacVoucherTypeObj = $esacVoucherType;
        
        $this->esacVoucherSubTypeObj = $esacVoucherSubType;
        
        $this->masterDataObj = $masterData;
        
        $this->userObj = $user;

        $this->masterInterfaceObj = $masterInterface;

        $this->memberTreeRepositoryObj = $memberTreeInterface;
    }
    
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int $campaignId
     * @param int $promotionId
     * @param int $voucherTypeId
     * @param int $voucherSubTypeId
     * @param string $voucherNumber
     * @param string $voucherStatus
     * @param string $voucherRemarks
     * @param int $voucherPeriodId
     * @param int $memberUserId
     * @param int $sponsorId
     * @param string $issuedDate
     * @param string $expiryDate
     * @param int $fromCampaignCwScheduleId
     * @param int $toCampaignCwScheduleId
     * @param string $fromCreatedAt
     * @param string $toCreatedAt
     * @param bool $forRedemption 
     * @param int $active
     * @param string $search
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEsacVouchersByFilters(
        int $countryId,
        int $campaignId = null,
        int $promotionId = null,
        int $voucherTypeId = null,
        int $voucherSubTypeId = null,
        string $voucherNumber = null,
        string $voucherStatus = null,
        string $voucherRemarks = null,
        int $voucherPeriodId = null,
        int $memberUserId = null,
        int $sponsorId = null,
        string $issuedDate = null,
        string $expiryDate = null,
        int $fromCampaignCwScheduleId = null,
        int $toCampaignCwScheduleId = null,
        string $fromCreatedAt = null,
        string $toCreatedAt = null,
        bool $forRedemption = null,
        int $active = null,
        string $search = null,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $downlineUserIdsWithLevel = [];

        $data = $this->modelObj
            ->with(['country', 'campaign', 'esacPromotion', 'esacVoucherType', 'esacVoucherSubType', 'voucherPeriod', 'user.member']);
        
        if (isset($countryId)) {
            $data = $data
                ->where('esac_vouchers.country_id', $countryId);
        }

        if (isset($campaignId)) {
            $data = $data
                ->where('esac_vouchers.campaign_id', $campaignId);
        }

        if (isset($promotionId)) {
            $data = $data
                ->where('esac_vouchers.promotion_id', $promotionId);
        }
        
        if (isset($voucherTypeId)) {
            $data = $data
                ->where('esac_vouchers.voucher_type_id', $voucherTypeId);
        }
        
        if (isset($voucherSubTypeId)) {
            $data = $data
                ->where('esac_vouchers.voucher_sub_type_id', $voucherSubTypeId);
        }

        if (isset($voucherNumber)) {
            $data = $data
                ->where('esac_vouchers.voucher_number', 'like', '%' . $voucherNumber . '%');
        }
        
        if (isset($voucherStatus)) {
            $data = $data
                ->where('esac_vouchers.voucher_status', 'like', '%' . $voucherStatus . '%');
        }

        if (isset($voucherRemarks)) {
            $data = $data
                ->where('esac_vouchers.voucher_remarks', 'like', '%' . $voucherRemarks . '%');
        }

        if (isset($voucherPeriodId)) {
            $data = $data
                ->where('esac_vouchers.voucher_period_id', $voucherPeriodId);
        }

        if (isset($memberUserId)) {
            $data = $data
                ->where('esac_vouchers.member_user_id', $memberUserId);
        }

        if (isset($sponsorId)) {
            $downlineUserIdsWithLevel = $this->memberTreeRepositoryObj
                ->getAllSponsorChildUserId($sponsorId, true);

            $downlineUserIdsWithLevel[$sponsorId] = 0; // include myself

            $downlineUserIds = array_keys($downlineUserIdsWithLevel);

            if (count($downlineUserIds) > 50000) {
                $data = $data->whereRaw('esac_vouchers.member_user_id in (' . implode(',', $downlineUserIds) . ')');
            }
            else {
                $data = $data->whereIn('esac_vouchers.member_user_id', $downlineUserIds);
            }
        }

        if (isset($searh)) {
            $data = $data
                ->where('esac_voucher_types.voucher_number', 'like', '%' . $searh . '%')
                ->orWhere('esac_voucher_types.voucher_remarks', 'like', '%' . $searh . '%');
        }

        if (isset($fromCampaignCwScheduleId)) {
            $campaignIdArray = $this->campaignObj
                ->where('from_cw_schedule_id', $fromCampaignCwScheduleId)
                ->pluck('id')
                ->toArray();

            $data = $data
                ->whereIn('campaign_id', $campaignIdArray);
        }

        if (isset($toCampaignCwScheduleId)) {
            $campaignIdArray = $this->campaignObj
                ->where('to_cw_schedule_id', $toCampaignCwScheduleId)
                ->pluck('id')
                ->toArray();

            $data = $data
                ->whereIn('campaign_id', $campaignIdArray);
        }

        if (isset($creationDateFrom)) {
            $data = $data
                ->where('date(created_at)','>=', $creationDateFrom);
        }

        if (isset($creationDateTo)) {
            $data = $data
                ->where('date(created_at)','<=', $creationDateTo);
        }

        if (isset($forRedemption) && $forRedemption === true) {
            $data = $data
                ->where('esac_vouchers.voucher_status', 'N')
                ->where('expiry_date', '>=', date('Y-m-d'));
        }
        
        if (isset($active)) {
            $data = $data
                ->where('esac_vouchers.active', $active);
        }
        
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );
        
        switch (strtolower($orderBy)) {
            case 'country.name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('countries', 'countries.id', '=', 'esac_vouchers.country_id', 'left outer')
                    ->orderBy('countries.name', $orderMethod);
                break;
            case 'campaign.name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('campaigns', 'campaigns.id', '=', 'esac_vouchers.campaign_id', 'left outer')
                    ->orderBy('campaigns.name', $orderMethod);
                break;
            case 'campaign.from_cw_schedule.cw_name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('campaigns', 'campaigns.id', '=', 'esac_vouchers.campaign_id', 'left outer')
                    ->join('cw_schedules', 'cw_schedules.id', '=', 'campaigns.from_cw_schedule_id', 'left outer')
                    ->orderBy('cw_schedules.cw_name', $orderMethod);
                break;
            case 'campaign.to_cw_schedule.cw_name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('campaigns', 'campaigns.id', '=', 'esac_vouchers.campaign_id', 'left outer')
                    ->join('cw_schedules', 'cw_schedules.id', '=', 'campaigns.to_cw_schedule_id', 'left outer')
                    ->orderBy('cw_schedules.cw_name', $orderMethod);
                break;
            case 'esac_voucher_type.name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('esac_voucher_types', 'esac_voucher_types.id', '=', 'esac_vouchers.voucher_type_id', 'left outer')
                    ->orderBy('esac_voucher_types.name', $orderMethod);
                break;
            case 'esac_voucher_sub_type.name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('esac_voucher_sub_types', 'esac_voucher_sub_types.id', '=', 'esac_vouchers.voucher_sub_type_id', 'left outer')
                    ->orderBy('esac_voucher_sub_types.name', $orderMethod);
                break;
            case 'user.old_member_id':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('users', 'users.id', '=', 'esac_vouchers.member_user_id', 'left outer')
                    ->orderBy('users.old_member_id', $orderMethod);
                break;
            case 'user.member.name':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('members', 'members.user_id', '=', 'esac_vouchers.member_user_id', 'left outer')
                    ->orderBy('members.name', $orderMethod);
                break;
            case 'last_modified_by':
                $data = $data
                    ->select('esac_vouchers.*')
                    ->join('users as created_by_user', 'created_by_user.id', '=', 'esac_vouchers.created_by', 'left outer')
                    ->join('users as updated_by_user', 'updated_by_user.id', '=', 'esac_vouchers.updated_by', 'left outer')
                    ->orderByRaw('COALESCE(updated_by_user.name, created_by_user.name) ' . $orderMethod);
                break;
            case 'last_modified_at':
                $data = $data->orderByRaw('COALESCE(updated_at, created_at) ' . $orderMethod);
                break;
            default:
                $data = $data->orderBy($orderBy, $orderMethod);
                break;
        }
        
        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        if (isset($sponsorId)) { //append level info
            foreach ($data as $voucher) {
                $voucher['sponsor_child_level'] = $downlineUserIdsWithLevel[$voucher->member_user_id];
            }
        }

        if (!($this->isUser('root') || $this->isUser('back_office'))) {
            $data = $data->map(function ($esacVoucher) {
                $country = $esacVoucher->country;
                $campaign = $esacVoucher->campaign;
                $esacPromotion = $esacVoucher->esacPromotion;
                $esacVoucherType = $esacVoucher->esacVoucherType;
                $esacVoucherSubType = $esacVoucher->esacVoucherSubType;
                $voucherPeriod = $esacVoucher->voucherPeriod;
                $user = $esacVoucher->user;
                return [
                    'id' => $esacVoucher->id,
                    'voucher_number' => $esacVoucher->voucher_number,
                    'voucher_value' => $esacVoucher->voucher_value,
                    'voucher_status' => $esacVoucher->voucher_status,
                    'voucher_remarks' => $esacVoucher->voucher_remarks,
                    'issued_date' => $esacVoucher->issued_date,
                    'expiry_date' => $esacVoucher->expiry_date,
                    'redeem_date' => $esacVoucher->redeem_date,
                    'max_purchase_qty' => $esacVoucher->max_purchase_qty,
                    'min_purchase_amount' => $esacVoucher->min_purchase_amount,
                    'sponsor_child_level' => $esacVoucher->sponsor_child_level,
                    'country_id' => $esacVoucher->country_id,
                    'campaign_id' => $esacVoucher->campaign_id,
                    'promotion_id' => $esacVoucher->promotion_id,
                    'voucher_type_id' => $esacVoucher->voucher_type_id,
                    'voucher_sub_type_id' => $esacVoucher->voucher_sub_type_id,
                    'voucher_period_id' => $esacVoucher->voucher_period_id,
                    'member_user_id' => $esacVoucher->member_user_id,
                    'country' => empty($country) ? null : [
                        'id' => $country->id,
                        'name' => $country->name,
                        'code' => $country->code,
                        'code_iso_2' => $country->code_iso_2
                    ],
                    'campaign' => empty($campaign) ? null : [
                        "id" => $campaign->id,
                        'name' => $campaign->name,
                        'report_group' => $campaign->report_group,
                        'country_id' => $campaign->country_id,
                        'from_cw_schedule_id' => $campaign->from_cw_schedule_id,
                        'to_cw_schedule_id' => $campaign->to_cw_schedule_id,
                        'from_cw_schedule' => empty($campaign->fromCwSchedule) ? null : [
                            'id' => $campaign->fromCwSchedule->id,
                            'cw_name' => $campaign->fromCwSchedule->cw_name,
                            'date_from' => $campaign->fromCwSchedule->date_from,
                            'date_to' => $campaign->fromCwSchedule->date_to,
                        ],
                        'to_cw_schedule' => empty($campaign->toCwSchedule) ? null : [
                            'id' => $campaign->toCwSchedule->id,
                            'cw_name' => $campaign->toCwSchedule->cw_name,
                            'date_from' => $campaign->toCwSchedule->date_from,
                            'date_to' => $campaign->toCwSchedule->date_to,
                        ]
                    ],
                    'esac_promotion' => empty($esacPromotion) ? null : [
                        'id' => $esacPromotion->id,
                        'taxable' => $esacPromotion->taxable,
                        'entitled_by' => $esacPromotion->entitled_by,
                        'max_purchase_qty' => $esacPromotion->max_purchase_qty
                    ],
                    'esac_voucher_type' => empty($esacVoucherType) ? null : [
                        'id' => $esacVoucherType->id,
                        'name' => $esacVoucherType->name,
                        'description' => $esacVoucherType->description
                    ],
                    'esac_voucher_sub_type' => empty($esacVoucherSubType) ? null : [
                        'id' => $esacVoucherSubType->id,
                        'name' => $esacVoucherSubType->name,
                        'description' => $esacVoucherSubType->description
                    ],
                    'voucher_period' => empty($voucherPeriod) ? null : [
                        'id' => $voucherPeriod->id,
                        'title' => $voucherPeriod->title
                    ],
                    'user' => empty($user) ? null : [
                        'id' => $user->id,
                        'name' => $user->name,
                        'old_member_id' => $user->old_member_id,
                        'member' => empty($user->member) ? null : [
                            'id' => $user->member->id,
                            'user_id' => $user->member->user_id,
                            'name' => $user->member->name,
                            'translated_name' => $user->member->translated_name
                        ]
                    ]
                ];
            });
        }
        
        return $totalRecords->merge(['data' => $data]);
    }
    
    /**
     * get one esac voucher by id
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $data = $this->modelObj
            ->with(['country', 'campaign', 'esacPromotion', 'esacVoucherType', 'esacVoucherSubType', 'voucherPeriod', 'user', 'user.member'])
            ->findOrFail($id);

        return $data;
    }
    
    /**
     * create or update esac voucher
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $esacVoucher = null;
        $errorBag = [];
        
        $esacVoucherData = [
            'country_id' => $data['country_id'],
            'campaign_id' => $data['campaign_id'],
            'promotion_id' => $data['promotion_id'],
            'voucher_type_id' => $data['voucher_type_id'],
            'voucher_sub_type_id' => $data['voucher_sub_type_id'],
            'voucher_number' => $data['voucher_number'],
            'voucher_value' => $data['voucher_value'],
            'voucher_status' => $data['voucher_status'],
            'voucher_remarks' => $data['voucher_remarks'],
            'voucher_period_id' => $data['voucher_period_id'],
            'member_user_id' => $data['member_user_id'],
            'issued_date' => $data['issued_date'],
            'expiry_date' => $data['expiry_date'],
            'active' => $data['active']
        ];        
        
        $esacPromotion = $this->esacPromotionObj
            ->findOrFail($data['promotion_id']);

        if($esacPromotion['entitled_by'] === 'P') {
            $esacVoucherData['max_purchase_qty'] = $esacPromotion['max_purchase_qty'];
        }
        else {
            $esacVoucherData['max_purchase_qty'] = 0;
        }
        
        $esacPromotionVoucherSubTypes = $this->esacPromotionVoucherSubTypeObj
            ->where('promotion_id', '=', $data['promotion_id'])
            ->where('voucher_sub_type_id', '=', $data['voucher_sub_type_id'])
            ->get();
        
        if (count($esacPromotionVoucherSubTypes) > 0) {
            $esacVoucherData['min_purchase_amount'] = $esacPromotionVoucherSubTypes[0]['min_purchase_amount'];
        }
        else {
            $esacVoucherData['min_purchase_amount'] = 0;
        }

        if (isset($data['id'])) {
            $esacVoucher = $this->modelObj->findOrFail($data['id']);
        
            $esacVoucher->update(array_merge(['updated_by' => Auth::id()], $esacVoucherData));
        }
        else
        {
            $esacVoucher = Auth::user()
                ->createdBy($this->modelObj)
                ->create($esacVoucherData);
        }

        return array_merge(['errors' => $errorBag],
            $this->show($esacVoucher['id'])->toArray()
        );
    }
    
    /**
     * update esac voucher status
     *
     * @param int $voucherId
     * @param string $voucherStatus
     */
    public function updateStatus(int $voucherId, string $voucherStatus)
    {   
        $esacVoucher = $this->modelObj
            ->findOrFail($voucherId);
        
        $updateData = [
            'voucher_status' => $voucherStatus,
            'redeem_date' => ($voucherStatus == 'P')? Carbon::now(): null,
            'updated_by' => Auth::id()
        ];

        return $esacVoucher->update($updateData);
    }
    
    /**
     * delete esac voucher
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id)
    {   
        $deleteStatus = $this->modelObj
            ->findOrFail($id)
            ->delete(); 

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }
}
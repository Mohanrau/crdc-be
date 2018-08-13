<?php
namespace App\Repositories\Campaigns;

use App\{
    Interfaces\Campaigns\CampaignInterface,
    Helpers\Traits\AccessControl,
    Models\Campaigns\Campaign,
    Models\Campaigns\CampaignRule,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;

class CampaignRepository extends BaseRepository implements CampaignInterface
{
    use AccessControl;
    
    private $campaignRuleObj;
    /**
     * CampaignRepository constructor.
     *
     * @param Campaign $model
     * @param CampaignRule $campaignRule
     */
    public function __construct(
        Campaign $model,
        CampaignRule $campaignRule
    )
    {
        parent::__construct($model);
        
        $this->campaignRuleObj = $campaignRule;
    }

    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param string|null $name
     * @param string|null $reportGroup
     * @param int|null $fromCwScheduleId
     * @param int|null $toCwScheduleId
     * @param string|null $search
     * @param int|null $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getCampaignsByFilters(
        int $countryId,
        string $name = null,
        string $reportGroup = null,
        int $fromCwScheduleId = null,
        int $toCwScheduleId = null,
        string $search = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'name',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with(['country', 'fromCwSchedule', 'toCwSchedule']);
        
        if (isset($countryId)) {
            $data = $data
                ->where('campaigns.country_id', $countryId);
        }

        if (isset($name)) {
            $data = $data
                ->where('campaigns.name', 'like', '%' . $name . '%');
        }

        if (isset($reportGroup)) {
            $data = $data
                ->where('campaigns.report_group', 'like', '%' . $reportGroup . '%');
        }
        
        if (isset($fromCwScheduleId)) {
            $data = $data
                ->where('campaigns.from_cw_schedule_id', $fromCwScheduleId);
        }
        
        if (isset($toCwScheduleId)) {
            $data = $data
                ->where('campaigns.to_cw_schedule_id', $toCwScheduleId);
        }
        
        if (isset($searh)) {
            $data = $data
                ->where('campaigns.name', 'like', '%' . $searh . '%')
                ->orWhere('campaigns.report_group', 'like', '%' . $reportGroup . '%');
        }

        if (isset($active)) {
            $data = $data
                ->where('campaigns.active', $active);
        }
        
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );
        
        switch (strtolower($orderBy)) {
            case 'country':
                $data = $data
                    ->join('countries', 'countries.id', '=', 'campaigns.country_id', 'left outer')
                    ->orderBy('countries.name', $orderMethod);
                break;
            case 'from_cw_schedule':
                $data = $data
                    ->join('cw_schedules', 'cw_schedules.id', '=', 'campaigns.from_cw_schedule_id', 'left outer')
                    ->orderBy('cw_schedules.cw_name', $orderMethod);
                break;
            case 'to_cw_schedule':
                $data = $data
                    ->join('cw_schedules', 'cw_schedules.id', '=', 'campaigns.to_cw_schedule_id', 'left outer')
                    ->orderBy('cw_schedules.cw_name', $orderMethod);
                break;
            default:
                $data = $data->orderBy($orderBy, $orderMethod);
                break;
        }
        
        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        if (!($this->isUser('root') || $this->isUser('back_office'))) {
            $data = $data->map(function ($campaign) {
                $country = $campaign->country;
                
                $fromCwSchedule = $campaign->fromCwSchedule;
                
                $toCwSchedule = $campaign->toCwSchedule;
                
                return [
                    "id" => $campaign->id,
                    'name' => $campaign->name,
                    'report_group' => $campaign->report_group,
                    'country_id' => $campaign->country_id,
                    'from_cw_schedule_id' => $campaign->from_cw_schedule_id,
                    'to_cw_schedule_id' => $campaign->to_cw_schedule_id,
                    'country' => empty($country) ? null : [
                        'id' => $country->id,
                        'name' => $country->name,
                        'code' => $country->code,
                        'code_iso_2' => $country->code_iso_2
                    ],
                    'from_cw_schedule' => empty($fromCwSchedule) ? null : [
                        'id' => $fromCwSchedule->id,
                        'cw_name' => $fromCwSchedule->cw_name,
                        'date_from' => $fromCwSchedule->date_from,
                        'date_to' => $fromCwSchedule->date_to,
                    ],
                    'to_cw_schedule' => empty($toCwSchedule) ? null : [
                        'id' => $toCwSchedule->id,
                        'cw_name' => $toCwSchedule->cw_name,
                        'date_from' => $toCwSchedule->date_from,
                        'date_to' => $toCwSchedule->date_to,
                    ]
                ];
            });
        }
        else {
            foreach($data as $campaign) {
                unset($campaign->custom_script);
            }
        }
        
        return $totalRecords->merge(['data' => $data]);
    }
    
    /**
     * get one campaign by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id)
    {
        $data = $this->modelObj
            ->with([
                'country', 'fromCwSchedule', 'toCwSchedule', 'campaignRules', 
                'campaignRules.campaignRuleQualifyTeamBonusRanks',
                'campaignRules.campaignRuleQualifyEnrollmentRanks',
                'campaignRules.voucherType', 
                'campaignRules.voucherSubType',
                'campaignRules.campaignRuleLocations', 
                'campaignRules.campaignRuleProductCategories', 
                'campaignRules.campaignRuleProducts', 
                'campaignRules.campaignRuleKittings', 
                'campaignRules.campaignRuleSaleTypes', 
                'campaignRules.campaignRuleTeamBonusRanks',
                'campaignRules.campaignRuleEnrollmentRanks'
            ])
            ->findOrFail($id);

        foreach($data->campaignRules as $campaignRuleItem) {
            $campaignRuleItem['qualify_team_bonus_ranks'] = $campaignRuleItem->campaignRuleQualifyTeamBonusRanks->pluck('id')->toArray();
            $campaignRuleItem['qualify_enrollment_ranks'] = $campaignRuleItem->campaignRuleQualifyEnrollmentRanks->pluck('id')->toArray();
            $campaignRuleItem['locations'] = $campaignRuleItem->campaignRuleLocations->pluck('id')->toArray();
            $campaignRuleItem['product_categories'] = $campaignRuleItem->campaignRuleProductCategories->pluck('id')->toArray();
            $campaignRuleItem['products'] = $campaignRuleItem->campaignRuleProducts->pluck('id')->toArray();
            $campaignRuleItem['kittings'] = $campaignRuleItem->campaignRuleKittings->pluck('id')->toArray();
            $campaignRuleItem['sale_types'] = $campaignRuleItem->campaignRuleSaleTypes->pluck('id')->toArray();
            $campaignRuleItem['team_bonus_ranks'] = $campaignRuleItem->campaignRuleTeamBonusRanks->pluck('id')->toArray();
            $campaignRuleItem['enrollment_ranks'] = $campaignRuleItem->campaignRuleEnrollmentRanks->pluck('id')->toArray();
        }
        return $data;
    }

    /**
     * delete campaign rule and child
     * 
     * @param array $deleteRuleIds
     */
    private function deleteCampaignRulesAndChild(array $deleteRuleIds) 
    {
        if (count($deleteRuleIds) > 0) {
            foreach($deleteRuleIds as $campaignRuleId) {
                $campaignRule = $this->campaignRuleObj
                    ->findOrFail($campaignRuleId);
                
                // remove qualify team bonus rank
                $teamBonusRankIds = $campaignRule
                    ->campaignRuleQualifyTeamBonusRanks()
                    ->pluck('team_bonus_rank_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleQualifyTeamBonusRanks()
                    ->detach($teamBonusRankIds);

                // remove qualify enrollment rank
                $enrollmentRankIds = $campaignRule
                    ->campaignRuleQualifyEnrollmentRanks()
                    ->pluck('enrollment_rank_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleQualifyEnrollmentRanks()
                    ->detach($enrollmentRankIds);
                
                // remove location
                $locationIds = $campaignRule
                    ->campaignRuleLocations()
                    ->pluck('location_id')
                    ->toArray();
                
                $campaignRule
                    ->campaignRuleLocations()
                    ->detach($locationIds);

                // remove product category
                $productCategoryIds = $campaignRule
                    ->campaignRuleProductCategories()
                    ->pluck('product_category_id')
                    ->toArray();
                
                $campaignRule
                    ->campaignRuleProductCategories()
                    ->detach($productCategoryIds);

                // remove product
                $productIds = $campaignRule
                    ->campaignRuleProducts()
                    ->pluck('product_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleProducts()
                    ->detach($productIds);

                // remove kitting
                $kittingIds = $campaignRule
                    ->campaignRuleKittings()
                    ->pluck('kitting_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleKittings()
                    ->detach($kittingIds);
                
                // remove sale type
                $saleTypeIds = $campaignRule
                    ->campaignRuleSaleTypes()
                    ->pluck('sale_type_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleSaleTypes()
                    ->detach($saleTypeIds);

                // remove team bonus rank
                $teamBonusRankIds = $campaignRule
                    ->campaignRuleTeamBonusRanks()
                    ->pluck('team_bonus_rank_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleTeamBonusRanks()
                    ->detach($teamBonusRankIds);

                // remove enrollment rank
                $enrollmentRankIds = $campaignRule
                    ->campaignRuleEnrollmentRanks()
                    ->pluck('enrollment_rank_id')
                    ->toArray();

                $campaignRule
                    ->campaignRuleEnrollmentRanks()
                    ->detach($enrollmentRankIds);
            }

            $this->campaignRuleObj
                ->whereIn('id', $deleteRuleIds)
                ->delete();
        }
    }

    /**
     * create or update campaign
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $campaign = null;
        $campaignRule = null;
        $campaignRuleIdMapping = [];
        $errorBag = [];
        
        $campaignData = [
            'country_id' => $data['country_id'],
            'name' => $data['name'],
            'report_group' => $data['report_group'],
            'from_cw_schedule_id' => $data['from_cw_schedule_id'],
            'to_cw_schedule_id' => $data['to_cw_schedule_id'],
            'custom_script' => $data['custom_script'],
            'active' => $data['active']
        ];
        
        if (isset($data['id'])) {
            $campaign = $this->modelObj->findOrFail($data['id']);
        
            $campaign->update(array_merge(['updated_by' => Auth::id()], $campaignData));
        }
        else
        {
            $campaign = Auth::user()
                ->createdBy($this->modelObj)
                ->create($campaignData);
        }

        // get all existing child ids for later deletion
        $deleteRuleIds = $this->campaignRuleObj
            ->where('campaign_id', $campaign['id'])
            ->pluck('id')
            ->toArray();
        
        foreach ($data['campaign_rules'] as $campaignRuleData) {
            // sync rule
            $campaignRuleDataNew = [
                'parent_id' => null,
                'campaign_id' => $campaign['id'],
                'name' => $campaignRuleData['name'],
                'report_title' => $campaignRuleData['report_title'],
                'qualify_member_status' => $campaignRuleData['qualify_member_status'],
                'sale_item_quantity' => $campaignRuleData['sale_item_quantity'],
                'team_bonus_rank_quantity' => $campaignRuleData['team_bonus_rank_quantity'],
                'enrollment_rank_quantity' => $campaignRuleData['enrollment_rank_quantity'],
                'from_sale_item_level' => $campaignRuleData['from_sale_item_level'],
                'to_sale_item_level' => $campaignRuleData['to_sale_item_level'],
                'from_team_bonus_rank_level' => $campaignRuleData['from_team_bonus_rank_level'],
                'to_team_bonus_rank_level' => $campaignRuleData['to_team_bonus_rank_level'],
                'from_enrollment_rank_level' => $campaignRuleData['from_enrollment_rank_level'],
                'to_enrollment_rank_level' => $campaignRuleData['to_enrollment_rank_level'],
                'from_cv' => $campaignRuleData['from_cv'],
                'to_cv' => $campaignRuleData['to_cv'],
                'point' => $campaignRuleData['point'],
                'point_value' => $campaignRuleData['point_value'],
                'point_value_multiplier' => $campaignRuleData['point_value_multiplier'],
                'min_point_value' => $campaignRuleData['min_point_value'],
                'max_point_value' => $campaignRuleData['max_point_value'],
                'voucher' => $campaignRuleData['voucher'],
                'voucher_type_id' => $campaignRuleData['voucher_type_id'],
                'voucher_sub_type_id' => $campaignRuleData['voucher_sub_type_id'],
                'voucher_value' => $campaignRuleData['voucher_value'],
                'voucher_value_multiplier' => $campaignRuleData['voucher_value_multiplier'],
                'min_voucher_value' => $campaignRuleData['min_voucher_value'],
                'max_voucher_value' => $campaignRuleData['max_voucher_value'],
                'ewallet_money' => $campaignRuleData['ewallet_money'],
                'ewallet_money_value' => $campaignRuleData['ewallet_money_value'],
                'ewallet_money_value_multiplier' => $campaignRuleData['ewallet_money_value_multiplier'],
                'min_ewallet_money_value' => $campaignRuleData['min_ewallet_money_value'],
                'max_ewallet_money_value' => $campaignRuleData['max_ewallet_money_value']
            ];

            if ($campaignRuleData['id'] > 0) {
                $campaignRule = $this->campaignRuleObj
                    ->findOrFail($campaignRuleData['id']);
                
                $campaignRule->update($campaignRuleDataNew);

                $spliceIndex = array_search($campaignRuleData['id'], $deleteRuleIds);

                array_splice($deleteRuleIds, $spliceIndex, 1);
            }
            else {
                $campaignRule = $this->campaignRuleObj
                    ->create($campaignRuleDataNew);
            }

            //store mapping for later update
            $campaignRuleIdMapping[$campaignRuleData['id']] = $campaignRule['id'];
            
            //sync qualify team bonus rank
            $campaignRule->campaignRuleQualifyTeamBonusRanks()
                ->sync($campaignRuleData['qualify_team_bonus_ranks']);

            //sync qualify enrollment rank
            $campaignRule->campaignRuleQualifyEnrollmentRanks()
                ->sync($campaignRuleData['qualify_enrollment_ranks']);

            //sync rule location
            $campaignRule->campaignRuleLocations()
                ->sync($campaignRuleData['locations']);
            
            //sync rule product category
            $campaignRule->campaignRuleProductCategories()
                ->sync($campaignRuleData['product_categories']);

            //sync rule product
            $campaignRule->campaignRuleProducts()
                ->sync($campaignRuleData['products']);

            //sync rule kitting
            $campaignRule->campaignRuleKittings()
                ->sync($campaignRuleData['kittings']);

            //sync rule sale types
            $campaignRule->campaignRuleSaleTypes()
                ->sync($campaignRuleData['sale_types']);

            //sync team bonus rank
            $campaignRule->campaignRuleTeamBonusRanks()
                ->sync($campaignRuleData['team_bonus_ranks']);

            //sync enrollment rank
            $campaignRule->campaignRuleEnrollmentRanks()
                ->sync($campaignRuleData['enrollment_ranks']);
        }

        // delete campaign rule
        $this->deleteCampaignRulesAndChild($deleteRuleIds);

        // update campaign rule parent child relation
        foreach ($data['campaign_rules'] as $campaignRuleData) {
            if ($campaignRuleData['parent_id'] != null && isset($campaignRuleIdMapping[$campaignRuleData['parent_id']])) {
                $campaignRule = $this->campaignRuleObj
                    ->findOrFail($campaignRuleIdMapping[$campaignRuleData['id']]);
                
                $campaignRule->update(['parent_id' => $campaignRuleIdMapping[$campaignRuleData['parent_id']]]);
            }
        }

        // return result json
        return array_merge(['errors' => $errorBag],
            $this->show($campaign['id'])->toArray()
        );
    }

    /**
     * delete campaign
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id)
    {
        $campaign = $this->modelObj
            ->findOrFail($id);

        $deleteRuleIds = $this->campaignRuleObj
            ->where('campaign_id', $campaign['id'])
            ->pluck('id')
            ->toArray();
            
        $this->deleteCampaignRulesAndChild($deleteRuleIds);
        
        $deleteStatus = $campaign->delete(); 

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }
}
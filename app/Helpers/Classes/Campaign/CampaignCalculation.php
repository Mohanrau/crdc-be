<?php
namespace App\Helpers\Classes\Campaign;

use App\Helpers\Classes\MemberNetworkTree;
use App\Models\Bonus\TeamBonusRank;
use App\Models\Campaigns\Campaign;
use App\Models\Campaigns\CampaignRule;
use App\Models\General\CWSchedule;
use App\Models\Masters\Master;
use App\Models\Masters\MasterData;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleCancellation;
use App\Models\Sales\SaleProduct;
use Illuminate\Support\Facades\DB;

class CampaignCalculation
{
    // All Sales informations
    private $productsLocations;
    private $productCategories;
    private $products;
    private $salesTypes;
    private $teamRanks;
    private $completedOrderStatusId; // master id for completed sales

    private $campaigns;
    private $memberTree;
    private $cwSchedule;
    protected $cwScheduleObj, $memberNetworkTreeHelper, $campaignObj, $campaignRuleObj, $masterDataObj,
            $teamBonusRankObj, $saleCancellationObj, $saleProduct;

    public function __construct(
        Campaign $campaign,
        MemberNetworkTree $memberNetworkTree,
        CWSchedule $CWSchedule,
        CampaignRule $campaignRule,
        MasterData $masterData,
        TeamBonusRank $teamBonusRank,
        SaleCancellation $saleCancellation,
        SaleProduct $saleProduct
    )
    {
        $this->cwScheduleObj = $CWSchedule;
        $this->memberNetworkTreeHelper = $memberNetworkTree;
        $this->campaignObj = $campaign;
        $this->campaignRuleObj = $campaignRule;
        $this->masterDataObj = $masterData;
        $this->teamBonusRankObj = $teamBonusRank;
        $this->saleCancellationObj = $saleCancellation;
        $this->saleProduct = $saleProduct;
    }

    public function process($cwId = '')
    {
        ini_set('memory_limit, -1');
        //Gets all the campaign that happened between the cw_id
        $campaignIds = $this->campaignObj->where([
            ['from_cw_schedule_id', '<=', $cwId],
            ['to_cw_schedule_id', '>=', $cwId]
        ])->get()->pluck('id')->toArray();

        $this->memberTree = null;

        $this->cwSchedule = $this->cwScheduleObj->find($cwId);

        $this->completedOrderStatusId = $this->masterDataObj->where('title', 'COMPLETED')
            ->whereHas('master', function($masterQuery){
                $masterQuery->where('key', 'sale_order_status');
            })->get()->first()->id;

        $this->teamRanks = $this->teamBonusRankObj->orderBy('rank_order', 'asc')->get()->keyBy('id');

        //instantiate all sales by locations

        //Gets all the rules from the campaign
        $this->checkRules($campaignIds);
    }

    /**
     * @param $campaignIds
     * @return bool
     */
    public function checkRules($campaignIds)
    {
        // validate campaign ids
        if(!is_array($campaignIds) || empty($campaignIds)){
            return false;
        }

        //we will have to loop through all the campaign with the campaign rules id
        foreach($campaignIds as $id){
            //get all the campaign rules id applied to this campaign id
            $campaignRules = $this->campaignRuleObj->where('campaign_id', $id)
                ->with(['campaignRuleLocations','campaignRuleProductCategories','campaignRuleProducts',
                    'campaignRuleSaleTypes','campaignRuleTeamBonusRanks'])->get();

            //continue to next rule if there is no rule for current campaign id
            if($campaignRules->isEmpty()){
                continue;
            }

            //let's run through all the rules
            $campaignRules->each(function($rule){
                $memberTree = $this->memberNetworkTreeHelper->initiateMemberTree($this->cwSchedule->id, 'bonus_member_tree_details');
                $memberTree = $memberTree->getMemberTree();
                $markedMembers = collect();

                $fromLevel = $rule->from_level;
                $toLevel = $rule->to_level;

                //location rule
                $locationRule = $rule->campaignRuleLocations->mapWithKeys(function($rule){
                    return [$rule->location_id => $rule->quantity];
                });

                $locationIds = $locationRule->keys();
                $locationProductQty = $locationRule->sum(); // total products from location needed

                //category rule
                /*
                $productCategoryRule = $rule->campaignRuleProductCategories->mapWithKeys(function($rule){
                    return [$rule->product_category_id => $rule->quantity];
                }); // [location_id => quantity]

                $productCategoryIds = $productCategoryRule->keys();
                $productCategory = $productCategoryRule->sum(); // total products from location needed
                */

                //sales type rule
                $saleTypesRule = $rule->campaignRuleSaleTypes->mapWithKeys(function($rule){
                    return [$rule->sale_type_id => $rule->quantity];
                });

                $saleTypeIds = $saleTypesRule->keys();
                $saleTypeQty = $saleTypesRule->sum();

                //product/kitting rule
                $looseProductRule = collect();
                $kittingProductRule = collect();

                $rule->campaignRuleProducts->each(function($rule) use(&$looseProductRule,
                    &$kittingProductRule){
                    if($rule->product_id){
                        $looseProductRule->put($rule->product_id, $rule->quantity);
                    }else{
                        //is a kitting
                        $kittingProductRule->put($rule->kitting_id, $rule->quantity);
                    }
                });

                $productIds = $looseProductRule->keys();
                $kittingIds = $kittingProductRule->keys();
                $productsTypeQty = $looseProductRule->sum() + $kittingProductRule->sum();

                $memberRanksRule = $rule->campaignRuleTeamBonusRanks->mapWithKeys(function($rule){
                    return [$rule->rankId => $rule->exact_match]; //id => exact match or not
                });

                $memberRanksQty = $rule->campaignRuleTeamBonusRanks->sum(function($rule){
                    return $rule->quantity;
                });

                //gets all the cancelled sales
                $cancelledSalesIds = $this->saleCancellationObj->select('sale_id')->where('cw_id', $this->cwSchedule->id)
                    ->whereNotNull('sale_id')->get()->toArray();

                $saleProducts = $this->saleProduct->whereHas('sale',
                    function($saleQuery) use($cancelledSalesIds, $locationIds){

                    $saleQuery->where('cw_id', $this->cwSchedule->id)
                        ->where('is_product_exchange', 0)
                        ->where('sales.order_status_id', $this->completedOrderStatusId);

                    //filter out cancelled sales
                    if($cancelledSalesIds){
                        $saleQuery->whereNotIn('id', $cancelledSalesIds);
                    }

                    //filter out locations
                    if(count($locationIds)){
                        $saleQuery->whereIn('transaction_location_id', $locationIds);
                    }
                })->where(function($query){
                    $query->where('mapping_model', '<>', 'sales_promotion_free_items_clone')
                        ->orWhereNull('mapping_model');
                })->with('sale');

                $saleProducts = $saleProducts->get();

                //first level filtering, remove all duplicates from kitting leaving only one avail
                $saleProducts = $saleProducts->unique(function($product){
                    if($product->mapping_id){
                        $product->load('getMappedModel.kitting');
                        return 'kitting'.$product->mapping_id;
                    }else{
                        $product->load('getMappedModel.product');
                        return $product->product_id;
                    }
                });


                $totalProductsNeeded = collect([$locationProductQty, $saleTypeQty, $productsTypeQty, $memberRanksQty])
                                        ->max();
                $minCv = $rule->from_cv;
                $maxCv = $rule->to_cv;

                //we will do the product level filtering here
                $eligibleCampaignProducts = collect(); // all products level that is eligible for the campaign

                $saleProducts->each(function($saleProduct) use(&$eligibleCampaignProducts,
                    $productIds, $kittingIds){
                    /**
                     * since product id and kitting id is the original id,
                     * we have no choice but to find from the base table
                     * */
                    // loose product
                    if($saleProduct->mapping_id == null){
                        if($productIds && $productIds->has($saleProduct->getMappedModel->product->id)){
                            // has product id specified, we must make sure that this product exists in the list
                            $eligibleCampaignProducts->put( $saleProduct->sale->user_id, [
                                'qty' => $saleProduct->quantity,
                                'cv' => $saleProduct->eligible_cv
                                ] );
                        }elseif(!$productIds && !$kittingIds){
                            //if both are not filled, thats mean we are taking everything
                            $eligibleCampaignProducts->put( $saleProduct->sale->user_id, [
                                'qty' => $saleProduct->quantity,
                                'cv' => $saleProduct->eligible_cv
                            ] );
                        }
                    }else{
                        // this must be a kitting
                        if($kittingIds && $kittingIds->has($saleProduct->getMappedModel->kitting->id)){
                            $eligibleCampaignProducts->put( $saleProduct->sale->user_id, [
                                'qty' => $saleProduct->getMappedModel->quantity,
                                'cv' => $saleProduct->getMappedModel->eligible_cv
                            ] );
                        }elseif(!$productIds && !$kittingIds){
                            $eligibleCampaignProducts->put( $saleProduct->sale->user_id, [
                                'qty' => $saleProduct->getMappedModel->quantity,
                                'cv' => $saleProduct->getMappedModel->eligible_cv
                            ] );
                        }
                    }
                });

                $eligibleCampaignProducts->each(function($info, $userId) use(&$markedMembers, &$memberTree,
                    $fromLevel, $toLevel, $memberRanksRule){

                    //before assigning the info, we need to ensure that the member who purchase is at the right rank
                    $isEligible = false;
                    $memberRanksRule->each(function($exactMatch, $rankId) use(&$isEligible, $memberTree, $userId){
                        $memberEffectiveId = $memberTree->get($userId)->effective_rank_id;
                        if($exactMatch && $memberEffectiveId == $rankId){
                            $isEligible = true;
                            return false;
                        }elseif($this->teamRanks->get($memberEffectiveId)->rank_order >=
                            $this->teamRanks->get($rankId)->rank_order){
                            $isEligible = true;
                            return false;
                        }
                    });

                    if($isEligible){
                        $this->assignInfo($memberTree->get($userId), 0, $fromLevel, $toLevel, $markedMembers,
                            $info['cv'], $info['qty']);
                    }
                });

                $markedMembers = $markedMembers->unique();

                $markedMembers->each(function($memberId) use($memberTree, $totalProductsNeeded, $minCv, $maxCv){
                    //we will check if the members is eligible or not.
                    $info = $memberTree->get($memberId)->campaignInfo;
                    if($info['qty'] > $totalProductsNeeded && $info['cv'] >= $minCv && $info['cv'] <= $maxCv){
                        //@todo we will give points to this user/put in temp table until the final run
                    }
                });

            });
        }
    }

    /**
     * @param $member
     * @param $currentLevel
     * @param $fromLevel
     * @param $toLevel
     * @param $markedMember
     * @param int $cv
     * @param $qty
     */
    public function assignInfo($member, $currentLevel, $fromLevel, $toLevel, $markedMember, $cv, $qty)
    {
        //check if we need to mark the user
        if($currentLevel >= $fromLevel && ($currentLevel <= $toLevel || $toLevel == 0)){
            $markedMember[] = $member->user_id;

            // only add to marked user
            $member->campaignInfo['cv'] = (!isset($member->campaignInfo['cv'])) ?
                $cv : $cv + $member->campaignInfo['cv']; // add up the count

            $member->campaignInfo['qty'] = (!isset($member->campaignInfo['qty'])) ?
                $qty : $qty + $member->campaignInfo['qty']; // add up the count
        }

        if($member->sponsor_parent_user_id && ++$currentLevel >= $toLevel || !$currentLevel ){
            $this->assignInfo($member->sponsorParent, $currentLevel, $fromLevel, $toLevel, $markedMember, $cv, $qty);
        }

        return;
    }
}
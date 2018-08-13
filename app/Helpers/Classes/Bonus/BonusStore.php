<?php
namespace App\Helpers\Classes\Bonus;

use App\Models\Bonus\BonusMemberTreeDetails;
use App\Models\Bonus\BonusMentorBonusDetails;
use App\Models\Bonus\BonusQuarterlyDividendDetails;
use App\Models\Bonus\BonusSummary;
use App\Models\Bonus\BonusTeamBonusDetails;
use App\Models\Bonus\BonusWelcomeBonusDetails;
use App\Models\Members\MemberTree;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * This class is used to store all bonus information of the CW
 */

class BonusStore
{
    private $memberTree;
    private $cwId;
    private $bringForwardInfo; //gcv bring forward information for each members

    public function __construct($memberTree, $cwId, $bringForwardInfo)
    {
        $this->memberTree = $memberTree;
        $this->cwId = $cwId;
        $this->bringForwardInfo = $bringForwardInfo;
        $this->storeBonus();
    }


    public function storeBonus()
    {
        $bonusSummaryIds = collect();

        DB::beginTransaction();
        $this->memberTree->each(function( $member ) use($bonusSummaryIds){
            $this->storeMemberTreeDetails($member); // do a clone of the members
            $bonusSummaryId = $this->storeBonusSummary($member);
            $bonusSummaryIds->put($member->user_id, $bonusSummaryId);
        });
        DB::commit();

        // before anything else, we should remove all the previous record in db because it might not be
        // accurate when there is a changes in the tree

        DB::beginTransaction();
        $bonusSummaryIds->chunk(2000)->each(function($bonusSummaryIdsChunk){
            $ids = $bonusSummaryIdsChunk->toArray();
            // we only remove those that has been redeemed
            BonusWelcomeBonusDetails::where('is_redeemed', 0)->whereIn('bonuses_summary_id', $ids)->delete();
            BonusTeamBonusDetails::whereIn('bonuses_summary_id', $ids)->delete();
            BonusMentorBonusDetails::whereIn('bonuses_summary_id', $ids)->delete();
        });
        DB::commit();

        DB::beginTransaction();
        //after clearing, we will do a fresh insert
        $this->memberTree->each(function( $member ) use(&$bonusSummaryIds){
            $this->storeWelcomeBonusDetails($member, $bonusSummaryIds->get($member->user_id));
            $this->storeTeamBonusDetails($member, $bonusSummaryIds->get($member->user_id));
            $this->storeMentorBonusDetails($member, $bonusSummaryIds->get($member->user_id));
        });
        DB::commit();
    }

    /**
     * Store a snapshot of the member tree with additional info
     *
     * @param $member
     */
    private function storeMemberTreeDetails($member)
    {

        $bonusMemberTreeDetails = BonusMemberTreeDetails::firstOrNew(['cw_id' => $this->cwId,
            'user_id' => $member->user_id]);
        $bonusMemberTreeDetails->sponsor_parent_user_id = $member->sponsor_parent_user_id;
        $bonusMemberTreeDetails->placement_parent_user_id = $member->placement_parent_user_id;
        $bonusMemberTreeDetails->sponsor_depth_level = $member->sponsor_depth_level;
        $bonusMemberTreeDetails->placement_depth_level = $member->placement_depth_level;
        $bonusMemberTreeDetails->placement_position = $member->placement_position;
        $bonusMemberTreeDetails->personal_sales_cv = $member->personalSalesCv;
        $bonusMemberTreeDetails->member_sales_cv = $member->membersCv; //cv passed by member/premier member
        $bonusMemberTreeDetails->is_active_brand_ambassador = ($member->isActive) ? 1 : 0 ;
        $bonusMemberTreeDetails->is_tri_formation = $member->isTriformation;

        $bonusMemberTreeDetails->total_ba_left = $member->totalBALeft;
        $bonusMemberTreeDetails->total_ba_right = $member->totalBARight;
        $bonusMemberTreeDetails->total_unique_line_left = $member->totalUniqueLeftActiveBA;
        $bonusMemberTreeDetails->total_unique_line_right = $member->totalUniqueRightActiveBA;
        $bonusMemberTreeDetails->total_active_ba_left = $member->totalLeftActiveBA;
        $bonusMemberTreeDetails->total_active_ba_right = $member->totalRightActiveBA;
        $bonusMemberTreeDetails->total_new_ba_left = $member->totalNewBALeft;
        $bonusMemberTreeDetails->total_new_ba_right = $member->totalNewBARight;
        $bonusMemberTreeDetails->total_new_ba = $member->totalNewBA;
        $bonusMemberTreeDetails->is_new_ba = $member->isNewBA;
        $bonusMemberTreeDetails->total_downline = $member->totalDownlines;
        $bonusMemberTreeDetails->total_direct_downline = count($member->firstLevelSponsorChild);
        $bonusMemberTreeDetails->total_direct_downline_active_ba = $member->totalDirectDownlineActiveBA;

        $lineDetails = $member->sponsorLineRankDetails;
        $bonusMemberTreeDetails->total_sponsor_unique_line_1BA = $lineDetails['total_sponsor_unique_line_1_BA'][2];
        $bonusMemberTreeDetails->total_sponsor_unique_line_1SD = $lineDetails['total_sponsor_unique_line_1_SD'][2];
        $bonusMemberTreeDetails->total_sponsor_unique_line_2SD = $lineDetails['total_sponsor_unique_line_2_SD'][2];
        $bonusMemberTreeDetails->total_sponsor_unique_line_1RD = $lineDetails['total_sponsor_unique_line_1_RD'][2];
        $bonusMemberTreeDetails->total_sponsor_unique_line_2RD = $lineDetails['total_sponsor_unique_line_2_RD'][2];
        $bonusMemberTreeDetails->total_sponsor_unique_line_1ED = $lineDetails['total_sponsor_unique_line_1_ED'][2];
        $bonusMemberTreeDetails->total_sponsor_unique_line_2ED = $lineDetails['total_sponsor_unique_line_2_ED'][2];

        $bonusMemberTreeDetails->left_gcv = $member->accumulatedLeftCv;
        $bonusMemberTreeDetails->right_gcv = $member->accumulatedRightCv;

        $bonusMemberTreeDetails->save();
    }

    private function storeBonusSummary($member){
        $bonusSummary = BonusSummary::firstOrNew(['cw_id' => $this->cwId, 'user_id' => $member->user_id]);

        $achievedRankId = null; // this is for member/premier member
        $enrollmentRankMember = [5, 6]; // 5 = premier member, 6 = member
        if(!in_array($member->enrollment_rank_id, $enrollmentRankMember)){
            $achievedRankId = ($member->achievedRankId) ? $member->achievedRankId : 2; //default is BA
        }

        $bonusSummary->country_id = $member->country_id;
        $bonusSummary->statement_date = Carbon::now()->format('Y-m-d');
        $bonusSummary->tax_company_name = ''; //@todo TBC
        $bonusSummary->tax_no = ''; //@todo TBC
        $bonusSummary->tax_type = ''; //@todo TBC
        $bonusSummary->tax_rate = ''; //@todo TBC
        $bonusSummary->highest_rank_id = $member->highest_rank_id;
        $bonusSummary->effective_rank_id = $achievedRankId;
        $bonusSummary->enrollment_rank_id = $member->enrollment_rank_id;
        $bonusSummary->address_data = null; //@todo TBC
        $bonusSummary->welcome_bonus = $member->totalWelcomeBonus;
        $bonusSummary->team_bonus = $member->totalTeamBonus;
        $bonusSummary->team_bonus_diluted = $member->totalTeamBonusDiluted;
        $bonusSummary->mentor_bonus = $member->totalMentorBonus;
        $bonusSummary->mentor_bonus_diluted = $member->totalMentorBonusDiluted;
        $bonusSummary->quarterly_dividend = $member->totalQuarterlyDividend;
        $bonusSummary->incentive = $member->totalTeamBonus; //@todo TBC
        $bonusSummary->total_gross_bonus = $member->totalWelcomeBonus +
            $member->totalTeamBonusDiluted + $member->totalMentorBonusDiluted; //@todo add in quarterly bonuses
        $bonusSummary->default_currency_id = $member->default_currency_id;
        $bonusSummary->currency_rate = $member->currencyConversionRate;
        $bonusSummary->total_gross_bonus_local_amount = $bonusSummary->total_gross_bonus /
            $member->currencyConversionRate; //@todo TBC
        $bonusSummary->total_tax_amount = 0; //@todo TBC // is local amount
        $bonusSummary->total_net_bonus_payable = $bonusSummary->total_gross_bonus_local_amount
            - $bonusSummary->total_tax_amount; // is local amount
        $bonusSummary->diluted_percentage = $member->dilutionAmount;

        $bonusSummary->save();

        return $bonusSummary->id;
    }

    private function storeWelcomeBonusDetails($member, $bonusSummaryId)
    {
        $details = collect($member->welcomeBonusDetails);

        if($details->count()){
            $details->each(function($item, $key) use($bonusSummaryId, $member){

                //if the records is already in and redeemed, we should not enter a new record.

                $checkExists = BonusWelcomeBonusDetails::where([
                    ['bonuses_summary_id', '=', $bonusSummaryId],
                    ['sponsor_child_user_id', '=', $item['sponsor_child_user_id']],
                    ['sponsor_child_depth_level' , '=', $item['sponsor_child_depth_level']]
                    ])->first();

                if($checkExists){
                    return true; // move to the next one
                }

                $wbDetails = new BonusWelcomeBonusDetails;
                $wbDetails->bonuses_summary_id = $bonusSummaryId;
                $wbDetails->sponsor_child_user_id = $item['sponsor_child_user_id'];
                $wbDetails->sponsor_child_depth_level = $item['sponsor_child_depth_level'];
                $wbDetails->join_date = $item['join_date'];
                $wbDetails->total_local_amount = $item['total_local_amount'];
                $wbDetails->total_local_amount_currency = $item['total_local_amount_currency'];
                $wbDetails->total_amount = $item['total_amount'];
                $wbDetails->total_amount_currency = $item['total_amount_currency'];
                $wbDetails->total_usd_amount = $item['total_usd_amount'];
                $wbDetails->nett_usd_amount = $item['total_nett_usd_amount'];
                $wbDetails->calculation_date = $item['transaction_date'];

                $wbDetails->save();
            });
        }

    }

    private function storeTeamBonusDetails($member, $bonusSummaryId)
    {
        //if this member is inactive, maximum of 2000 can be bring forward OR powerleg - payleg whichever is lower
        if(!$member->isActive){
            $leftGcv = 0;
            $rightGcv = 0;
            $leftGcvBringForward = 0;
            $rightGcvBringForward = 0;
            $gcvBringOver = 0;

            $paylegPosition = 1; // default to left


            //there wont be any ops because the member is not active.(buying lesser than 60 cv)

            if(isset($member->placementLeft)){
                $leftGcv = $member->placementLeft->totalCv;
            }

            if(isset($member->placementRight)){
                $rightGcv = $member->placementRight->totalCv;
            }

            //take this chance to add in the carry forward value
            if(isset($this->bringForwardInfo[$member->user_id][1])){
                $leftGcvBringForward = $this->bringForwardInfo[$member->user_id][1]->gcv_bring_over;
            }

            if(isset($this->bringForwardInfo[$member->user_id][2])) {
                $rightGcvBringForward = $this->bringForwardInfo[$member->user_id][2]->gcv_bring_over;
            }

            $payLegFlushedCv = 0;
            $finalLeftCv = $leftGcv + $leftGcvBringForward;
            $finalRightCv = $rightGcv + $rightGcvBringForward;

            if($finalLeftCv > $finalRightCv){
                $payLegFlushedCv = $finalRightCv;
                $paylegPosition = 2; // payleg should be on the left
            }else{
                $payLegFlushedCv = $finalLeftCv;
            }

            // For the GCV which is less that 60, it can still bring over despite being a pay-leg
            $leftBringOver = 0;
            $rightBringOver = 0;

            if($finalLeftCv < 60 && $paylegPosition == 1){
                $leftBringOver = $finalLeftCv;
                $payLegFlushedCv = 0;
            }elseif($finalRightCv < 60 && $paylegPosition == 2){
                $rightBringOver = $finalRightCv;
                $payLegFlushedCv = 0;
            }

            $gcvBringOver = ($paylegPosition === 2) ? $finalLeftCv : $finalRightCv;

            //we will only allow 2k max or powerleg - payleg gcv whichever is lower
            $flushedGcv = 0;
            if($gcvBringOver > 2000){
                $flushedGcv = $gcvBringOver - 2000;
                $gcvBringOver = ($gcvBringOver >= 2000) ? 2000 : $gcvBringOver;
            }


            //insert left
            if(isset($member->placementLeft) && $member->placementLeft->user_id != null){
                $tbDetails = new BonusTeamBonusDetails;
                $tbDetails->bonuses_summary_id = $bonusSummaryId;
                $tbDetails->placement_child_user_id = $member->placementLeft->user_id;
                $tbDetails->gcv = $leftGcv;
                $tbDetails->optimising_personal_sales = 0;
                $tbDetails->gcv_calculation = 0;
                $tbDetails->gcv_bring_forward = $leftGcvBringForward;
                $tbDetails->gcv_bring_forward_position = 1;
                $tbDetails->gcv_leg_group = ($paylegPosition == 1) ? 'PAY' : 'POWER';
                $tbDetails->gcv_flush = ($paylegPosition == 1) ? $payLegFlushedCv : $flushedGcv;
                $tbDetails->gcv_bring_over = ($paylegPosition == 1) ? $leftBringOver : $gcvBringOver;
                $tbDetails->team_bonus_percentage = 0;
                $tbDetails->team_bonus = 0;
                $tbDetails->save();
            }


            if(isset($member->placementRight) && $member->placementRight->user_id != null){
                //insert right
                $tbDetails = new BonusTeamBonusDetails;
                $tbDetails->bonuses_summary_id = $bonusSummaryId;
                $tbDetails->placement_child_user_id = $member->placementRight->user_id;
                $tbDetails->gcv = $rightGcv;
                $tbDetails->optimising_personal_sales = 0;
                $tbDetails->gcv_calculation = 0;
                $tbDetails->gcv_bring_forward = $rightGcvBringForward;
                $tbDetails->gcv_bring_forward_position = 2;
                $tbDetails->gcv_leg_group = ($paylegPosition == 2) ? 'PAY' : 'POWER';
                $tbDetails->gcv_flush = ($paylegPosition == 2) ? $payLegFlushedCv : $flushedGcv;
                $tbDetails->gcv_bring_over = ($paylegPosition == 2) ? $rightBringOver : $gcvBringOver;
                $tbDetails->team_bonus_percentage = 0;
                $tbDetails->team_bonus = 0;
                $tbDetails->save();
            }

        }else {

            $details = collect($member->teamBonusDetails);

            //active member should have this info
            $details->each(function ($detail) use ($bonusSummaryId) {

                if($detail['placement_child_user_id'] == null){
                    return true; // skip if there a null
                }

                $tbDetails = new BonusTeamBonusDetails;
                $tbDetails->bonuses_summary_id = $bonusSummaryId;
                $tbDetails->placement_child_user_id = $detail['placement_child_user_id'];

                $tbDetails->gcv = $detail['gcv'];
                $tbDetails->optimising_personal_sales = $detail['optimising_personal_sales'];
                $tbDetails->gcv_calculation = $detail['gcv_calculation'];
                $tbDetails->gcv_bring_forward = $detail['gcv_bring_forward'];
                $tbDetails->gcv_bring_forward_position = $detail['gcv_bring_forward_position'];
                $tbDetails->gcv_leg_group = $detail['gcv_leg_group'];
                $tbDetails->gcv_flush = $detail['gcv_flush'];
                $tbDetails->gcv_bring_over = $detail['gcv_bring_over'];
                $tbDetails->team_bonus_percentage = $detail['team_bonus_percentage'] / 100;
                $tbDetails->team_bonus = $detail['team_bonus_cv'];
                $tbDetails->save();
            });
        }

    }

    private function storeMentorBonusDetails($member, $bonusSummaryId)
    {
        if($member->isActive && count($member->mentorBonusDetails) > 0){
            foreach($member->mentorBonusDetails as $details){
                BonusMentorBonusDetails::create([
                    'bonuses_summary_id' => $bonusSummaryId,
                    'sponsor_child_user_id' => $details['sponsor_child_user_id'],
                    'sponsor_generation_level' => $details['sponsor_generation_level'],
                    'team_bonus' => $details['team_bonus_cv'],
                    'mentor_bonus_percentage' => $details['mentor_bonus_percentage'],
                    'mentor_bonus' => $details['mentor_bonus_cv']
                ]);
            }
        }

    }

    private function storeQuarterlyDividendDetails($member, $bonusSummaryId)
    {
        $bonusDetails = BonusQuarterlyDividendDetails::firstOrNew([
            'cw_id' => $bonusSummaryId,
            'user_id' => $member->user_id
        ]);

        $bonusDetails->shares = $member->totalQuarterlyDividendShares;
        $bonusDetails->country_id = $member->country_id;
        $bonusDetails->save();

    }

    private function storeCampaignBonusDetails()
    {

    }

}
<?php
namespace App\Helpers\Classes\Bonus;

use App\Interfaces\General\CwSchedulesInterface;
use App\Models\Bonus\AmpCvAllocation;
use App\Models\Bonus\BonusDilution;
use App\Models\Bonus\BonusQuarterlyDividendDetails;
use App\Models\Bonus\BonusTeamBonusDetails;
use App\Models\Bonus\TeamBonus;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyConversion;
use App\Models\General\CWDividendSchedule;
use App\Models\General\CWSchedule;
use App\Models\Masters\Master;
use App\Models\Masters\MasterData;
use App\Models\Members\Member;
use App\Models\Members\MemberTree;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleAccumulation;
use App\Models\Sales\SaleCancellation;
use App\Models\Sales\SaleDetail;
use App\Models\Bonus\EnrollmentRank;
use App\Models\Bonus\TeamBonusRank;
use App\Models\Members\MemberRankTransaction;
use App\Helpers\Traits\Bonus;
use App\Models\Sales\SaleKittingClone;
use App\Models\Sales\SaleProduct;
use App\Models\Sales\SaleProductClone;
use App\Repositories\General\CwSchedulesRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use App\Helpers\Classes\MemberNetworkTree;


class MemberTreeBonus
{
    //this class will consists of all the members with all the operations to fill up the details for members and bonus
    private $sales; // all the sales that's happening
    private $welcomePackSalesProducts; // all the sales product
    private $membersTree;
    private $bonusDetails; // this will contains all the bonus details from eligible members for the CW
    private $salesDetails; // all the sales that has been made in the cw
    private $personalSales; // all the accumulated sales from members
    private $cwId;
    private $previousCwId; //cw id of the previous cw
    private $membersWithWelcomeBonus = array(); // consists of an array of user id that is eligible for bonus
    private $activeList = array(); // all the user that is active (maintained 30 CV on this CW)
    private $currencyConversion;
    private $cwInfo; // contains all the information of user history on that CW
    private $totalSalesCv; // all accumulated CV
    private $enrollmentRanks; // ranks and entitlement
    private $bringForwardInfo; //info from the previous CW
    private $dilutionAmount; // amount of the dillution

    private $usdCurrencyId; // the currency id for usd

    private $registrationTransactionId; // id for registration

    private $chinaBonusOveride = false;
    private $chinaOverrideValues = array(
        'teamBonusOneLeftOneRight' => 8,
        'teamBonusThreeLeftThreeRight' => 12
    );

    private $rentalOverrideValues = array(
        'teamBonusOneLeftOneRight' => 5,
        'teamBonusThreeLeftThreeRight' => 7.5
    );

    private $membersWithMentorBonus = array();

    //information for the bonus run;
    private $rankCollection= array(
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0
    );
    private $totalWelcomeBonusPayout = 0;
    private $totalSpecialBonusPayout = 0;
    private $totalTeamBonusPayout = 0;
    private $totalMentorBonusPayout = 0;
    private $totalPayoutByRanks = array(
        0 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        1 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        2 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        3 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        4 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        5 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        6 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        7 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        8 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        9 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        10 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        11 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        12 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        13 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        ),
        14 => array(
            'welcome_bonus' => 0,
            'special_bonus' => 0,
            'loyal_bonus' => 0,
            'team_bonus' => 0,
            'mentor_bonus' => 0,
            'quarterly_bonus' => 0
        )
    ); // keeps all the ranks payout

    /**
     * All ranking criteria - Settings
     */
    private $ranksPoints = array();

    private $teamBonusOneLeftOneRight = 10;
    private $teamBonusThreeLeftThreeRight = 15;

    private $minumumRankForTeamBonus = 2; // must make sure this id matched with the id from db table
    private $minimumRankForMentorBonus = 7; // must make sure this id matched with the id from db table

    // [rank_id] = (15,5,5) // 3 generations with 15%, 5%, 5%
    private $mentorBonus = array(
        7 => array(15,10),
        8 => array(15,10,5),
        9 => array(15,10,5),
        10 => array(15,10,5,5),
        11 => array(15,10,5,5,5),
        12 => array(15,10,5,5,5),
        13 => array(15,10,5,5,5,5),
        14 => array(15,10,5,5,5,5,5),
        15 => array(15,10,5,5,5,5,5)
    );

    private $rankings = array();

    private $loyalBonus = array(
        array(500, 1000, 5), // 500 - 999.99 = 5%
        array(1000, 2000, 10), // 1000 - 1999.99 = 10%
        array(2000, 5000, 15), // 2000 - 4999.99 = 15%
        array(5000, null, 20), // 5000 - infinite = 5%
    );

    private $quarterlyDividendShares  = array(
        10 => 1,
        11 => 2,
        12 => 2,
        13 => 3,
        14 => 4,
        15 => 4
    );

    private $memberSponsorRootId = 1; // User ID of the actual root

    protected $cwScheduleObj, $masterDataObj, $bonusDilutionObj, $teamBonusRankObj, $enrollmentRankObj,
                $currencyObj, $currencyConversionObj, $ampCvAllocationObj,
                $cwDividendScheduleObj, $saleCancellationObj, $saleProductObj, $bonusQuarterlyDividendDetailsObj,
                $cwSchedulesRepository, $memberNetworkTreeHelper;

    /**
     * If welcome bonus only is set, it will calculate welcome bonus only (normally running in daily)
     * MemberTreeBonus constructor.
     * @param string $cwId
     * @param boolean $welcomeBonusOnly
     */
    public function __construct(
        CWSchedule $cwSchedule,
        MasterData $masterData,
        BonusDilution $bonusDilution,
        TeamBonusRank $teamBonusRank,
        EnrollmentRank $enrollmentRank,
        Currency $currency,
        CurrencyConversion $currencyConversion,
        MemberNetworkTree $memberNetworkTree,
        AmpCvAllocation $ampCvAllocation,
        CWDividendSchedule $CWDividendSchedule,
        SaleCancellation $saleCancellation,
        SaleProduct $saleProduct,
        BonusQuarterlyDividendDetails $bonusQuarterlyDividendDetails,
        CwSchedulesInterface $cwSchedulesRepository
    )
    {
        $this->cwScheduleObj = $cwSchedule;
        $this->masterDataObj = $masterData;
        $this->bonusDilutionObj = $bonusDilution;
        $this->teamBonusRankObj = $teamBonusRank;
        $this->enrollmentRanksObj = $enrollmentRank;
        $this->currencyObj = $currency;
        $this->currencyConversionObj = $currencyConversion;
        $this->memberNetworkTreeHelper = $memberNetworkTree;
        $this->ampCvAllocationObj = $ampCvAllocation;
        $this->cwDividendScheduleObj = $CWDividendSchedule;
        $this->saleCancellationObj = $saleCancellation;
        $this->saleProductObj = $saleProduct;
        $this->bonusQuarterlyDividendDetailsObj = $bonusQuarterlyDividendDetails;
        $this->cwSchedulesRepository = $cwSchedulesRepository;
    }

    public function calculate($cwId = '', $welcomeBonusOnly = false)
    {
        ini_set('memory_limit', '-1');
        $this->cwId = ($cwId) ? $cwId : $this->cwSchedulesRepository->getCwSchedulesList('current')['data']->first()->id;
        $this->previousCwId = ($this->cwId == 1) ? 1 : $this->cwId - 1;

        $this->cwInfo = $this->cwScheduleObj->find($this->cwId);
        $this->totalSalesCv = 0; // all sales cv occurred in this CW calculation
        $this->personalSales = collect();
        $this->welcomePackTransactionIds = $this->masterDataObj->whereIn('title', [
                'Registration',
                'Member Upgrade',
                'BA Upgrade',
                'Formation'
            ]
        )->get(['id'])->pluck('id')->toArray();
        $this->registrationTransactionId = $this->masterDataObj->where('title', 'Registration')->get(['id'])->first()->id;

        $dilutionInfo = $this->bonusDilutionObj->select('diluted_percentage')->where('cw_id', $this->cwId)->first();
        $this->dilutionAmount = ($dilutionInfo == null) ? 1 : $dilutionInfo->diluted_percentage;

        //map local rankings criteria to table ranking id
        $ranksTable = $this->teamBonusRankObj->all();
        $ranksTable->each(function($rank){
            $this->rankings[$rank->id] = array(
                'skip' => ($rank->status) ? false : true,
                'name' => $rank->rank_name,
                'rank' => $rank->rank_code,
                'minimum_ps' => $rank->min_ps,
                'minimum_downlines_rank' => $rank->min_line_rank_id, // 1 refers to the id
                'total_rank_per_line' => $rank->line_rank_count, // how many minimum_downlines_rank in one line
                'lines' => $rank->no_of_lines,
                'minimum_gcv'=> $rank->min_payleg_gcv, // from pay-leg
                'max_carry_forward' => $rank->max_gcv_bf,
                'max_cap' => $rank->max_payout, //weekly cap in USD
                'minimum_active_ba' => $rank->total_active_ba, // minimum active BAs, direct, indirect
            );

            $this->ranksPoints[$rank->id] = $rank->min_payleg_gcv;
        });

        $this->enrollmentRanks = $this->enrollmentRanksObj->all()->keyBy('id');

        $this->usdCurrencyId = $this->currencyObj->select('id')->where('code', 'USD')->get()->first()->id;

        $this->currencyConversion = $this->currencyConversionObj->select('id', 'from_currency_id', 'to_currency_id', 'rate')
            ->with(['fromCurrency', 'toCurrency'])
            ->where('cw_id', $this->cwId)
            ->where('to_currency_id', $this->usdCurrencyId)->get()
            ->keyBy('from_currency_id');

        //If this is a genealogy tree for china, we need to swap the values of certain bonus
        $this->chinaBonusOveride = false;
        if($this->chinaBonusOveride){
            $this->teamBonusOneLeftOneRight = $this->chinaOverrideValues['teamBonusOneLeftOneRight'];
            $this->teamBonusThreeLeftThreeRight = $this->chinaOverrideValues['teamBonusThreeLeftThreeRight'];
        }

        // get the sponsor and placement network
        $memberNetworkTree = $this->memberNetworkTreeHelper->initiateMemberTree($cwId);
        $this->membersTree = $memberNetworkTree->getMemberTree();

        /**
         * Load all the accumulated sales on the cw
         */
        $this->sales = $this->ampCvAllocationObj->groupBy('user_id')
            ->selectRaw('*, sum(cv) as cv')->where([
                ['cw_id','=', $this->cwId], ['active', '=', 1], ['cv', '<>', 0]])->get();

        //load a list of from previous from members, each member might have 2 records.
        $bringForwardInfo = DB::table('bonus_team_bonus_details')
            ->join('bonuses_summary', 'bonuses_summary.id', '=', 'bonus_team_bonus_details.bonuses_summary_id')
            ->where('bonuses_summary.cw_id', $this->previousCwId)
            ->select('bonus_team_bonus_details.*', 'bonuses_summary.user_id')
            ->get();

        $bringForwardInfo->each(function($record){
            if($record->gcv_bring_forward_position == 1){
                //left side
                $this->bringForwardInfo[$record->user_id][1] = $record;
            }else{
                //must be at right
                $this->bringForwardInfo[$record->user_id][2] = $record;
            }
        });

        $this->populateActiveMembers(); // we will populate the active members to give green light first
        $this->calculateCommissions(); // move on to commmission

        return $this;
        dd($this->membersTree->get(89));
//        echo '<pre>';
//        foreach($this->activeList as $memberId){
//            $member = $this->membersTree->get($memberId);
//            if($member->totalTeamBonus > 1000){
//                dd($member);
//            }
////            print_r($this->membersTree->get($memberId));
//        }

//        $total = 0;
//        $this->membersTree->each(function($member) use(&$total){
//            if($member->totalWelcomeBonus > 0){
//                echo $member->user->old_member_id.','.$member->totalWelcomeBonus.'<br>';
//            }
//        });
//        echo 'Actual total CV : '.$total;
        //die;

        $this->populateBonusInfo();
        $this->printJSON();die;
    }

    public function getBringForwardInfo(){
        return $this->bringForwardInfo;
    }

    /**
     * gets the cw id that is being calculated
     * @return string
     */
    public function getCwId()
    {
        return $this->cwId;
    }

    private function populateActiveMembers()
    {
        //before anything else, lets loop all the accumulated personal sales/AMP and pass all the information up
        $this->sales->each(function($sale){
            $this->totalSalesCv += $sale->cv;
            $this->addPersonalSales($sale->cv, $sale->user_id);
        });

        //amt type id in
        $ampTypeId = $this->masterDataObj->where('title', 'Amp')->whereHas('master', function($query){
            $query->where('key', 'amp_cv_allocation_types');
        })->get()->first()->id;

        $saleTypeId = $this->masterDataObj->where('title', 'Sales')->whereHas('master', function($query){
            $query->where('key', 'amp_cv_allocation_types');
        })->get()->first()->id;

        $megaTypeId = $this->masterDataObj->where('title', 'Rental')->whereHas('master', function($query){
            $query->where('key', 'amp_cv_allocation_types');
        })->get()->first()->id;

        $activeUsers = []; // active users from previous cw

        // we do not have to worry about negative value in amp as negative only happens in sales
        $activeUsersByAmp = $this->ampCvAllocationObj->select('user_id')->where('cw_id', $this->previousCwId)
            ->where('type_id', $ampTypeId)->where('active', 1)->get()->pluck('user_id')->toArray();

        //this is to look for the previous cw where the user has purchase up to at least 60 cv
        $activeUsersBySales = $this->ampCvAllocationObj->groupBy('user_id')
            ->selectRaw('sum(cv) as cv, user_id')
            ->where('type_id', $saleTypeId)
            ->where([
                ['cw_id','=', $this->previousCwId], ['active', '=', 1], ['cv', '<>', 0]])
            ->get()->keyBy('user_id')->toArray(); // results already sort by user

        /** For mega type, we should make the user active. Also to tag the parent member and above in placement tree
         *  We will look for current CW with the previous one.
         */
        $currentActiveUsersByRental = $this->ampCvAllocationObj->groupBy('user_id')
            ->selectRaw('sum(cv) as cv, user_id')
            ->where('type_id', $megaTypeId)
            ->where([
                ['cw_id','=', $this->cwId], ['active', '=', 1], ['cv', '<>', 0]])
            ->get()->keyBy('user_id')->toArray(); // results already sortby user

        $previousActiveUsersByRental = $this->ampCvAllocationObj->groupBy('user_id')
            ->selectRaw('sum(cv) as cv, user_id')
            ->where('type_id', $megaTypeId)
            ->where([
                ['cw_id','=', $this->previousCwId], ['active', '=', 1], ['cv', '<>', 0]])
            ->get()->keyBy('user_id')->toArray(); // results already sortby user

        foreach($activeUsersByAmp as $userId){
            //setting each of them to active
            $activeUsers[] = $userId;
            $this->membersTree->get($userId)->isActive = true;
            $this->membersTree->get($userId)->isActiveByNormalCv = true;
        }

        foreach($activeUsersBySales as $data){
            if(!$this->membersTree->has($data['user_id'])){
                //skip if this user is not in member tree
                continue;
            }

            if($data['cv'] >= 60){
                $activeUsers[] = $data['user_id'];
                $this->membersTree->get($data['user_id'])->isActive = true;
                $this->membersTree->get($data['user_id'])->isActiveByNormalCv = true;
            }
        }

        foreach($previousActiveUsersByRental as $data){
            if($data['cv'] >= 30){
                $activeUsers[] = $data['user_id'];
                $this->membersTree->get($data['user_id'])->isActive = true;
                $this->membersTree->get($userId)->isActiveByMegaCv = true;
            }
        }

        foreach ($currentActiveUsersByRental as $data){
            if($data['cv'] >= 30){
                $activeUsers[] = $data['user_id'];
                $this->membersTree->get($data['user_id'])->isActive = true;
                $this->membersTree->get($userId)->isActiveByMegaCv = true;

                //passing the mega cv generated by this user.
                if($this->membersTree->get($data['user_id'])->placementParent){
                    //we will need to let the parents and above knows that they have a rental cv below them
                    $this->passMegaCv($this->membersTree->get($data['user_id'])->placementParent, $data['cv'],
                        $this->membersTree->get($data['user_id'])->placement_position);
                }
            }
        }

        $this->activeList = array_merge($this->activeList, $activeUsers);

        //we will need to find out the rank of each users, we do not have to loop everyone, just Active BA will do
        $this->activeList = array_unique($this->activeList);

        // we need to sort the active list based on the depth
        $tempActiveList = array();
        foreach($this->activeList as $memberId){
            $tempActiveList[$this->membersTree->get($memberId)->sponsor_depth_level][] = $memberId;
        }

        //sort by key [which is the depth] in desc order
        krsort($tempActiveList);
        $this->activeList = array(); // reset active list

        foreach($tempActiveList as $userIdArray){
            $this->activeList = array_merge($this->activeList, $userIdArray);
        }

        //lets add the active BA count to the member details
        foreach($this->activeList as $memberId){

            //set every active user achieved rank as 2 first
            $this->membersTree->get($memberId)->achievedRankId = $this->minumumRankForTeamBonus;

            if(isset($this->membersTree->get($memberId)->sponsorParent)){
                //Adding active ba from own sponsor line
                $this->addActiveBA($this->membersTree->get($memberId), $this->membersTree->get($memberId)->sponsorParent);
            }

            //take this chance to add in the carry forward value
            if(isset($this->bringForwardInfo[$memberId][1])){
                $this->membersTree->get($memberId)->carryForwardInfo['left_amount'] = $this->bringForwardInfo[$memberId][1]->gcv_bring_over;
                $this->membersTree->get($memberId)->carryForwardInfo['left_passed_by'] = $this->bringForwardInfo[$memberId][1]->placement_child_user_id;
            }

            if(isset($this->bringForwardInfo[$memberId][2])) {
                $this->membersTree->get($memberId)->carryForwardInfo['right_amount'] = $this->bringForwardInfo[$memberId][2]->gcv_bring_over;
                $this->membersTree->get($memberId)->carryForwardInfo['right_passed_by'] = $this->bringForwardInfo[$memberId][2]->placement_child_user_id;
            }
        }

        /**
         * Loop through to find out unique lines that has active BA
         */
        $this->membersTree->each(function($member){
            //we also want to find out total BA on the left and right of the user

            $member->isNewBA = ($this->cwInfo->cw_name == $member->cw) ? true : false ;
            $this->addBaCountInfo( $member, $member->isActive, $member->isNewBA );
            $this->addPlacementCountInfo( $member, $member->isActive, $member->isNewBA );

            //add currency conversion rate for this user
            if($member->default_currency_id){
                $member->currencyConversionRate = $this->currencyConversion->get($member->default_currency_id)->rate;
            }

            foreach($member->firstLevelSponsorChild as $child){
                //looping each lines
                if($child->isActive || $child->totalActiveBA){
                    //find out which way
                    $position = $this->findSponsorPositionInPlacement($child, $member);
                    if($position == 1){
                        $member->totalUniqueLeftActiveBA++;
                    }elseif($position == 2){
                        $member->totalUniqueRightActiveBA++;
                    }
                }
            }
        });
    }

    /**
     * Passing the information up
     * @param $member
     * @param $cv
     * @param $position - placement position, 1 = left, 2 = right
     */
    public function passMegaCv($member, $cv, $position)
    {

        if($position == 1){
            $member->leftMegaCv += $cv;
        }elseif($position == 2){
            $member->rightMegaCv += $cv;
        }

        if($member->placementParent){
            $this->passMegaCv($member->placementParent, $cv, $member->placement_position);
        }

        return;
    }


    /*******************************************************************************************************************
     * Commissions related
     * ***************************************************************************************************************/

    /**
     * Start calculating the Commissions
     */
    private function calculateCommissions()
    {
        /**
         * Welcome bonus calculations
         */
        $this->calculateWelcomeBonus(); // calculate welcome bonus

        /**
         * Team Bonus Calculations & Differential bonus calculations
         *
         * once we know who are the active BA and we populate the information of active BA to all the levels, we will find
         * out the ranks for each active user
         */
        foreach($this->activeList as $memberId){
            //take this chance to populate total BA that is active under the member
            if($this->membersTree->get($memberId)->sponsorParent){
                $this->membersTree->get($memberId)->sponsorParent->totalDirectDownlineActiveBA++;
            }

            $this->calculateTeamBonus($this->membersTree->get($memberId));
        }

        /**
         * When everything is done, loop through all the members that has bonus to get and calculate the bonus
         * Mentor Bonus
         */
        $this->calculateMentorBonus();

        /**
         * Quarterly dividend
         */

        $cwDividendSchedule = $this->cwDividendScheduleObj->where('to_cw_id', $this->cwId)->get()->first();
        if($cwDividendSchedule){
            $this->calculateQuarterlyDividend($cwDividendSchedule);
        }
    }

    /**
     * @param $cwDividendSchedule
     */
    public function calculateQuarterlyDividend($cwDividendSchedule)
    {
        //gets all the shares of users in the cws
        $dividendShares = $this->bonusQuarterlyDividendDetailsObj->whereBetween('cw_id',
            [$cwDividendSchedule->from_cw_id, $cwDividendSchedule->to_cw_id])->get()->mapToGroups(function($item, $key){
            return [$item->user_id => $item->shares];
        });

        $totalShares = $dividendShares->sum(function($shares){
            return $shares->sum();
        });

        $totalCompanyCV = $this->ampCvAllocationObj->select('cv')->whereBetween('cw_id', [$cwDividendSchedule->from_cw_id,
            $cwDividendSchedule->to_cw_id])->where('active', 1)->get()->sum('cv');

        $dividendShares->each(function($memberShares, $userId) use($totalShares, $totalCompanyCV){
            $shares = $memberShares->sum();
            $totalPayout = $shares / $totalShares * $totalCompanyCV;
            $this->membersTree->get($userId)->totalQuarterlyDividend = $totalPayout;
        });
    }


    /**
     * This will add the information of the member to the uplines in sponsor line
     * @param $member
     * @param bool $isActive
     * @param $isNewBa
     */
    private function addBaCountInfo($member, $isActive = false, $isNewBa)
    {
        if(isset($member->sponsorParent)){
            if($isActive){
                $member->sponsorParent->totalActiveBa++;
            }

            if($isNewBa){
                $member->sponsorParent->totalNewBA++;
            }

            $member->sponsorParent->totalDownlines++;

            $this->addBaCountInfo($member->sponsorParent, $isActive, $isNewBa);
        }

        return;
    }

    /**
     *
     * @param $member
     * @param bool $isActive
     * @param $isNewBa
     */
    private function addPlacementCountInfo($member, $isActive = false, $isNewBa)
    {
        /*
         * This part will only use placement ignoring the sponsor line
         * */
        if(isset($member->placementParent)){
            if($member->placement_position == 1){
                $member->placementParent->totalBALeft++;

                if($isActive){
                    $member->placementParent->totalLeftActiveBAPlacement++;
                }

                //is a new ba if the join date is equivalent to current cw name
                if($isNewBa){
                    $member->placementParent->totalNewBALeft++;
                }
            }else{
                $member->placementParent->totalBARight++;

                if($isActive){
                    $member->placementParent->totalRightActiveBAPlacement++;
                }

                if($isNewBa){
                    $member->placementParent->totalNewBARight++;
                }
            }

            $this->addPlacementCountInfo($member->placementParent, $isActive, $isNewBa);
        }

    }

    private function calculateWelcomeBonus()
    {
        /**
         * This is the sales details to count Welcome bonus
         * Gets all the products from sales by cw
         */
        $completedOrderStatusId = $this->masterDataObj->where('title', 'COMPLETED')
            ->whereHas('master', function($masterQuery){
                $masterQuery->where('key', 'sale_order_status');
            })->get()->first()->id;

        $cancelledSalesIds = $this->saleCancellationObj->where('cw_id', $this->cwId)->get()->pluck('sale_id')->toArray();
        $this->welcomePackSalesProducts = $this->saleProductObj->whereIn('transaction_type_id', $this->welcomePackTransactionIds)
            ->whereHas('sale', function($query) use($cancelledSalesIds, $completedOrderStatusId) {
                //exclude product exchange
                $query->where('cw_id', $this->cwId)
                    ->where('is_product_exchange', 0)
                    ->where('order_status_id', $completedOrderStatusId);
                if(!empty($cancelledSalesIds)){
                    //remove cancelled product from the list
                    $query->whereNotIn('id', $cancelledSalesIds);
                }
            })->with('sale.country')->get();

        $calculatedWBKitting = []; //mapping id of the same kitting that has been calculated

        $this->welcomePackSalesProducts->each(function($saleProduct) use(&$calculatedWBKitting){
            //$saleCountry = $saleProduct->sale->country->code_iso_2;
            $currencyId = $saleProduct->sale->country->default_currency_id;
            $sale = $saleProduct->sale;

            if($this->membersTree->get($sale->user_id) == null){
                return true;
            }

            $product = $saleProduct;

            if($saleProduct->mapping_model == 'sales_kitting_clone' &&
                !in_array($saleProduct->mapping_id, $calculatedWBKitting)){
                $product = $saleProduct->getMappedModel;
                $calculatedWBKitting[] = $saleProduct->mapping_id;

            }elseif($saleProduct->mapping_model != '' || in_array($saleProduct->mapping_id, $calculatedWBKitting)){
                //this can be FOC, etc, skip this product
                return true;
            }
            // from the welcome pack/upgrade buyer
            $commissionsInfo['total_local_amount'] = [
                '1' => $product->quantity * $product->welcome_bonus_l1,
                '2' => $product->quantity * $product->welcome_bonus_l2,
                '3' => $product->quantity * $product->welcome_bonus_l3,
                '4' => $product->quantity * $product->welcome_bonus_l4,
                '5' => $product->quantity * $product->welcome_bonus_l5
            ];

            // after converted to the welcome pack
            $rate = $this->currencyConversion->get($currencyId)->rate; // rate from sale country to USD

            $commissionsInfo['total_usd_amount'] = [
                '1' => round($product->quantity * $product->welcome_bonus_l1 * $rate, 2),
                '2' => round($product->quantity * $product->welcome_bonus_l2 * $rate, 2),
                '3' => round($product->quantity * $product->welcome_bonus_l3 * $rate, 2),
                '4' => round($product->quantity * $product->welcome_bonus_l4 * $rate, 2),
                '5' => round($product->quantity * $product->welcome_bonus_l5 * $rate, 2),
            ];

            $commissionsInfo['user_id'] = $sale->user_id; //user id of whoever bought the package

            $commissionsInfo['user_join_date'] = isset($this->membersTree->get($commissionsInfo['user_id'])->join_date)
                ? $this->membersTree->get($commissionsInfo['user_id'])->join_date : null;
            $commissionsInfo['local_to_usd_conversion_rate'] = $rate;
            $commissionsInfo['transaction_date'] = $sale->transaction_date;

            if($this->membersTree->get($sale->user_id)->sponsorParent){
                $this->addWPCommission($commissionsInfo, 1, $this->membersTree->get($sale->user_id)->sponsorParent);
            }else{
                return true;
            }
        });
    }

    /**
     * Recursively add commission for welcome bonus
     *
     * @param $commissionsInfo
     * @param int $currentLevel
     * @param MemberTree $member
     */
    private function addWPCommission($commissionsInfo, $currentLevel = 1, MemberTree $member)
    {
        $enrollmentRankId = (isset($member->bonusSummary->enrollment_rank_id)
            && $member->bonusSummary->enrollment_rank_id > 0) ? $member->bonusSummary->enrollment_rank_id :
            $member->enrollment_rank_id;

        if($enrollmentRankId){
            //check the member eligibility
            $commissionLevel = $this->enrollmentRanks->get($enrollmentRankId)->entitlement_lvl;

            //if member is not active, he/she can only get up to 1 level
            if(!$member->isActive){
                $commissionLevel = 1; // reset to entitlement of commission level to 1
            }

            if($commissionLevel >= $currentLevel && $currentLevel < 6){
                $this->membersWithWelcomeBonus[] = $member->user_id;
                $member->addWelcomeBonusCommission($commissionsInfo['total_usd_amount'][$currentLevel]);

                if(isset($member->welcomeBonusDetails[$commissionsInfo['user_id']])){
                    // we add up the amount for this user id if the sales comes from the same user
                    $existingWelcomeBonus = $member->welcomeBonusDetails[$commissionsInfo['user_id']];
                    $member->welcomeBonusDetails[$commissionsInfo['user_id']] = [
                        'sponsor_child_user_id' => $commissionsInfo['user_id'],
                        'sponsor_child_depth_level' => $currentLevel,
                        'join_date' => $commissionsInfo['user_join_date'],
                        'total_local_amount' => $commissionsInfo['total_local_amount'][$currentLevel]
                            + $existingWelcomeBonus['total_local_amount'],
                        'total_local_amount_currency' => $commissionsInfo['local_to_usd_conversion_rate'],
                        'total_amount' => $commissionsInfo['total_usd_amount'][$currentLevel] /
                            $this->currencyConversion->get($member->default_currency_id)->rate +
                            $existingWelcomeBonus['total_amount'],
                        'total_amount_currency' => $this->currencyConversion->get($member->default_currency_id)->rate,
                        'total_usd_amount' => $commissionsInfo['total_usd_amount'][$currentLevel] + $existingWelcomeBonus['total_usd_amount'],
                        'total_nett_usd_amount' => $commissionsInfo['total_usd_amount'][$currentLevel]
                            + $existingWelcomeBonus['total_nett_usd_amount'], //@todo to times this amount with WHT tax
                        'transaction_date' => $commissionsInfo['transaction_date']
                    ];
                }else{
                    $member->welcomeBonusDetails[$commissionsInfo['user_id']] = [
                        'sponsor_child_user_id' => $commissionsInfo['user_id'],
                        'sponsor_child_depth_level' => $currentLevel,
                        'join_date' => $commissionsInfo['user_join_date'],
                        'total_local_amount' => $commissionsInfo['total_local_amount'][$currentLevel],
                        'total_local_amount_currency' => $commissionsInfo['local_to_usd_conversion_rate'],
                        'total_amount' => $commissionsInfo['total_usd_amount'][$currentLevel] / $this->currencyConversion->get($member->default_currency_id)->rate,
                        'total_amount_currency' => $this->currencyConversion->get($member->default_currency_id)->rate,
                        'total_usd_amount' => $commissionsInfo['total_usd_amount'][$currentLevel],
                        'total_nett_usd_amount' => $commissionsInfo['total_usd_amount'][$currentLevel], //@todo to times this amount with WHT tax amount
                        'transaction_date' => $commissionsInfo['transaction_date']
                    ];
                }
            }
        }

        //if there is upline and the level does not exceed level 5
        if($member->sponsorParent && $currentLevel != 6){
            $this->addWPCommission($commissionsInfo, $currentLevel + 1, $member->sponsorParent);
        }

        return;
    }

    /**
     *
     */
    private function calculateMentorBonus()
    {
        //find out all the members who are entitled for mentorBonus
        foreach($this->activeList as $memberId){
            $member = $this->membersTree->get($memberId);
            // only member with SD and above are entitled for this bonus
            // by default, whoever is in this rank should reached tri formation
            if($member->achievedRankId >= $this->minimumRankForMentorBonus){
                $info = array();
                $member->totalMentorBonus = $this->addMentorBonusDownwards($member, $member, 0, $this->mentorBonus[$member->achievedRankId], $info);
                $member->totalMentorBonusDiluted = $member->totalMentorBonus * $this->dilutionAmount;
                $member->mentorBonusInfo = $info; // assign the information of the members who passed the bonus up
            }
        }

    }

    /**
     * @param $actualMember - the member that we are calculating for
     * @param $member
     * @param int $generation
     * @param $mentorBonusRate
     * @param $info
     * @return float|int
     */
    private function addMentorBonusDownwards($actualMember, $member, $generation = 0, $mentorBonusRate, &$info)
    {
        if(count($mentorBonusRate) == $generation || !count($member->firstLevelSponsorChild)){
            return 0;
        }

        $total = 0;
        foreach($member->firstLevelSponsorChild as $child){
            $amt = $mentorBonusRate[$generation] / 100 * $child->totalTeamBonus;

            if($amt) {
                $actualMember->mentorBonusDetails[] = [
                    'child_name' => $child->name,
                    'sponsor_child_user_id' => $child->user_id,
                    'sponsor_generation_level' => $generation + 1, // in report, it starts with 1
                    'team_bonus_cv' => $child->totalTeamBonus,
                    'mentor_bonus_percentage' => $mentorBonusRate[$generation] / 100,
                    'mentor_bonus_cv' => $amt
                ];
            }

            $total += $amt;
//            if($amt){
//                $info[$child->user->old_member_id] = array( $amt, $mentorBonusRate[$generation], $generation );
//            }

            //check if this child is at least a SD or not, if not, the next call will still be in the same generation
            if($child->achievedRankId < 7){
                $total += $this->addMentorBonusDownwards($actualMember, $child, $generation, $mentorBonusRate, $info);
            }else{
                $total += $this->addMentorBonusDownwards($actualMember, $child, $generation + 1, $mentorBonusRate, $info);
            }
        }

        return $total;
    }

    /**
     * @param $member
     * @param int $generation
     * @param $mentorBonusRate
     * @return float|int
     */
    private function addMentorBonus($member, $generation = 0, $mentorBonusRate, &$info)
    {
        if(count($mentorBonusRate) == $generation || !count($member->firstLevelCompressedSponsorChild)){
            return 0;
        }

        $total = 0;
        foreach($member->firstLevelCompressedSponsorChild as $child){
            //all the child should be eligible since we already do first level filtering
            $amt = $mentorBonusRate[$generation] / 100 * $child->payLegCvWithOps * $child->eligibleTeamBonusPercent / 100; // we recalculate because some of the member has special arrangement
            $total += $amt;
            if($amt){
                $info[$child->user_id] = array( $amt, $child->eligibleTeamBonusPercent ,$generation );
            }
            $total += $this->addMentorBonusUpwards($child, $member->user_id, $mentorBonusRate[$generation], $info, $generation);
            $total += $this->addMentorBonus($child, $generation + 1, $mentorBonusRate, $info);
        }

        return $total;
    }

    /**
     * When we found a generation marker, all the team bonus above this member should be shared as well.
     * @param $member
     * @param $breakId break when we find out the id of the upline
     * @param $percentage
     * @param $info
     * @param $generation
     * @return float|int|void
     */
    private function addMentorBonusUpwards($member, $breakId, $percentage, &$info, $generation)
    {
        $total = 0;
        if($member->sponsorParent->user_id == $breakId){
            return;
        }

        $amt = $percentage / 100 * $member->payLegCvWithOps * $member->eligibleTeamBonusPercent / 100; // we recalculate because some of the member has special arrangement
        $total += $amt;

        if($amt){
            $info[$member->user_id] = array( $amt, $member->eligibleTeamBonusPercent, $generation );
        }

        if($member->sponsorParent){
            $this->addMentorBonusUpwards($member->sponsorParent, $breakId, $percentage, $info, $generation);
        }

        return $total;
    }

    private function printCompressedTree($member, $level = 1)
    {
        echo '<br/>Level '.$level.' child of '.$member->user_id.'<br/>';
        foreach($member->firstLevelCompressedSponsorChild as $child){;
            $this->printMember($child);
            echo "=========<br/>";
        }

        foreach($member->firstLevelCompressedSponsorChild as $child){
            $this->printCompressedTree($child, $level + 1);
        }

        return;
    }

    /**
     * Add ranks count for each rank to the member and all the parents in sponsor tree
     * @param MemberTree $member
     * @param $rankId
     */
    private function addRankToSponsors(MemberTree $member, $rankId){
        if(isset($member->totalRanks[$rankId])){
            $member->totalRanks[$rankId]++;
        }else{
            $member->totalRanks[$rankId] = 1;
        }

        if(isset($member->sponsorParent)) {

            // we only need to keep track of Rank ABOVE EBA
            if ($rankId > 1) {
                //we will need to find out where this member resides on the parent placement position, add this information
                //into the sponsor parent
                if (isset($member->sponsorParent->placementLeft) && $member->sponsorParent->placementLeft->user_id == $member->user_id) {
                    //this is the direct one
                    if (isset($member->sponsorParent->placementRankLeft[$rankId])) {
                        $member->sponsorParent->placementRankLeft[$rankId]++;
                    } else {
                        $member->sponsorParent->placementRankLeft[$rankId] = 1;
                    }
                } elseif (isset($member->sponsorParent->placementRight) && $member->sponsorParent->placementRight->user_id == $member->user_id) {
                    //this is the direct one
                    if (isset($member->sponsorParent->placementRankRight[$rankId])) {
                        $member->sponsorParent->placementRankRight[$rankId]++;
                    } else {
                        $member->sponsorParent->placementRankRight[$rankId] = 1;
                    }
                } else {
                    //if the sponsored member is not a direct downline in placement, we need to traverse the tree upwards
                    if (isset($member->sponsorParent->placementLeft)) {
                        $position = ($this->placementUpSearch($member, $member->sponsorParent->placementLeft->user_id)) ? 1 : 2; // 1 = left, 2 = right
                        if ($position === 1) {
                            if (isset($member->sponsorParent->placementRankLeft[$rankId])) {
                                $member->sponsorParent->placementRankLeft[$rankId]++;
                            } else {
                                $member->sponsorParent->placementRankLeft[$rankId] = 1;
                            }
                        } else {
                            if (isset($member->sponsorParent->placementRankRight[$rankId])) {
                                $member->sponsorParent->placementRankRight[$rankId]++;
                            } else {
                                $member->sponsorParent->placementRankRight[$rankId] = 1;
                            }
                        }
                    } else {
                        //if its not in the left, it must be in the right
                        if (isset($member->sponsorParent->placementRankRight[$rankId])) {
                            $member->sponsorParent->placementRankRight[$rankId]++;
                        } else {
                            $member->sponsorParent->placementRankRight[$rankId] = 1;
                        }
                    }
                }
                $this->addRankToSponsors($member->sponsorParent, $rankId);
            }
        }

        return;
    }

    /**
     * Add 1 to the active BA count to the placement parents (Only from sponsorline)
     * @param $member
     */
    private function addActiveBA(MemberTree $child, MemberTree $member){
        //lets see if this left placement is directly connected to the direct downline
        if(isset($member->placementLeft) && $member->placementLeft->user_id == $child->user_id){
            $member->totalLeftActiveBA++;
        }elseif(isset($member->placementRight) && $member->placementRight->user_id == $child->user_id){
            $member->totalRightActiveBA++;
        }else{
            //if the sponsored member is not a direct downline in placement, we need to traverse the tree upwards
            if(isset($member->placementLeft)){
                $position = ($this->placementUpSearch($child, $member->placementLeft->user_id)) ? 1 : 2 ; // 1 = left, 2 = right
                if($position === 1){
                    $member->totalLeftActiveBA++;
                }else{
                    $member->totalRightActiveBA++;
                }
            }else{
                //if its not in the left, it must be in the right
                $member->totalRightActiveBA++;
            }
        }

        if(isset($member->sponsorParent)){
            $this->addActiveBA($member, $member->sponsorParent);
        }

        return;
    }

    /**
     * @param $member
     * @param $memberId
     * @return bool
     */
    private function placementUpSearch($member, $memberId){

        if($member->user_id == $memberId){
            return true;
        }

        if(isset($member->placementParent)){
            return $this->placementUpSearch($member->placementParent, $memberId);
        }

        return false;
    }

    /**
     * Add personal sales to the specific member
     * If the member is not a BA, we will have to pass the sales up
     * @param $cv
     * @param int $userId
     * @param boolean $ownSales is it my the member own sale or not
     */
    private function addPersonalSales($cv, $userId = 0, $ownSales = true){
        if(!$this->membersTree->has($userId)){
            // if there is no record of this user in the tree
            return;
        }

        $member = $this->membersTree->get($userId);

        $memberEnrollmentRankId = isset($member->bonusSummary->enrollment_rank_id) && $member->bonusSummary->enrollment_rank_id > 0
            ? $member->bonusSummary->enrollment_rank_id : $member->enrollment_rank_id;

        // pre-requisite : we need to find out if the user is currently a premier member or BA or not
        // else we will need to pass the sales up

        // if member is not a normal member/premium member, he is eligible to have personal sales cv
        // if enrolment ranks id with entitlement level 0/1 is doesnt belong to this user
        if(!$this->enrollmentRanks->whereIn('entitlement_lvl', [0,1])->pluck('id')
            ->contains($memberEnrollmentRankId)){
            if(!$ownSales){
                //if this is not own sale, add this info
                $member->addMemberSalesCv($cv);
            }else{
                $member->addPersonalSalesCv($cv);
            }
        }else{
            // we will need to roll this up to the sponsor parent
            if($this->membersTree->has($member->sponsor_parent_user_id)){
                $this->addPersonalSales($cv, $member->sponsor_parent_user_id, false);
            }
        }

        // member who get through this line is consider BA and above.

        /**
         * Loyal Customer Bonus
         * - Only apply to non china and valid member
         */
        //@todo check if the member is valid or not
        if(1 && !$this->chinaBonusOveride){
            $this->addLoyalCustomerBonus($member, 0);
        }

        // if his own personal sales is more than 60 CV, he is an active BA,
        // so in the case when someone bought over 60, he is also an active BA on this CW
        if($member->personalSalesCv >= 60 && !$member->isActive){
            //can only be activated once
            $member->isActive = true; // set this guy as active
            $this->activeList[] = $member->user_id;
        }

        if(isset($member->placementParent)){
            $this->addUplineSalesCV($cv, $member->placementParent);
        }
    }

    private function addLoyalCustomerBonus($member, $remainingDifferentialValue){
        $entitlementPercentage = 0;
        //check which entitlement percentage of this user is in
        if($member->personalSalesCv > 0){
            foreach($this->loyalBonus as $criteria){

            }
        }
        // must break if the remaining differential value is zero
        return;

        $this->addLoyalCustomerBonus();
    }

    private function addUplineSalesCV($cvAmount, MemberTree $member)
    {
        $member->addTotalCv($cvAmount);

        //check if we have placement parent, if yes we will add this to top
        if($member->placementParent){
            $this->addUplineSalesCV($cvAmount, $member->placementParent);
        }
    }

    /**
     * After we have all the information of the CV/GCV, we can now work on the Ranks that member based on
     * the criteria hits. Also, Calculate team bonus here.
     * @param MemberTree $member
     * @return bool
     */
    private function calculateTeamBonus(MemberTree $member)
    {
        // also use this opportunity to find out the accumulated left and right cv
        if(isset($member->placementLeft)){
            $member->accumulatedLeftCv = $member->placementLeft->totalCv + $member->placementLeft->personalSalesCv
                + $member->placementLeft->membersCv;
        }

        if(isset($member->placementRight)){
            $member->accumulatedRightCv = $member->placementRight->totalCv + $member->placementRight->personalSalesCv
                + $member->placementRight->membersCv;
        }

        //let's find out the payleg first to determine their rank
        $finalLeftCv = $member->accumulatedLeftCv + $member->carryForwardInfo['left_amount'];
        $finalRightCv = $member->accumulatedRightCv + $member->carryForwardInfo['right_amount'];

        $member->teamBonusDetails['left'] = [
            'gcv' => $member->accumulatedLeftCv,
            'gcv_calculation' => $member->payLegCvWithOps,
            'gcv_bring_forward' => $member->carryForwardInfo['left_amount'],
            'gcv_bring_forward_position' => 1,
            'placement_child_user_id' => isset($member->placementLeft) ? $member->placementLeft->user_id : null,
            'gcv_bring_over' => '0.00',
            'team_bonus_cv' => '0.00',
            'gcv_flush' => '0.00',
            'optimising_personal_sales' => '0.00',
            'team_bonus_percentage' => 0
        ];

        $member->teamBonusDetails['right'] = [
            'gcv' => $member->accumulatedRightCv,
            'gcv_calculation' => $member->payLegCvWithOps,
            'gcv_bring_forward' => $member->carryForwardInfo['right_amount'],
            'gcv_bring_forward_position' => 2,
            'placement_child_user_id' => isset($member->placementRight) ? $member->placementRight->user_id : null,
            'gcv_bring_over' => '0.00',
            'team_bonus_cv' => '0.00',
            'gcv_flush' => '0.00',
            'optimising_personal_sales' => '0.00',
            'team_bonus_percentage' => 0
        ];

        //we need to put in OPS first before determine which one is the Power/Pay leg
        if(($member->personalSalesCv + $member->membersCv) > 60) {
            $ops = $member->personalSalesCv + $member->membersCv - 60;
            $member->ops = $ops;

            if ($finalLeftCv > $finalRightCv) {
                $member->teamBonusDetails['right']['optimising_personal_sales'] = $ops;
                $finalRightCv += $ops;
            } else {
                $member->teamBonusDetails['left']['optimising_personal_sales'] = $ops;
                $finalLeftCv += $ops;
            }
        }

        $member->carryForwardPos = 1;

        $bringOverCv = 0;

        if($finalLeftCv > $finalRightCv){
            $member->payLegCv = $finalRightCv - $member->ops;
            $member->payLegCvWithOps = $finalRightCv;
            $bringOverCv = $powerlegAmt = $finalLeftCv;
            $member->teamBonusDetails['right']['gcv_leg_group'] = 'PAY';
            $member->teamBonusDetails['left']['gcv_leg_group'] = 'POWER';
        }else{
            $member->carryForwardPos = 2;
            $member->payLegCv = $finalLeftCv - $member->ops;
            $member->payLegCvWithOps = $finalLeftCv;
            $bringOverCv = $powerlegAmt = $finalLeftCv;
            $member->teamBonusDetails['right']['gcv_leg_group'] = 'POWER';
            $member->teamBonusDetails['left']['gcv_leg_group'] = 'PAY';
        }

        $totalLines = count($member->firstLevelSponsorChild);

        if($totalLines < 2){
            // only if this guy have more than 2 lines, he is qualified for the team bonus
            // else he is not eligible at all
            // this member, however, is still an active BA, pass this info up
            if(isset($member->sponsorParent)){
                $this->addRankToSponsors($member->sponsorParent, 2); // where 2 indicates BA
            }
            return false;
        }

        // If member is active because of 60 CV (non-mega) and my pay-leg is lesser than 60, he's not eligible at all
        if($member->isActiveByNormalCv && $member->payLegCv < 60){
            $this->addRankToSponsors($member->sponsorParent, 2); // where 2 indicates BA
            return false;
        }

        //lets find out what is the maximum rank this fella can reach with his pay leg GCV first
        $maximumRank = 0;
        foreach($this->ranksPoints as $rankId => $points){
            if($member->payLegCvWithOps >= $points){
                $maximumRank = $rankId;
            }
        }

        // Mega, for 30 cv, this user will be eligible.
        if( $member->payLegCv >= 30 && $member->payLegCv < 60
            && (($finalLeftCv < 60 && $member->leftMegaCv >= 30) || $finalLeftCv > 60)
            && (($finalRightCv < 60 && $member->rightMegaCv >= 30) || $finalRightCv > 60)
            && $maximumRank <= 2
        ){
            $maximumRank = 2; // eligible to get team bonus because of mega cv
        }

        $requirementMet = false; //if there is a requirement met, this will be true

        /**
         * Team bonus
         */
        //now that this user requirements is met, lets count the bonus that this user is entitled for

        //check for tri-formation first
        if($member->totalLeftActiveBA > 0 && $member->totalRightActiveBA > 0 ){
            $member->isTriformation = true; //tri-formation reached
        }else{
            //if there is no triformation, this should be false and break
            $this->addRankToSponsors($member->sponsorParent, 2); // where 2 indicates BA
            return false;
        }

        /**
         * Add rank information of this user's lines (2 BA per line x 2, 2 SD per line x 2)
         */
        foreach($member->sponsorLineRankDetails as &$details){
            $minimumRank = $details[0];
            $rankCount = $details[1]; // at least how many in single line

            foreach($member->firstLevelSponsorChild as $child){
                $totalMatches = 0;
                foreach($child->totalRanks as $rankId => $total){
                    if($rankId >= $minimumRank){
                        $totalMatches += $total;
                    }
                }
                //add in the child rank as well
                if($child->achievedRankId >= $minimumRank){
                    $totalMatches++;
                }

                if($totalMatches >= $rankCount){
                    $details[2]++; // add in the count of the sponsor line detail by 1
                }
            }
        }

        /**
         * if there is a maximum rank that is achieved by this guy,
         * lets check the minimum qualification of sponsored downlines
         */
        if($maximumRank){
            for($i = $maximumRank; $i > 0; $i-- ){
                if(isset($this->rankings[$i]['skip']) && $this->rankings[$i]['skip']){
                    //skip if needed, this is used to disable some of the rankings
                    continue;
                }

                $minimumLines = $this->rankings[$i]['lines'];

                //this user might be eligible for this rank, let's check for the total ranks he has under him
                $minimumRanking = $this->rankings[$i]['minimum_downlines_rank'];

                //total minimum rank in a line
                $minimumRankPerLine = $this->rankings[$i]['total_rank_per_line'];

                //minimum active BAs needed direct/indirect (to cater SBA, RBA and EBA)
                $minimumActiveBa = $this->rankings[$i]['minimum_active_ba'];

                $totalLinesMatch = 0; //total lines that hits the requirements of the ranks

                foreach($member->firstLevelSponsorChild as $child){
                    $totalMatchedMembers = 0; // total ranks that is found in the child
                    //we will loop all the ranks under the child

                    if($child->achievedRankId >= $minimumRanking ){
                        $totalLinesMatch++;
                        continue;
                    }

                    if($minimumRanking == 2){ // 2 indicates BA
                        // if we are comparing normal active BA, we do not have to use the totalRanks as long as we find Active BA hits the requirements
                        $totalMatchedMembers += $child->totalActiveBA;
                        if($child->isActive){
                            // if this child is active, he is a BA. note that totalactiveba is only for the counts from downlines
                            $totalMatchedMembers += 1;
                        }

                        if($totalMatchedMembers >= $minimumRankPerLine){
                            $totalLinesMatch++;
                        }
                    }

                    // this will be used to cater those with more than normal BA ranks
                    foreach($child->totalRanks as $key=>$rank){ //key = id of ranks, value = total ranks under this user

                        //if it doesnt hit the minimum ranking and make sure if BA, active BA is counted.
                        if($minimumRanking > $key ){
                            continue;
                        }

                        $totalMatchedMembers += $child->totalRanks[$key];
                        if($totalMatchedMembers >= $minimumRankPerLine){
                            $totalLinesMatch++;
                            break;
                        }
                    }
                }

                //special campaign until end of 2018
                // the logic is having at least 3 SD, RD or ED in 2 lines.
                switch($this->rankings[$i]['rank']){
                    case 'SC' :
                        //for SC, must have at least 3 SD in at least 2 lines
                        if($member->sponsorLineRankDetails['total_sponsor_unique_line_2_SD'] >= 2 ||
                            $member->sponsorLineRankDetails['total_sponsor_unique_line_1_SD'] >= 3||
                            ($member->sponsorLineRankDetails['total_sponsor_unique_line_2_SD'] == 1 &&
                                $member->sponsorLineRankDetails['total_sponsor_unique_line_1_SD'] >= 2)
                        ){
                           $totalLinesMatch = 3;
                        }
                        break;
                    case 'RC' :
                        //for RC, must have at least 3 RD in at least 2 lines
                        if($member->sponsorLineRankDetails['total_sponsor_unique_line_2_RD'] >= 2 ||
                            $member->sponsorLineRankDetails['total_sponsor_unique_line_1_RD'] >= 3||
                            ($member->sponsorLineRankDetails['total_sponsor_unique_line_2_RD'] == 1 &&
                                $member->sponsorLineRankDetails['total_sponsor_unique_line_1_RD'] >= 2)
                        ){
                            $totalLinesMatch = 3;
                        }
                        break;
                    case 'EC' :
                        //for EC, must have at least 3 ED in at least 2 lines
                        if($member->sponsorLineRankDetails['total_sponsor_unique_line_2_ED'] >= 2 ||
                            $member->sponsorLineRankDetails['total_sponsor_unique_line_1_ED'] >= 3||
                            ($member->sponsorLineRankDetails['total_sponsor_unique_line_2_ED'] == 1 &&
                                $member->sponsorLineRankDetails['total_sponsor_unique_line_1_ED'] >= 2)
                        ){
                            $totalLinesMatch = 3;
                        }
                        break;
                }

                if($totalLinesMatch >= $minimumLines && $member->totalActiveBA >= $minimumActiveBa){

                    //check if there is a special rank that we need to validate
                    if(isset($this->rankings[$i]['placementMatch'])){
                        $placementLeftNeeded = $this->rankings[$i]['placementMatch'][0]; // total needed on left
                        $placementRightNeeded = $this->rankings[$i]['placementMatch'][1]; // total needed on right
                        $minRank = $this->rankings[$i]['placementMatch'][2]; // minimum rank needed

                        if($minRank == 2){
                            //if we only need to search for 2, all we need is to search for active members
                            if($member->totalLeftActiveBA >= $placementLeftNeeded && $member->totalRightActiveBA >= $placementRightNeeded){
                                //means he hit the requirements, nothing else to check here, continue with the script
                            }else{
                                //failed, check next rank
                                continue;
                            }
                        }else{
                            $leftMatches = 0;
                            $rightMatches = 0;
                            $pass = false; // meet the requirements

                            foreach($member->placementRankLeft as $rankId=>$total){
                                if($rankId >= $minRank){
                                    $leftMatches = $placementLeftNeeded - $total;
                                }

                                if($leftMatches <= 0 ){
                                    $pass = true;
                                    break;
                                }
                            }

                            if($pass){
                                $pass = false; // it has to pass the condition below first
                                foreach($member->placementRankRight as $rankId=>$total){
                                    if($rankId >= $minRank){
                                        $rightMatches = $placementRightNeeded - $total;
                                    }

                                    if($rightMatches <= 0 ){
                                        $pass = true;
                                        break;
                                    }
                                }
                            }

                            if(!$pass){
                                // go and check next rank
                                continue;
                            }
                        }
                    }

                    $requirementMet = $i; // the rank that this user is eligible for

                    //special bonus if applicable
                    if(isset($this->rankings[$i]['special_bonus_amt'])){
                        $member->totalSpecialBonus = $this->rankings[$i]['special_bonus_amt'];
                    }

                    $member->achievedRankId = $i;
                    $member->totalQuarterlyDividendShares = isset($this->totalQuarterlyDividendShares[$i]) ?
                        $this->totalQuarterlyDividendShares[$i] : 0;
                    $this->rankCollection[$i]++;

                    // pass this info to the upline in sponsor network
                    if(isset($member->sponsorParent)){
                        $this->addRankToSponsors($member->sponsorParent, $requirementMet);
                    }
                    break;
                }

                if($requirementMet){
                    break;
                }
            }
        }else{
            // means that this member will remains a BA
            $this->addRankToSponsors($member->sponsorParent, 2); // where 2 indicates BA
            return false;
        }

        //To calculate bonuses when the requirements are met
        if($requirementMet){
            $member->calculationCv = $member->payLegCvWithOps;

            // this rank should be a configurable
            if($member->totalUniqueLeftActiveBA >= 3 && $member->totalUniqueRightActiveBA >= 3){

                //if we can get into here, and member personal Sales CV is lesser than 60, he must be mega qualified
                if($member->isActiveByMegaCv){
                    $member->eligibleTeamBonusPercent = $this->rentalOverrideValues['teamBonusThreeLeftThreeRight'];
                }else{
                    $member->eligibleTeamBonusPercent = $this->teamBonusThreeLeftThreeRight;
                }

            }else{
                //if we can get into here, and member personal Sales CV is lesser than 60, he must be mega qualified
                if($member->isActiveByMegaCv){
                    $member->eligibleTeamBonusPercent = $this->rentalOverrideValues['teamBonusOneLeftOneRight'];
                }else{
                    // if we have more than 1 active ba from each side, this must be 10%,
                    $member->eligibleTeamBonusPercent = $this->teamBonusOneLeftOneRight;
                }
                //only if both sides are more than 3, this guy are eligible to draw 15% team bonus
            }

            $member->totalTeamBonus = $member->payLegCvWithOps * $member->eligibleTeamBonusPercent / 100;

            if(($member->totalTeamBonus > $this->rankings[$member->achievedRankId]['max_cap'])){
                //if the amount is larger, we need to do a backward calculation for the calculation cv to get the flushed value
                $cappedAmount = $this->rankings[$member->achievedRankId]['max_cap'];

                $member->totalTeamBonus = ($member->totalTeamBonus > $cappedAmount) ?
                        $cappedAmount : $member->totalTeamBonus;
                $member->calculationCv = round($cappedAmount * 100 / $member->eligibleTeamBonusPercent);
                $member->flushedCv = $member->payLegCvWithOps - $member->calculationCv;
            }

            $member->dilutionAmount = $this->dilutionAmount;
            $member->totalTeamBonusDiluted = $member->totalTeamBonus * $this->dilutionAmount;

            //check limitation of the carry forward CV
            $maxBringForward = ($member->highest_rank_id > 0) ?
                $this->rankings[$member->highest_rank_id]['max_carry_forward'] :
                $this->rankings[$member->achievedRankId]['max_carry_forward'] ;

            $flushedCarryForwardCv = 0;

            $bringOverCv -= $member->calculationCv;

            if($bringOverCv > $maxBringForward){
                $flushedCarryForwardCv = $bringOverCv - $maxBringForward;
                $bringOverCv = $maxBringForward;
            }


            //to finish populating the carry forward information
            if($member->carryForwardPos == 1){
                //powerleg is left
                $member->teamBonusDetails['left'] = array_merge($member->teamBonusDetails['left'], [
                    'gcv_leg_group' => 'POWER',
                    'gcv_bring_over' => $bringOverCv,
                    'gcv_flush' => $flushedCarryForwardCv,
                    'gcv_calculation' => $member->calculationCv
                ]);

                $member->teamBonusDetails['right'] = array_merge($member->teamBonusDetails['right'], [
                    'gcv_leg_group' => 'PAY',
                    'gcv_flush' => $member->flushedCv,
                    'gcv_calculation' => $member->calculationCv,
                    'team_bonus_percentage' => $member->eligibleTeamBonusPercent,
                    'gcv_bring_over' => 0,
                    'team_bonus_cv' => $member->totalTeamBonus
                ]);
            }else{
                //powerleg is right
                $member->teamBonusDetails['right'] = array_merge($member->teamBonusDetails['right'], [
                    'gcv_leg_group' => 'POWER',
                    'gcv_bring_over' => $bringOverCv,
                    'gcv_flush' => $flushedCarryForwardCv,
                    'gcv_calculation' => $member->calculationCv
                ]);

                $member->teamBonusDetails['left'] = array_merge($member->teamBonusDetails['left'], [
                    'gcv_leg_group' => 'PAY',
                    'gcv_flush' => $member->flushedCv,
                    'gcv_calculation' => $member->calculationCv,
                    'team_bonus_percentage' => $member->eligibleTeamBonusPercent,
                    'gcv_bring_over' => 0,
                    'team_bonus_cv' => $member->totalTeamBonus
                ]);
            }

            return true;
        }else{
            if(isset($member->sponsorParent)){
                $this->addRankToSponsors($member->sponsorParent, 2); // where 2 indicates BA
            }
        }
        return false;
    }

    private function findSponsorPositionInPlacement($child, $member){
        if(isset($child->placementParent) && $member->user_id == $child->placementParent->user_id){
            //now we can check which position of this node in the parent
            if(isset($member->placementLeft->user_id) &&  $member->placementLeft->user_id == $child->user_id){
                return 1; // this user is on the left of the member
            }
            return 2; // this user is on the right of the member
        }elseif(isset($child->placementParent)){
            return $this->findSponsorPositionInPlacement($child->placementParent, $member);
        }

        return false;
    }

    public function getMemberTree()
    {
        return $this->membersTree;
    }

    /*******************************************************************************************************************
     * Building relationship between member @todo refactor this into another class for single responsibility
     * ***************************************************************************************************************/

    public function getSponsorChild($memberId, $levels = 10)
    {
        $info = array();
        $this->membersTree[$memberId]->getSponsorChild($this->membersTree, $levels, $info);
        return $info;
    }

    public function getPlacementChild($memberId, $levels = 10)
    {
        $info = array();
        $this->membersTree[$memberId]->getPlacementChild($this->membersTree, $levels, $info);
        return $info;
    }


    /*******************************************************************************************************************
     * Printout for debugging and short reports
     * ****************************************************************************************************************/

    private function printParent($member)
    {
        $this->printMember($member);
        if(isset($member->sponsorParent)){
            $this->printParent($member->sponsorParent);
        }
    }

    public function getTopPlacementMember($member)
    {
        echo $member->user_id.'<br/>';
        if(isset($member->placementParent)){
            $this->getTopPlacementMember($member->placementParent);
        }

        echo $member->user_id;
        die;
    }

    /**
     * Prints all the info upwards in sponsor tree
     * @param $member
     */
    private function printSponsorParentUpward($member){
        if(isset($member->sponsorParent)){
            $this->printMember($member);
            $this->printSponsorParentUpward($member->sponsorParent);
        }
    }

    /**
     * Prints all the info upwards in placement tree
     * @param $member
     */
    private function printPlacementParentUpward($member){
        if(isset($member->placementParent)){
            $this->printMember($member);
            $this->printPlacementParentUpward($member->placementParent);
        }
    }

    /**
     * Will print out all the info of the placement under the member, only those with personal sales
     * @param $member
     */
    public function printPlacementChildInfo($member)
    {
        if($member->personalSalesCv > 0){
            echo $member->user->old_member_id.','.$member->personalSalesCv.'<br/>';
        }
        if(isset($member->placementLeft)){
            if($member->placementLeft->personalSalesCv > 0){
                //echo $member->user->old_member_id.','.$member->personalSalesCv.'<br/>';
            }
            $this->printPlacementChildInfo($member->placementLeft);
        }

        if(isset($member->placementRight)){
            if($member->placementRight->personalSalesCv > 0){
                //echo $member->user->old_member_id.','.$member->personalSalesCv.'<br/>';
            }
            $this->printPlacementChildInfo($member->placementRight);
        }

        return;
    }

    /**
     * Populate information of all the payout based on Ranks
     */
    private function populateBonusInfo()
    {

        $totalSpecialBonusPayout = 0;
        $totalPayout = 0;
        foreach($this->activeList as $memberId){

            $mem = $this->membersTree->get($memberId);
            if($mem->totalTeamBonus > 0){
                // echo $mem->user->old_member_id.','.$mem->totalTeamBonus.'<br/>';
                // $this->printMember($mem);

            }

            //adding in payout by rank

            $this->totalPayoutByRanks[$mem->achievedRankId]['team_bonus'] =
                $this->totalPayoutByRanks[$mem->achievedRankId]['team_bonus'] +
                $mem->totalTeamBonus;

            $this->totalPayoutByRanks[$mem->achievedRankId]['mentor_bonus'] =
                $this->totalPayoutByRanks[$mem->achievedRankId]['mentor_bonus'] +
                $mem->totalMentorBonus;

            $this->totalPayoutByRanks[$mem->achievedRankId]['special_bonus'] =
                $this->totalPayoutByRanks[$mem->achievedRankId]['special_bonus'] +
                $mem->totalSpecialBonus;

            $this->totalMentorBonusPayout += $mem->totalMentorBonus;
            $this->totalTeamBonusPayout += $mem->totalTeamBonus;
            $this->totalSpecialBonusPayout += $mem->totalSpecialBonus;
        }

        $this->membersWithWelcomeBonus = array_unique($this->membersWithWelcomeBonus);
        foreach($this->membersWithWelcomeBonus as $memberId){
            $mem = $this->membersTree->get($memberId);
            $this->totalWelcomeBonusPayout += $mem->totalWelcomeBonus;
            $this->totalPayoutByRanks[$mem->achievedRankId]['welcome_bonus'] =
                $this->totalPayoutByRanks[$mem->achievedRankId]['welcome_bonus'] +
                $mem->totalWelcomeBonus;
        }

        //format payout
        foreach($this->totalPayoutByRanks as &$payout){
            $payout['welcome_bonus'] = round($payout['welcome_bonus'], 2);
            $payout['special_bonus'] = round($payout['special_bonus'], 2);
            $payout['loyal_bonus'] = round($payout['loyal_bonus'], 2);
            $payout['team_bonus'] = round($payout['team_bonus'], 2);
            $payout['mentor_bonus'] = round($payout['mentor_bonus'], 2);
        }
        $this->totalTeamBonusPayout = round($this->totalTeamBonusPayout, 2);
        $this->totalMentorBonusPayout = round($this->totalMentorBonusPayout, 2);
        $this->totalSpecialBonusPayout = round($this->totalTeamBonusPayout, 2);
    }

    public function printJSON()
    {
        $results = array(
            'total_sales_cv' => $this->totalSalesCv, //total sales made
            'total_payout_by_ranks' => $this->totalPayoutByRanks,
            'total_welcome_bonus_payout' => $this->totalWelcomeBonusPayout,
            'total_team_bonus_payout' => $this->totalTeamBonusPayout,
            'total_mentor_bonus_payout' => $this->totalMentorBonusPayout,
            'cw_id' => $this->cwId,
            'achieved_ranks' => $this->rankCollection,
            'rankings_info' => $this->rankings
        );

        echo '<pre>';
        print_r($results);
        die;

        //this can be integrated in the bonus
        echo json_encode($results);
    }

    public function printMember($member)
    {
        //echo $member->user->old_member_id.','.$member->totalMentorBonus.'<br/>';
        //return;
        $info  = 'Name : ' . $member->name . '<br/>';
        $info .= 'user_id : ' . $member->user_id . '<br/>';
        $info .= 'Member type : ' . $member->memberType . '<br/>';
        $info .= 'Member old_member_id : ' . $member->user->old_member_id . '<br/>';
        if(isset($this->rankings[$member->achievedRankId])){
            $info .= 'Rank achieved in this CW : ' . $this->rankings[$member->achievedRankId]['name'] . '<br/>';
        }
        $info .= 'Carried Forward Left : ' . $member->carryForwardInfo['left_amount']  . '<br/>';
        $info .= 'Carried Forward Right : ' . $member->carryForwardInfo['right_amount']  . '<br/>';
        $info .= '======Sponsor Detail=====<br/>';
        $info .= (!isset($member->sponsorParent->user_id)) ? '' : 'Sponsor user_id : ' . $member->sponsorParent->user_id. '<br/>';
        $info .= (!isset($member->sponsorParent->user_id)) ? '' : 'Sponsor old_member_id : ' . $member->sponsorParent->user->old_member_id. '<br/>';
        $info .= 'Total unique sponsor in left placement : ' . $member->totalUniqueLeftActiveBA . '<br/>';
        $info .= 'Total unique sponsor in right placement : ' . $member->totalUniqueRightActiveBA  . '<br/>';
        $info .= '======Placement Detail=====<br/>';
        //$info .= 'Placement f_id : ' . $member->placementParent->id . '<br/>';
        //$info .= 'Placement f_code : ' . $member->placementParent->f_code . '<br/>';
        $info .= 'Total Active BA (Sponsor) : ' . $member->totalActiveBA . '<br/>';
        $info .= 'Total Active BA in left : ' . $member->totalLeftActiveBA . '<br>';
        $info .= 'Total Active BA in right : ' . $member->totalRightActiveBA . '<br>';
        $info .= 'Total Left Leg GCV : '. $member->accumulatedLeftCv . '<br/>';
        $info .= 'Total Right Leg GCV: '. $member->accumulatedRightCv . '<br>';
        $info .= 'Carry Forward Next CV : ' . $member->carryForwardNextCv . '<br>';
        $info .= 'Total Personal sales: USD ' . $member->personalSalesCv . '<br>';
        $info .= 'Welcome Bonus : USD '.$member->totalWelcomeBonus. '<br>';
        $info .= 'Mentor Bonus : USD '.$member->totalMentorBonus. '<br>';
        $info .= 'Special Bonus : USD '.$member->totalSpecialBonus. '<br>';
        $info .= 'Total GCV: ' . $member->totalCv . '<br>';
        $info .= 'Total Team Bonus: USD ' . $member->totalTeamBonusDiluted . '<br>=============================<br><br>';
        ob_start();
        print_r($member->mentorBonusInfo);
        $info .= ob_get_contents();
        ob_end_clean();
        echo $info;
    }

    /**
     * Not used. For future use if needed
     */

    /**
     * To build a compressed tree for mentor bonus
     * @param $member
     */
    private function buildCompressedSponsorTree($member)
    {
        $member->firstLevelCompressedSponsorChild = $this->findSponsorChildByRank($member);

        foreach($member->firstLevelCompressedSponsorChild as $compressedChild){
            $this->buildCompressedSponsorTree($compressedChild);
        }
    }

    /**
     * To be used for mentor bonus only
     * @param $member
     * @return array
     */
    private function findSponsorChildByRank($member)
    {
        //check if all the child in this levels has at least one that hits the minimum rank
        $members = array(); // found member user_id
        foreach($member->firstLevelSponsorChild as $child){
            if(!($child->firstLevelSponsorChild) || //if the child is at an end of a node, it should be counted as one generation as well
                $child->achievedRankId >= $this->minimumRankForMentorBonus) {
                $members[] = $child;
            }else{
                //find until u find it!
                $memberChild = $this->findSponsorChildByRank($child);
                $members = array_merge($members, $memberChild);
            }
        }

        return $members;
    }
}
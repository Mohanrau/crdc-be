<?php
namespace App\Helpers\Traits;

trait Bonus
{
    public $totalCv = 0; // total cv accumulated from all the childs in placement
    public $personalSalesCv = 0; // this is the cv generated from this user personal sales
    public $dilutionAmount = 0; // 0.9xxxxxx
    public $membersCv = 0; // all cv passed up by member/premier member
    public $isTriformation = false;

    /**
     * Welcome bonus Related
     */

    public $eligibleCommissionLevel = 0; // welcome bonus draw up to this level
    public $totalWelcomeBonus = 0; // total welcome bonus Commission
    public $welcomeBonusDetails = [];

    /**
     * Team Bonus Related
     */
    public $accumulatedLeftCv = 0; // accumulated left GCV
    public $accumulatedRightCv = 0; // accumulated right GCV
    public $carryForwardInfo = array(
        'left_amount' => 0,
        'left_passed_by' => 0,
        'right_amount' => 0,
        'right_passed_by' => 0
    ); // array('left_amount'=>0, 'left_passed_by'=>'12', 'right_passed_by' 'right_amount'=>100)
    public $teamBonusDetails = []; // this will consist of all the information from the calculated CW team bonus details
    public $eligibleTeamBonusPercent = 0; //percentage of withdrawal of the member
    public $totalPassedToParentSponsor = 0; // total personal sales that has been passed to parent
    public $payLegCv = 0; // total cv in the payleg without OPS
    public $payLegCvWithOps = 0; // total cv in payleg with OPS
    public $totalTeamBonus = 0; // total team bonus payout
    public $carryForwardNextCv = 0; // cv to be carried forward next
    public $carryForwardNextPos = 0; // pos to be carried forward next
    public $ops = 0; // optimize personal sales amount
    public $flushedCv = 0;
    public $calculationCv = 0; //calculated gcv
    public $totalTeamBonusDiluted = 0; //total team bonus after being diluted

    /**
     * Loyal customer bonus
     * - Exclude WP sales, only counted from personal sales
     */
    public $loyalBonusAccumulation = 0; // populate this with the latest points.
    public $totalLoyalBonus = 0;

    /**
     * Mentor Bonus
     */
    public $totalMentorBonus = 0;
    public $totalMentorBonusDiluted = 0;
    public $mentorBonusDetails = [];

    /**
     * special bonus
     */
    public $totalSpecialBonus = 0;

    /**
     * Quarterly Dividend
     */
    public $totalQuarterlyDividend = 0;
    public $totalQuarterlyDividendShares = 0;

    /**
     * @param $amount
     */
    public function addWelcomeBonusCommission($amount)
    {
        $this->totalWelcomeBonus += $amount;
    }

    public function addPersonalSalesCv($amount)
    {
        $this->personalSalesCv += $amount;
    }

    public function addMemberSalesCv($amount)
    {
        $this->membersCv += $amount;
    }

    public function addTotalCv($amount)
    {
        $this->totalCv += $amount;
    }
}
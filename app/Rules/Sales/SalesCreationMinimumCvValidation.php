<?php
namespace App\Rules\Sales;

use App\Interfaces\{
    Masters\MasterInterface,
    Settings\SettingsInterface
};
use App\Models\{
    Members\Member,
    Bonus\EnrollmentRank,
    Enrollments\EnrollmentTemp
};
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class SalesCreationMinimumCvValidation implements Rule
{
    private
        $masterRepositoryObj,
        $settingsRepositoryObj,
        $enrollmentRankObj,
        $enrollmentTempObj,
        $memberObj,
        $userId,
        $cvType,
        $kitting,
        $products,
        $saleOrderStatusConfigCodes,
        $enrollmentStatusConfigCodes,
        $saleTypesConfigCodes,
        $minimumCv,
        $errorType;

    /**
     * SalesCreationMinimumCvValidation constructor.
     *
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param EnrollmentRank $enrollmentRank
     * @param EnrollmentTemp $enrollmentTemp
     * @param Member $member
     * @param int $userId
     * @param string $cvType
     * @param array $kitting
     * @param array $products
     */
    public function __construct
    (
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        EnrollmentRank $enrollmentRank,
        EnrollmentTemp $enrollmentTemp,
        Member $member,
        int $userId,
        string $cvType,
        array $kitting,
        array $products
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->settingsRepositoryObj = $settingsInterface;

        $this->enrollmentRankObj = $enrollmentRank;

        $this->memberObj = $member;

        $this->enrollmentTempObj = $enrollmentTemp;

        $this->userId = $userId;

        $this->cvType = $cvType;

        $this->kitting = $kitting;

        $this->products = $products;

        $this->minimumCv = 0;

        $this->saleOrderStatusConfigCodes = config('mappings.sale_order_status');

        $this->enrollmentStatusConfigCodes = config('mappings.enrollment_status');

        $this->saleTypesConfigCodes = config('mappings.sale_types');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = true;

        //Get Sale Type
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_types', 'sale_order_status', 'enrollment_status'));

        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')->toArray());

        $enrollmentStatus = array_change_key_case($settingsData['enrollment_status']
            ->pluck('id','title')->toArray());

        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']
            ->pluck('id','title')->toArray());

        $products = collect($this->products);

        $kitting = collect($this->kitting);

        if($this->cvType == "amp"){

            $formationId = $saleType[$this->saleTypesConfigCodes['formation']];

            $autoMaintenanceId = $saleType[$this->saleTypesConfigCodes['auto-maintenance']];

            $systemSettings = $this->settingsRepositoryObj
                ->getSettingDataByKey(array('minimum_amp_cv_per_sales'));

            $this->minimumCv = $systemSettings['minimum_amp_cv_per_sales'][0]->value;

            $ampProducts = $products->whereIn('transaction_type', [$formationId, $autoMaintenanceId])->first();

            $ampKitting = $kitting->whereIn('transaction_type', [$formationId, $autoMaintenanceId])->first();

            if($ampProducts || $ampKitting){

                $this->errorType = "lessAmpCv";

                $result = ($value >= $this->minimumCv) ? true : false;
            }

        } else if($this->cvType == "enrollmentUpgrade"){

            $lessUpgradeCv = 0;

            $completedSaleOrderStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

            $completedEnrollmentStatusId = $enrollmentStatus[strtolower($this->enrollmentStatusConfigCodes['completed'])];

            $registrationId = $saleType[$this->saleTypesConfigCodes['registration']];

            $formationId = $saleType[$this->saleTypesConfigCodes['formation']];

            $memberUpgradeId = $saleType[$this->saleTypesConfigCodes['member-upgrade']];

            $baUpgradeId = $saleType[$this->saleTypesConfigCodes['ba-upgrade']];

            $systemSettings = $this->settingsRepositoryObj
                ->getSettingDataByKey(array('minimum_ba_upgrade_cv_per_sales', 'current_cw_id'));

            $currentCwId = $systemSettings['current_cw_id'][0]->value;

            //Get Enrollment Record
            $enrollmentRecord = $this->enrollmentTempObj
                ->where('user_id', $this->userId)
                ->where('status_id', $completedEnrollmentStatusId)
                ->first();

            if($enrollmentRecord){

                $enrollmentInvoiceCwId = optional($enrollmentRecord->sale->invoices)->cw_id;

                if($enrollmentInvoiceCwId == $currentCwId){

                    $enrollmentProductCvs = $enrollmentRecord->sale
                        ->saleProducts()
                        ->whereIn('transaction_type_id', [$registrationId, $formationId])
                        ->sum('cv4');

                    $enrollmentKittingCvs = $enrollmentRecord->sale
                        ->saleKittingClone()
                        ->whereIn('transaction_type_id', [$registrationId, $formationId])
                        ->sum('cv4');

                    $lessUpgradeCv = $enrollmentProductCvs + $enrollmentKittingCvs;
                }
            }

            //Get Member Record
            $memberDetail = $this->memberObj
                ->where("user_id", $this->userId)
                ->first();

            $memberEnrollmentRankLevel = $memberDetail->enrollmentRank->entitlement_lvl;

            $nextEnrollmentDetail = $this->enrollmentRankObj
                ->where("entitlement_lvl", ">", $memberEnrollmentRankLevel)
                ->orderBy("entitlement_lvl", "asc")
                ->first();

            $this->minimumCv = (($nextEnrollmentDetail) ? $nextEnrollmentDetail->CV :
                $systemSettings['minimum_ba_upgrade_cv_per_sales'][0]->value) - $lessUpgradeCv;

            $upgradeProducts = $products->whereIn('transaction_type', [$memberUpgradeId, $baUpgradeId])->first();

            $upgradeKitting = $kitting->whereIn('transaction_type', [$memberUpgradeId, $baUpgradeId])->first();

            if($upgradeProducts || $upgradeKitting){

                $this->errorType = "lessEnrollmentUpgradeCv";

                $result = ($value >= $this->minimumCv) ? true : false;
            }
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if($this->errorType == "lessAmpCv"){
            return __('message.sales.minimum-amp-cv',[
                'minimumCv' => $this->minimumCv
            ]);
        } else if ($this->errorType == "lessEnrollmentUpgradeCv"){
            return __('message.sales.minimum-enrollment-upgrade-cv',[
                'minimumCv' => $this->minimumCv
            ]);
        }
    }
}

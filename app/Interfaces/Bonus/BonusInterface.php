<?php
namespace App\Interfaces\Bonus;

interface BonusInterface
{
	/**
     * Get CW Bonus Report
     *
     * @param int $cwId
     * @param array $userIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCwBonusReport(int $cwId, array $userIds);

    /**
     * Get bonus statement based on CW name and distributor code
     *
     * @param int $cwId
     * @param array $userIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBonusStatement(int $cwId, array $userIds);

    /**
     * Get Yearly Income statement
     *
     * @return \Illuminate\Support\Collection
     */
    public function getYearlyIncomeStatement();

    /**
     * Get Yearly Income summary
     * @param int $year
     * @param int $countryId
     * @param array $userIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getYearlyIncomeSummary(int $year, int $countryId, array $userIds);

    /**
     * Get CP-58 form
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCP58Form();

    /**
     * Get Yearly Bonus Statement - LHDN excel
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLHDNsheet();

    /**
     * Get Yearly Bonus Statement - CP-37F form
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCp37fForm();

    /**
     * Get Self Billed Invoice
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSelfBilledInvoice();

    /**
     * Get Self Billed Invoice - Stockist
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSelfBilledInvoiceStockist();

    /**
     * Get Stockist Commission statement
     *
     * @param int $cwId
     * @param int $stockistId
     * @return \Illuminate\Support\Collection
     */
    public function getStockistCommissionStatement($cwId, $stockistId);

    /**
     * Get Sponsor Tree report
     *
     * @param int $cwId
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getSponsorTree(int $cwId, int $userId);

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIncentiveSummary();

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWelcomeBonusSummary();

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWelcomeBonusDetail();

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBonusAdjustmentListing();

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function get77kReport();

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWhtReport();
}
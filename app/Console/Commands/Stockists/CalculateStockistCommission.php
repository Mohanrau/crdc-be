<?php

namespace App\Console\Commands\Stockists;

use App\{
    Interfaces\Currency\CurrencyInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface,
    Models\Currency\Currency,
    Models\Invoices\Invoice,
    Models\Locations\LocationTypes,
    Models\Sales\Sale,
    Models\Sales\SaleKittingClone,
    Models\Sales\SaleProduct,
    Models\Stockists\Stockist,
    Models\Stockists\StockistCommission,
    Models\Stockists\StockistTypeDetail
};
use Illuminate\Console\Command;

class CalculateStockistCommission extends Command
{
    protected
        $currencyRepositoryObj,
        $masterRepositoryObj,
        $settingsRepositoryObj,
        $currencyObj,
        $invoiceObj,
        $locationTypesObj,
        $saleObj,
        $saleKittingCloneObj,
        $saleProductObj,
        $stockistObj,
        $stockistCommissionObj,
        $stockistTypeDetailObj,
        $saleOrderStatusConfigCodes,
        $transactionTypeConfigCodes,
        $locationTypeConfigCodes;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stockist:calculate-stockist-commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @param CurrencyInterface $currencyInterface
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param Currency $currency
     * @param Invoice $invoice
     * @param LocationTypes $locationTypes
     * @param Sale $sale
     * @param SaleKittingClone $saleKittingClone
     * @param SaleProduct $saleProduct
     * @param Stockist $stockist
     * @param StockistCommission $stockistCommission
     * @param StockistTypeDetail $stockistTypeDetail
     * @return void
     */
    public function __construct
    (
        CurrencyInterface $currencyInterface,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        Currency $currency,
        Invoice $invoice,
        LocationTypes $locationTypes,
        Sale $sale,
        SaleKittingClone $saleKittingClone,
        SaleProduct $saleProduct,
        Stockist $stockist,
        StockistCommission $stockistCommission,
        StockistTypeDetail $stockistTypeDetail
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.stockist-commission-daily-update');

        $this->currencyRepositoryObj = $currencyInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->settingsRepositoryObj = $settingsInterface;

        $this->currencyObj = $currency;

        $this->invoiceObj = $invoice;

        $this->locationTypesObj = $locationTypes;

        $this->saleObj = $sale;

        $this->saleKittingCloneObj = $saleKittingClone;

        $this->saleProductObj = $saleProduct;

        $this->stockistObj = $stockist;

        $this->stockistCommissionObj = $stockistCommission;

        $this->stockistTypeDetailObj = $stockistTypeDetail;

        $this->saleOrderStatusConfigCodes = config('mappings.sale_order_status');

        $this->transactionTypeConfigCodes = config('mappings.sale_types');

        $this->locationTypeConfigCodes = config('mappings.location_type_code');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Get order status  mapping id
        $mastersData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_order_status', 'sale_types'));

        $saleOrderStatusValues = array_change_key_case(
            $mastersData['sale_order_status']->pluck('id','title')->toArray());

        $saleCompleteStatusId = $saleOrderStatusValues[
            $this->saleOrderStatusConfigCodes['completed']];

        //Get sale type mapping id
        $saleType = array_change_key_case($mastersData['sale_types']
            ->pluck('id','title')->toArray());

        $registrationSaleTypeId = $saleType[$this->transactionTypeConfigCodes['registration']];

        $formationSaleTypeId = $saleType[$this->transactionTypeConfigCodes['formation']];

        //Get location type id
        $onlineLocationType = $this->locationTypesObj
            ->where('code', $this->locationTypeConfigCodes['online'])
            ->first();

        $stockistLocationType = $this->locationTypesObj
            ->where('code', $this->locationTypeConfigCodes['stockist'])
            ->first();

        $onlineLocationTypeId = $onlineLocationType->id;

        $stockistLocationTypeId = $stockistLocationType->id;

        //Get stockist commission type
        $commissionTypeDetails = $this->stockistTypeDetailObj
            ->get();

        $commissionTypeDetails = collect($commissionTypeDetails)
            ->keyBy('stockist_type_id');

        //Get current cw id and base currency
        $systemSettings = $this->settingsRepositoryObj
            ->getSettingDataByKey(array('current_cw_id', 'base_currency'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        $baseCurrencyCode = $systemSettings['base_currency'][0]->value;

        //Get base currency id
        $baseCurrencyDetail = $this->currencyObj
            ->where('code', $baseCurrencyCode)
            ->active()
            ->first();

        $baseCurrencyId = $baseCurrencyDetail->id;

        //Delete commission record within current cw id
        $this->stockistCommissionObj
            ->where('cw_id', $currentCwId)
            ->delete();

        //Get completed invoice and sale record within current cw
        $invoices = $this->invoiceObj
            ->join('sales', function ($join){
                $join->on('sales.id', '=', 'invoices.sale_id');
            })
            ->where('invoices.cw_id', $currentCwId)
            ->where('sales.order_status_id', $saleCompleteStatusId)
            ->whereIn('sales.channel_id', [$stockistLocationTypeId, $onlineLocationTypeId])
            ->select('sales.*')
            ->get();

        $stockistCommisions = [];

        collect($invoices)->each(function($invoiceData)
            use (
                $commissionTypeDetails,
                $currentCwId,
                $baseCurrencyId,
                $onlineLocationTypeId,
                $registrationSaleTypeId,
                $formationSaleTypeId,
                &$stockistCommisions
            ){

                $saleRecord = $this->saleObj
                    ->find($invoiceData->id);

                $comissionType = ($invoiceData->channel_id == $onlineLocationTypeId) ? "online" : "otc";

                $saleTransactionLoction = $saleRecord->transactionLocation()->first();

                $stockist = $this->stockistObj
                    ->where('stockist_number', $saleTransactionLoction->code)
                    ->first();

                if(!$stockist){
                    return true;
                }

                //Get sale product record
                $products = $this->saleProductObj
                    ->where('sale_id', $invoiceData->id)
                    ->get();

                //Get sale kitting record
                $kittings = $this->saleKittingCloneObj
                    ->where('sale_id', $invoiceData->id)
                    ->get();

                //Calculate wp cv
                $totalWpCv = collect($products)
                    ->whereIn('transaction_type_id', [$registrationSaleTypeId, $formationSaleTypeId])
                    ->sum(function ($product) {
                        return $product['cv3'] * $product['quantity'];
                    });

                $totalWpCv += collect($kittings)
                    ->whereIn('transaction_type_id', [$registrationSaleTypeId, $formationSaleTypeId])
                    ->sum(function ($kitting) {
                        return $kitting['cv3'] * $kitting['quantity'];
                    });

                //Calculate other cv
                $totalOtherCv = collect($products)
                    ->where('transaction_type_id', '!=', $registrationSaleTypeId)
                    ->sum(function ($product) {
                        return $product['cv2'] * $product['quantity'];
                    });

                $totalOtherCv += collect($kittings)
                    ->where('transaction_type_id', '!=', $registrationSaleTypeId)
                    ->sum(function ($kitting) {
                        return $kitting['cv2'] * $kitting['quantity'];
                    });

                //Get Stockist Currency Details
                $stockistCurrencyId = $stockist->country->default_currency_id;

                $stockistCurrencyConversionRate = $this->currencyRepositoryObj
                    ->getCurrenciesConversionsRate($baseCurrencyId, $stockistCurrencyId);

                //Get stockist country tax rate
                $stockistTaxDetail = $stockist->country->taxes()->first();

                $stockistTax = $stockistTaxDetail->rate;

                //Get Stockist commission Processing Type Setup
                $commissionSetupDetail = $commissionTypeDetails[$stockist->stockist_type_id];

                //Calculate stockist commission based on online and otc commission rate
                $wpCommissionPercentage = ($comissionType == "online") ?
                    $commissionSetupDetail->online_wp_percentage :
                        $commissionSetupDetail->otc_wp_percentage;

                $wpAmount = floatval($totalWpCv) * floatval($wpCommissionPercentage) / 100;

                $othersCommissionPercentage = ($comissionType == "online") ?
                    $commissionSetupDetail->online_other_percentage :
                        $commissionSetupDetail->otc_other_percentage;

                $othersAmount = floatval($totalOtherCv) * floatval($othersCommissionPercentage) / 100;

                $totalCommissionCv = floatval($totalWpCv) + floatval($totalOtherCv);

                $totalCommissionAmount = floatval($wpAmount) + floatval($othersAmount);

                $totalLocalGrossAmount = floatval($stockistCurrencyConversionRate) *
                    floatval($totalCommissionAmount);

                $totalLocalTaxAmount = floatval($totalLocalGrossAmount) * floatval($stockistTax) / 100;

                $totalLocalNettsAmount = $totalLocalGrossAmount - $totalLocalTaxAmount;

                $commissionData = [
                    'cw_id' => $currentCwId,
                    'stockist_id' => $stockist->id,
                    'otc_wp_cv' => ($comissionType == "online") ? 0 : $totalWpCv,
                    'otc_wp_commission_percentage' => $commissionSetupDetail->otc_wp_percentage,
                    'otc_wp_amount' => ($comissionType == "online") ? 0 : $wpAmount,
                    'otc_others_cv' => ($comissionType == "online") ? 0 : $totalOtherCv,
                    'otc_others_commission_percentage' => $commissionSetupDetail->otc_other_percentage,
                    'otc_others_amount' => ($comissionType == "online") ? 0 : $othersAmount,
                    'total_otc_cv' => ($comissionType == "online") ? 0 : $totalCommissionCv,
                    'total_otc_amount' => ($comissionType == "online") ? 0 : $totalCommissionAmount,
                    'online_wp_cv' => ($comissionType == "online") ? $totalWpCv : 0,
                    'online_wp_commission_percentage' => $commissionSetupDetail->online_wp_percentage,
                    'online_wp_amount' => ($comissionType == "online") ? $wpAmount : 0,
                    'online_others_cv' => ($comissionType == "online") ? $totalOtherCv : 0,
                    'online_others_commission_percentage' => $commissionSetupDetail->online_other_percentage,
                    'online_others_amount' => ($comissionType == "online") ? $othersAmount : 0,
                    'total_online_cv' => ($comissionType == "online") ? $totalCommissionCv : 0,
                    'total_online_amount' => ($comissionType == "online") ? $totalCommissionAmount : 0,
                    'tax_company_name' => NULL,
                    'tax_no' => NULL,
                    'tax_type' => NULL,
                    'tax_rate' => 0,
                    'gross_commission' => $totalCommissionAmount,
                    'currency_rate' => $stockistCurrencyConversionRate,
                    'local_currency_id' => $stockistCurrencyId,
                    'total_gross_amount' => $totalLocalGrossAmount,
                    'total_tax_amount' => $totalLocalTaxAmount,
                    'total_nett_amount' => $totalLocalNettsAmount
                ];

                $stockistCommisions[$stockist->id][] = $commissionData;

            });

            collect($stockistCommisions)->each(function($stockistCommision, $stockistId)
                use ($currentCwId){

                    $commissionData = [
                        'cw_id' => $currentCwId,
                        'stockist_id' => $stockistId,
                        'otc_wp_cv' => collect($stockistCommision)->sum('otc_wp_cv'),
                        'otc_wp_commission_percentage' => collect($stockistCommision)->first()['otc_wp_commission_percentage'],
                        'otc_wp_amount' => collect($stockistCommision)->sum('otc_wp_amount'),
                        'otc_others_cv' => collect($stockistCommision)->sum('otc_others_cv'),
                        'otc_others_commission_percentage' => collect($stockistCommision)->first()['otc_others_commission_percentage'],
                        'otc_others_amount' => collect($stockistCommision)->sum('otc_others_amount'),
                        'total_otc_cv' => collect($stockistCommision)->sum('total_otc_cv'),
                        'total_otc_amount' => collect($stockistCommision)->sum('total_otc_amount'),
                        'online_wp_cv' => collect($stockistCommision)->sum('online_wp_cv'),
                        'online_wp_commission_percentage' => collect($stockistCommision)->first()['online_wp_commission_percentage'],
                        'online_wp_amount' => collect($stockistCommision)->sum('online_wp_amount'),
                        'online_others_cv' => collect($stockistCommision)->sum('online_others_cv'),
                        'online_others_commission_percentage' => collect($stockistCommision)->first()['online_others_commission_percentage'],
                        'online_others_amount' => collect($stockistCommision)->sum('online_others_amount'),
                        'total_online_cv' => collect($stockistCommision)->sum('total_online_cv'),
                        'total_online_amount' => collect($stockistCommision)->sum('total_online_amount'),
                        'tax_company_name' => NULL,
                        'tax_no' => NULL,
                        'tax_type' => NULL,
                        'tax_rate' => 0,
                        'gross_commission' => collect($stockistCommision)->sum('gross_commission'),
                        'currency_rate' => collect($stockistCommision)->first()['currency_rate'],
                        'local_currency_id' => collect($stockistCommision)->first()['local_currency_id'],
                        'total_gross_amount' => collect($stockistCommision)->sum('total_gross_amount'),
                        'total_tax_amount' => collect($stockistCommision)->sum('total_tax_amount'),
                        'total_nett_amount' => collect($stockistCommision)->sum('total_nett_amount')
                    ];

                    $this->stockistCommissionObj->create($commissionData);

                });

        $this->info('Stockist commission update successfully!');
    }
}

?>
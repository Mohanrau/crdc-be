<?php
namespace App\Listeners\Sales;

use App\Interfaces\{
    Masters\MasterInterface,
    Sales\SaleInterface,
    Campaigns\EsacVoucherInterface,
    Settings\SettingsInterface
};
use App\Models\{
    Sales\SaleKittingClone,
    Sales\SaleProduct,
    Sales\SaleCancellation,
    Sales\SaleCancellationProduct,
    Sales\CreditNote,
    Sales\Sale
};
use Illuminate\{
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Support\Facades\Auth,
    Support\Facades\Config
};
use App\Events\Sales\SaleCancellationEvents;

class SaleCancellationListeners
{
    private $saleRepositoryObj, $masterRepositoryObj, $saleCancellationObj,
        $settingRepositoryObj, $creditNoteObj, $saleObj, $saleProductObj,
        $saleKittingCloneObj, $saleCancellationProductObj, $esacVoucherRepositoryObj;

    /**
     * Create the event listener.
     *
     * @param SaleInterface $saleInterface
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param EsacVoucherInterface $esacVoucherInterface
     * @param Sale $sale
     * @param SaleKittingClone $saleKittingClone
     * @param SaleProduct $saleProduct
     * @param SaleCancellation $saleCancellation
     * @param SaleCancellationProduct $saleCancellationProduct
     * @param CreditNote $creditNote
     * @return void
     */
    public function __construct(
        SaleInterface $saleInterface,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        EsacVoucherInterface $esacVoucherInterface,
        Sale $sale,
        SaleKittingClone $saleKittingClone,
        SaleProduct $saleProduct,
        SaleCancellation $saleCancellation,
        SaleCancellationProduct $saleCancellationProduct,
        CreditNote $creditNote
    )
    {
        $this->saleRepositoryObj = $saleInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->esacVoucherRepositoryObj = $esacVoucherInterface;

        $this->saleObj = $sale;

        $this->saleKittingCloneObj = $saleKittingClone;

        $this->saleProductObj = $saleProduct;

        $this->saleCancellationObj = $saleCancellation;

        $this->saleCancellationProductObj = $saleCancellationProduct;

        $this->creditNoteObj = $creditNote;
    }

    /**
     * Handle the event.
     *
     * @param SaleCancellationEvents $event
     * @return mixed
     */
    public function handle(SaleCancellationEvents $event)
    {
        $saleCancellationId = $event->saleCancellationId;

        $action = $event->stepInput['trigger'];

        //Get config Code Mappings
        $saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');

        $saleCancellationStatusConfigCodes = Config::get('mappings.sale_cancellation_status');

        $stockistTransactionReleaseStatusConfigCodes =
            Config::get('mappings.stockist_daily_transaction_release_status');

        //Get Status ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_cancellation_status', 'sale_cancellation_mode',
                'sale_order_status', 'stockist_daily_transaction_release_status'));

        $statusValues = array_change_key_case(
            $settingsData['sale_cancellation_status']->pluck('id','title')->toArray()
        );

        $saleCancellation = $this->saleCancellationObj->where('id', $saleCancellationId);

        $invalidAction = false;

        switch (strtolower($action)) {
            case "sendapproval":
                $saleCancellation->where('cancellation_status_id', $statusValues[
                    $saleCancellationStatusConfigCodes['pending-approval']]);
                $statusUpdate = $saleCancellationStatusConfigCodes['approved'];
                $generateCreditNote = false;
                break;

            case "senddecline":
                $saleCancellation->where('cancellation_status_id', $statusValues[
                    $saleCancellationStatusConfigCodes['pending-approval']]);
                $statusUpdate = $saleCancellationStatusConfigCodes['rejected'];
                $generateCreditNote = false;
                break;

            case "processrefund":
                $saleCancellation->where('cancellation_status_id', $statusValues[
                    $saleCancellationStatusConfigCodes['pending-refund']]);
                $statusUpdate = $saleCancellationStatusConfigCodes['completed'];
                $generateCreditNote = false;
                break;

            case "generatecnwithcompletestatus":
                $saleCancellation->where('cancellation_status_id', $statusValues[
                    $saleCancellationStatusConfigCodes['pending-approval']]);
                $statusUpdate = $saleCancellationStatusConfigCodes['completed'];
                $generateCreditNote = true;
                break;

            case "samedaygeneratecnwithpendingrefundstatus":
                $saleCancellation->where('cancellation_status_id', $statusValues[
                    $saleCancellationStatusConfigCodes['pending-approval']]);
                $statusUpdate = $saleCancellationStatusConfigCodes['pending-refund'];
                $generateCreditNote = true;
                break;

            case "generatecn":
                $saleCancellation->where('cancellation_status_id', $statusValues[
                    $saleCancellationStatusConfigCodes['approved']]);
                $statusUpdate = $saleCancellationStatusConfigCodes['pending-refund'];
                $generateCreditNote = true;
                break;

            default :
                $invalidAction = true;
                $statusUpdate = NULL;
                $generateCreditNote = false;
                break;
        };

        $saleCancellationRecord = $saleCancellation->first();

        if($saleCancellationRecord && !$invalidAction){

            if($generateCreditNote){

                //get country id
                if(!$saleCancellationRecord->is_legacy){

                    $saleDetails = $this->saleRepositoryObj->find($saleCancellationRecord->sale_id);

                    $countryId = $saleDetails->country_id;

                } else {
                    $countryId = $saleCancellationRecord->legacyInvoice->country_id;
                }

                //Generate Credit Note
                $creditNoteData = [
                    'sale_id' => ($saleCancellationRecord->is_legacy) ? NULL : $saleCancellationRecord->sale_id,
                    'mapping_id' => $saleCancellationRecord->id,
                    'mapping_model' => 'sales_cancellations',
                    'credit_note_number' => $this->settingRepositoryObj->getRunningNumber(
                            'credit_note', $countryId, $saleCancellationRecord->transaction_location_id
                        ),
                    'credit_note_date' => date('Y-m-d'),
                ];

                Auth::user()->createdBy($this->creditNoteObj)->create($creditNoteData);

                if(!$saleCancellationRecord->is_legacy){

                    //Get sale order status ID
                    $saleOrderStatus = array_change_key_case(
                        $settingsData['sale_order_status']->pluck('id','title')->toArray()
                    );

                    //Update Available Product Quantity
                    $saleCancelKittingProductIds = [];

                    $saleCancellationProducts = $saleCancellationRecord->saleCancelProducts()->get();

                    collect($saleCancellationProducts)->each(function ($saleCancelProduct)
                        use (&$saleCancelKittingProductIds){

                            $saleProduct = $this->saleProductObj->find($saleCancelProduct->sale_product_id);

                            $availableQuantity = intval($saleProduct->available_quantity) -
                                intval($saleCancelProduct->quantity);

                            $saleProduct->update([
                                'available_quantity' => intval($saleProduct->available_quantity) -
                                    intval($saleCancelProduct->quantity),
                            ]);

                            array_push($saleCancelKittingProductIds, $saleCancelProduct->sale_product_id);
                    });

                    //Update Available Kitting Quantity
                    $kittingCloneIds = $this->saleProductObj
                        ->whereIn('id', $saleCancelKittingProductIds)
                        ->where('sale_id', $saleCancellationRecord->sale_id)
                        ->where('mapping_model', 'sales_kitting_clone')
                        ->whereNotNull('mapping_id')
                        ->select('mapping_id')
                        ->distinct()
                        ->get();

                    collect($kittingCloneIds)->each(function ($kittingCloneId)
                        use ($saleCancelKittingProductIds){

                            //Get correspoding kitting product
                            $kittingProduct = $this->saleProductObj
                                ->whereIn('id', $saleCancelKittingProductIds)
                                ->where('mapping_id', $kittingCloneId->mapping_id)
                                ->where('mapping_model', 'sales_kitting_clone')
                                ->first();

                            $cancelProduct = $this->saleCancellationProductObj
                                ->where('sale_product_id', $kittingProduct->id)
                                ->first();

                            $kittingCloneDetail = $this->saleKittingCloneObj->find($kittingCloneId->mapping_id);

                            $kittingCloneDetail->update([
                                'available_quantity' =>
                                    intval($kittingCloneDetail->available_quantity) - intval($cancelProduct->kitting_quantity)
                            ]);
                    });

                    //Update Sale Status
                    $sale = $this->saleObj->find($saleCancellationRecord->sale_id);

                    $saleProducts = $sale->saleProducts()->where("available_quantity", ">", 0)->first();

                    $orderStatusId = ($saleProducts) ? $saleOrderStatus[$saleOrderStatusConfigCodes['partially-cancelled']] :
                        $saleOrderStatus[$saleOrderStatusConfigCodes['cancelled']];

                    $sale->update(
                        array(
                            'order_status_id' => $orderStatusId,
                            'updated_by' => Auth::id()
                        )
                    );

                    //Update esac status to active
                    $saleCancellationEsacVouchers = $saleCancellationRecord->saleCancelEsacVoucher()->get();

                    collect($saleCancellationEsacVouchers)->each(function ($esacVoucher) {
                        $this->esacVoucherRepositoryObj
                           ->updateStatus($esacVoucher['saleEsacVouchersClone']['voucher_id'], 'N');
                    });

                    //Swap Amp Allocation Record
                    if(!$sale->is_product_exchange && !$sale->is_esac_redemption){
                        $this->saleRepositoryObj->swapAmpCvAllocations($saleCancellationRecord->id);

                        $this->saleRepositoryObj->removeSaleCancellationCv($saleCancellationRecord->id);

                        $this->saleRepositoryObj->saleAccumulationCalculation($saleCancellationRecord->user_id);
                    }

                    //Update Invoice Stockist Release Status
                    $stockistReleaseStatusValues = array_change_key_case(
                        $settingsData['stockist_daily_transaction_release_status']->pluck('id','title')->toArray()
                    );

                    $pendingStockistReleaseStatusId = $stockistReleaseStatusValues[
                        $stockistTransactionReleaseStatusConfigCodes['pending']];

                    $saleInvoice = $sale->invoices()->first();

                    if($saleInvoice->stockist_daily_transaction_status_id == $pendingStockistReleaseStatusId){

                        //Update invoice stock release status to cancel
                        $cancelledStockistReleaseStatusId = $stockistReleaseStatusValues[
                            $stockistTransactionReleaseStatusConfigCodes['cancelled']];

                        $saleInvoice->update(
                            array(
                                'stockist_daily_transaction_status_id' => $cancelledStockistReleaseStatusId,
                                'updated_by' => Auth::id()
                            )
                        );
                    }
                }
            }

            $cancellationData = [
                'cancellation_status_id' => $statusValues[$statusUpdate],
                'updated_by' => Auth::id()
            ];

            if(strtolower($action) == 'sendapproval'){
                if(isset($event->stepInput['buy_back_amount'])){
                    $cancellationData['total_buy_back_amount'] = $event->stepInput['buy_back_amount'];
                }
            }

            $saleCancellationRecord->update($cancellationData);
        }

        return $this->saleRepositoryObj->saleCancellationDetail($saleCancellationId);
    }
}
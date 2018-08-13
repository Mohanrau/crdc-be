<?php
namespace App\Policies\Invoices;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\Policy,
    Models\Invoices\Invoice
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Request,
    Support\Facades\Gate
};

class InvoicePolicy
{
    use HandlesAuthorization, Policy, AccessControl;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * InvoicePolicy constructor.
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->modelObj = $invoice;

        $this->requestObj = Request::all();

        $this->moduleName = 'invoices';

        $this->modelId = 'invoice_id';
    }

    /**
     * check the authorization for downloading invoice
     *
     * @return bool
     */
    public function downloadInvoice()
    {
        return $this->checkUserTypeSelfResource('member', 'download');
    }

    //TODO CLEAN THIS PART ASAP - AFTER REFACTOR STOCKIST MODULE
    /**
     * check stockist daily transaction listing
     *
     * @return bool
     */
    public function stockistDailyTransactionListing()
    {
        //TODO CHECK THE COUNTRY FOR OTHER USER THAN STOCKIST
        if (! Gate::allows('stockist.daily.transactions.list')){
            return false;
        }

        return true;
    }

    /**
     * check stockist daily transaction update
     *
     * @return bool
     */
    public function stockistDailyTransactionUpdate()
    {
        if (! Gate::allows('stockist.daily.transactions.update')){
            return false;
        }

        return true;
    }

    /**
     * get country id
     *
     * @param string $section
     * @return mixed
     */
    private function getCountryId(string $section = null)
    {
        $model = $this->modelObj->find($this->requestObj[$this->modelId]);

        return $model->sale->country_id;
    }
}

<?php
namespace App\Policies\Sales;

use App\Helpers\Traits\AccessControl;
use App\Helpers\Traits\Policy;
use App\Models\{
    Sales\Sale, Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class SalePolicy
{
    use HandlesAuthorization, Policy, AccessControl;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * SalePolicy constructor.
     *
     * @param Sale $model
     */
    public function __construct(Sale $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'sales';

        $this->modelId = 'sale_id';
    }

    /**
     * check if user has privileges to access invoices
     *
     * @param $user
     * @param $sale
     * @return bool
     */
    public function salesViewByInvoice($user, $sale)
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $sale->country_id)){
            return false;
        }

        //check if user backOffice or stockist and can view this sale
        $this->resourceLocationAccessCheck($sale->transaction_location_id, $sale->user_id);

        return true;
    }

    /**
     * check sales daily report access rnp
     *
     * @return bool
     */
    public function saleDailyReport()
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.report.daily.download', $this->getCountryId())){
            return false;
        }

        return true;
    }

    /**
     * check sales mpos report access rnp
     *
     * @return bool
     */
    public function saleMposReport()
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'mpos.report.download', $this->getCountryId())){
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
        if ($section == 'create' || $section == 'update')
            return $this->requestObj['sales_data']['country_id'];
        else
            return $this->requestObj['country_id'];
    }
}

<?php
namespace App\Policies\Sales;

use App\{
    Helpers\Traits\Policy,
    Models\Sales\SaleExchange
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Request
};

class SaleExchangePolicy
{
    use HandlesAuthorization, Policy;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * SaleExchangePolicy constructor.
     *
     * @param SaleExchange $model
     */
    public function __construct(SaleExchange $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'sales.exchange';

        $this->modelId = 'sale_exchange_id';
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
            return $this->requestObj['sales_exchange_data']['country_id'];
        else
            return $this->requestObj['country_id'];
    }
}

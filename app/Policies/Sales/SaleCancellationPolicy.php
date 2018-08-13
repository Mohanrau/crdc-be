<?php
namespace App\Policies\Sales;

use App\{
    Helpers\Traits\Policy,
    Models\Sales\SaleCancellation
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Request
};

class SaleCancellationPolicy
{
    use HandlesAuthorization, Policy;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * SaleCancellationPolicy constructor.
     *
     * @param SaleCancellation $model
     */
    public function __construct(SaleCancellation $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'sales.cancellation';

        $this->modelId = 'sales_cancellation_id';
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
            return $this->requestObj['sale']['country_id'];
        else
            return $this->requestObj['country_id'];
    }
}
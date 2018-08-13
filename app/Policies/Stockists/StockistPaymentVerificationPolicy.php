<?php
namespace App\Policies\Stockists;

use App\Helpers\Traits\Policy;
use App\Models\Stockists\StockistSalePayment;
use App\Models\Users\User;
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class StockistPaymentVerificationPolicy
{
    use HandlesAuthorization, Policy;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * StockistDailyTransactionPolicy constructor.
     *
     * @param StockistSalePayment $model
     */
    public function __construct(StockistSalePayment $model)
    {
        $this->modelObj = $model;

        $this->requestObj = Request::all();

        $this->moduleName = 'stockist.payment.verifications';

        $this->modelId = 'stockist_id';
    }

    /**
     * get country id
     *
     * @param string $section
     * @return mixed
     */
    private function getCountryId(string $section = null)
    {
        return (
            isset($this->requestObj['country_id']) ?
                $this->requestObj['country_id'] :
                null
            );
    }
}

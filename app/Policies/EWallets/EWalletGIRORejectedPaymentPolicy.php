<?php
namespace App\Policies\EWallets;

use App\Helpers\Traits\Policy;
use App\Models\EWallets\EWalletGIRORejectedPayment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class EWalletGIRORejectedPaymentPolicy
{
    use HandlesAuthorization, Policy;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * EWalletGIRORejectedPaymentPolicy constructor.
     *
     * @param EWalletGIRORejectedPayment $eWalletGIRORejectedPayment
     */
    public function __construct(EWalletGIRORejectedPayment $eWalletGIRORejectedPayment)
    {
        $this->modelObj = $eWalletGIRORejectedPayment;

        $this->requestObj = Request::all();

        $this->moduleName = 'giro.rejected.payments';

        $this->modelId = 'giro_rejected_id';
    }

    /**
     * check the authorization for download
     *
     * @return bool
     */
    public function download()
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.download', $this->getCountryId())){
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
        return $this->requestObj['country_id'];
    }
}

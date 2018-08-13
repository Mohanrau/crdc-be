<?php
namespace App\Policies\EWallets;

use App\Helpers\Traits\AccessControl;
use App\Helpers\Traits\Policy;
use App\Models\EWallets\EWalletGIROBankPayment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class EWalletGIROBankPaymentPolicy
{
    use HandlesAuthorization, Policy, AccessControl;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * EWalletGIROBankPaymentPolicy constructor.
     *
     * @param EWalletGIROBankPayment $eWalletGIROBankPayment
     */
    public function __construct(EWalletGIROBankPayment $eWalletGIROBankPayment)
    {
        $this->modelObj = $eWalletGIROBankPayment;

        $this->requestObj = Request::all();

        $this->moduleName = 'giro.bank.payments';

        $this->modelId = 'giro_bank_id';
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
        return $this->requestObj['registered_country_id'];
    }
}

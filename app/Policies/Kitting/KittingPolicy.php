<?php
namespace App\Policies\Kitting;

use App\{
    Helpers\Traits\Policy,
    Models\Kitting\Kitting
};
use Illuminate\{
    Auth\Access\HandlesAuthorization, Support\Facades\Gate, Support\Facades\Request
};

class KittingPolicy
{
    use HandlesAuthorization, Policy{
        view as oldView;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * KittingPolicy constructor.
     *
     * @param Kitting $kitting
     */
    public function __construct(Kitting $kitting)
    {
        $this->modelObj = $kitting;

        $this->requestObj = Request::all();

        $this->moduleName = 'kitting';

        $this->modelId = 'kitting_id';
    }

    /**
     * Determine whether the user can view the sale.
     *
     * @return mixed
     */
    public function view()
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $this->getCountryId())){
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

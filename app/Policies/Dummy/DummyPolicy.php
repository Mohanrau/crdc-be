<?php
namespace App\Policies\Dummy;

use App\{
    Helpers\Traits\Policy,
    Models\Dummy\Dummy,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Request
};

class DummyPolicy
{
    use HandlesAuthorization, Policy;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * DummyPolicy constructor.
     *
     * @param Dummy $dummy
     */
    public function __construct(Dummy $dummy)
    {
        $this->modelObj = $dummy;

        $this->requestObj = Request::all();

        $this->moduleName = 'products.grouping';

        $this->modelId = 'dummy_id';
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

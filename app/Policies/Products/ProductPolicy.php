<?php
namespace App\Policies\Products;

use App\{
    Helpers\Traits\Policy,
    Models\Products\Product
};
use Illuminate\{
    Auth\Access\HandlesAuthorization, 
    Support\Facades\Gate, 
    Support\Facades\Request
};

class ProductPolicy
{
    use HandlesAuthorization, Policy {
        view as oldView;
    }

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * ProductPolicy constructor.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->modelObj = $product;

        $this->requestObj = Request::all();

        $this->moduleName = 'products';

        $this->modelId = 'product_id';
    }

    /**
     * Determine whether the user can view the sale.
     *
     * @return mixed
     */
    public function view()
    {
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

<?php
namespace App\Http\Controllers\V1\Products;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\ResourceController,
    Http\Requests\Products\ProductDetailsRequest,
    Http\Requests\Products\ProductImportRequest,
    Http\Requests\Products\ProductRequest,
    Interfaces\Products\ProductInterface,
    Http\Controllers\Controller,
    Models\Products\Product,
    Rules\Product\ProductAvailableInCountry
};
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * ProductController constructor.
     *
     * @param ProductInterface $productRepository
     * @param Product $model
     */
    public function __construct(ProductInterface $productRepository, Product $model)
    {
        $this->middleware('auth')->except('importYYProducts');

        $this->obj = $productRepository;

        $this->authorizedModel = $model;
    }

    /**
     * import YY Products
     *
     * @param ProductImportRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function importYYProducts(ProductImportRequest $request)
    {
        return response($this->obj->importYYProducts($request->all()));
    }

    /**
     * get list of products filtered by countryId and categoryId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterProducts(Request $request)
    {
        $this->validate($request, [
           'country_id' => 'required|integer|exists:countries,id',
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getProductsByFilters(
                $request->input('country_id'),
                ($request->has('category_id') ? $request->input('category_id') : 0),
                ($request->has('active') ? $request->input('active') : 1),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * search products by sku(code) or name
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function searchProducts(Request $request)
    {
        $this->validate($request, [
            'country_id' => 'required|integer|exists:countries,id',
            'text' => 'required|min:1|max:255'
        ]);

        //check the authorization
        $this->authorize('listing', [$this->authorizedModel]);

        return response(
            $this->obj->searchProducts(
                $request->input('country_id'),
                ($request->has('category_id') ? $request->input('category_id') : 0),
                $request->input('text'),
                ($request->has('location_id') ? $request->input('location_id') : 0),
                ($request->has('active') ? $request->input('active') : 1),
                ($request->has('date_check') ? $request->input('date_check') : false),
                ($request->has('sales_types') ? $request->input('sales_types') : []),
                null,
                null,
                null,
                ($request->has('exact_search') ? $request->input('exact_search') : false),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     *  get product Details for given productId and countryId
     *
     * @param ProductDetailsRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function productDetails(ProductDetailsRequest $request)
    {
        //check if user authorized for view or search permission
        $this->applySearchOrViewPermission($this->authorizedModel);

        return response($this->obj->productDetails(
            $request->input('country_id'),
            $request->input('product_id'),
            $request->input('location_id'))
        );
    }

    /**
     * get the effective product prices
     *
     * @param ProductDetailsRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function productPrice(ProductDetailsRequest $request)
    {
        request()->validate([
            'location_ids.*' => 'sometimes|integer|exists:locations,id',
            'start_date' => 'sometimes|required|nullable|date'
        ]);

        $this->authorize('view', [$this->authorizedModel]);

        return response($this->obj->productEffectivePricing(
            $request->input('country_id'),
            $request->input('product_id'),
            $request->input('location_ids'),
            $request->input('start_date')
        ));
    }

    /**
     *  update product info
     *
     * @param ProductRequest $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ProductRequest $request, int $id)
    {
        $this->authorize('update', [$this->authorizedModel]);

        return response($this->obj->update($request->all(), $id));
    }

    /**
     * delete product image
     *
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteProductImage(int $id)
    {
        return response($this->obj->deleteProductImage($id));
    }
}

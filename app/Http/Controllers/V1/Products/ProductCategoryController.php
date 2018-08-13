<?php
namespace App\Http\Controllers\V1\Products;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Products\ProductCategoryRequest,
    Interfaces\Products\ProductCategoryInterface,
    Http\Controllers\Controller,
    Models\Products\ProductCategory
};
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * ProductCategoryController constructor.
     *
     * @param ProductCategoryInterface $productCategoryRepository
     * @param ProductCategory $model
     */
    public function __construct(ProductCategoryInterface $productCategoryRepository, ProductCategory $model)
    {
        $this->middleware('auth');

        $this->obj = $productCategoryRepository;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Products\ProductCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductCategoryRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Products\ProductCategoryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductCategoryRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }

    /**
     * get list of product categories filtered by countryId and categoryId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterProductCategories(Request $request)
    {        
        request()->validate([
            'parent_id' => 'sometimes|nullable|integer',
            'for_sales' => 'sometimes|nullable|boolean',
            'for_marketing' => 'sometimes|nullable|boolean',
            'esac_vouchers.*' => 'sometimes|nullable|integer|exists:esac_vouchers,id',
            'active' => 'sometimes|nullable|boolean'
        ]);

        return response(
            $this->obj->getProductCategoriesByFilters(
                ($request->has('parent_id') ? $request->input('parent_id') : null),
                ($request->has('for_sales') ? $request->input('for_sales') : null),
                ($request->has('for_marketing') ? $request->input('for_marketing') : null),
                ($request->has('esac_vouchers') ? $request->input('esac_vouchers') : null),
                ($request->has('active') ? $request->input('active') : null),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }
}

<?php
namespace App\Http\Controllers\V1\Shop;

use App\{
    Http\Controllers\Controller,
    Interfaces\Shop\ShopProductAndKittingFilteringInterface
};
use Illuminate\Http\Request;

class ShopProductsController extends Controller
{
    private $productsAndKitting;

    /**
     * ShopProductController constructor.
     *
     * @param ShopProductAndKittingFilteringInterface $productAndKittingInterface
     */
    public function __construct(ShopProductAndKittingFilteringInterface $productAndKittingInterface)
    {
        $this->middleware('auth')->except('importYYProducts');
        $this->productsAndKitting = $productAndKittingInterface;
    }

    /**
     * Filter and sort Products and Kitting mixed
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getProductAndKitting(Request $request) {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'location_id' => 'required|integer|exists:locations,id',
            'categories' => 'sometimes|array',
            'sales_types' => 'sometimes|array',
            'sortby' => 'sometimes|integer',
            'price_min' => 'sometimes|numeric',
            'price_max' => 'sometimes|numeric',
            'cv_min' => 'sometimes|numeric',
            'cv_max' => 'sometimes|numeric',
            'limit' => 'sometimes|integer',
            'offset' => 'sometimes|integer',
            'active' => 'sometimes|integer',
            'name' => 'sometimes|string',
            'with' => 'sometimes|array'
        ]);

        return response($this
            ->productsAndKitting
            ->filterProductAndKitting(
                $request->input('country_id'),
                $request->input('location_id'),
                $request->input('categories') ?? [],
                $request->input('sales_types') ?? [],
                $request->input('sortby') ?? 1,
                $request->input('price_min') ?? 0,
                $request->input('price_max') ?? 0,
                $request->input('cv_min') ?? 0,
                $request->input('cv_max') ?? 0,
                $request->input('limit') ?? 18,
                $request->input('offset') ?? 0,
                $request->input('active') ?? 1,
                $request->input('name') ?? null,
                $request->input('with') ?? ['descriptions', 'images', 'prices', 'dummy', 'favorites']
            )->calculated()->refine()
        );
    }
}
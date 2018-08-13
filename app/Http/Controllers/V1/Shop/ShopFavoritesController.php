<?php
namespace App\Http\Controllers\V1\Shop;

use App\{
    Http\Controllers\Controller,
    Interfaces\Shop\ShopFavoriteInterface
};
use Auth;
use Illuminate\Http\Request;

class ShopFavoritesController extends Controller
{

    private $obj;

    /**
     * SaleController constructor.
     *
     * @param ShopFavoriteInterface $shopFavoriteInterface
     */
    public function __construct(ShopFavoriteInterface $shopFavoriteInterface)
    {
        $this->middleware('auth');
        $this->obj = $shopFavoriteInterface;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required_without:kitting_id|integer|exists:products,id',
            'kitting_id' => 'required_without:product_id|integer|exists:kitting,id'
        ]);
        return response($this->obj->create(
            array_merge($request->all(),
                ["user_id" => Auth::id()] // Use member id from token
            )
        ));
    }

    /**
     * List the favorites of the user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index() {
        return $this->obj->getUserFavorites(Auth::user());
    }
}
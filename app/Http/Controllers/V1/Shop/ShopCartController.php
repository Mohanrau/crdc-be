<?php
namespace App\Http\Controllers\V1\Shop;

use App\{
    Http\Controllers\Controller,
    Interfaces\Shop\ShopCartInterface,
    Interfaces\Shop\ShopSaleInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Members\MemberTreeInterface,
    Interfaces\Users\UserInterface,
    Models\Sales\Sale,
    Http\Requests\Shop\ProductAndKittingRequest,
    Http\Requests\Shop\CreateSaleRequest,
    Rules\General\MasterDataIdExists,
    Helpers\Traits\AccessControl
};
use Auth;
use Illuminate\Http\Request;
use App\Services\EShop\ShoppingCartService;

class ShopCartController extends Controller
{
    private
        $obj,
        $masterRepositoryObj,
        $shopSaleRepository,
        $memberTreeRepository,
        $shoppingCartService
    ;

    use AccessControl;

    /**
     * SaleController constructor.
     *
     * @param MasterInterface $masterRepository
     * @param ShopCartInterface $shopCartRepository
     * @param ShopSaleInterface $shopSaleRepository
     * @param ShoppingCartService $shoppingCartService
     * @param MemberTreeInterface $memberTreeRepository
     */
    public function __construct(
        MasterInterface $masterRepository,
        ShopCartInterface $shopCartRepository,
        ShopSaleInterface $shopSaleRepository,
        ShoppingCartService $shoppingCartService,
        MemberTreeInterface $memberTreeRepository
    )
    {
        $this->middleware('auth');

        $this->obj = $shopCartRepository;

        $this->shopSaleRepository = $shopSaleRepository;

        $this->masterRepositoryObj = $masterRepository;

        $this->shoppingCartService = $shoppingCartService;

        $this->memberTreeRepository = $memberTreeRepository;
    }

    /**
     * Add item to card
     *
     * @param ProductAndKittingRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(ProductAndKittingRequest $request)
    {
        $this->validate($request, [
            'sales_type_id' => [
                'required', new MasterDataIdExists($this->masterRepositoryObj, 'sale_types'),
            ],
            'quantity' => 'sometimes|integer',
            'order_for_user_id' => 'sometimes|nullable|integer|exists:members,user_id'
        ]);

        if (!is_null($request->input('order_for_user_id'))) {
            $this->resourceBelongToMyDownLine($request->input('order_for_user_id'));
        }

        return response($this->obj->create(
            array_merge(
                $request->all(),
                [
                    "user_identifier" => Auth::user()->identifier(),
                    "order_for_user_id" => $request->input('order_for_user_id') ?? Auth::id()
                ] // Use member id from token
            )
        ));
    }

    /**
     * Get details of the cart
     *
     * @param Request $request
     * @param UserInterface $userRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \App\Exceptions\Masters\InvalidSaleTypeIdException
     */
    public function details(Request $request, UserInterface $userRepository) {
        $this->validate($request, [
            'country_id' => 'required|integer|exists:countries,id',
            'location_id' => 'required|integer|exists:locations,id',
            'order_for_user_id' => 'sometimes|nullable|integer|exists:members,user_id'
        ]);

        if (!is_null($request->input('order_for_user_id'))) {
            $this->resourceBelongToMyDownLine($request->input('order_for_user_id'));
        }

        return response(
            array_merge(
                $this->obj->userCartItems(
                    Auth::user()->identifier(),
                    $request->input('country_id'),
                    $request->input('location_id'),
                    $request->input('order_for_user_id') ?? Auth::id()
                ),
                [
                    'checkout' => $this->shoppingCartService
                        ->processCheckout(
                            $this->obj,
                            !is_null($request->input('order_for_user_id'))
                                ? $userRepository->find($request->input('order_for_user_id'))
                                : Auth::user()
                        )
                        ->toArray()
                ]
            )
        );
    }

    /**
     * Clear cart of the user
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function clearCart() {
        return response([
            "cleared" => $this->obj->userCartClear(Auth::user()->identifier())
        ]);
    }

    /**
     * Create Cart Sale
     *
     * Create sales from cart
     *
     * @param CreateSaleRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function createCartSale(CreateSaleRequest $request) {
        if (!is_null($request->input('order_for_user_id'))) {
            $this->resourceBelongToMyDownLine($request->input('order_for_user_id'));
        }

        return response($this->shopSaleRepository->createCartSale(
            Auth::user()->identifier(),
            $request->input('location_id'),
            $request->input('country_id'),
            array_merge(
                $request->all(),
                [
                    "sponsor_member_id" => Auth::user()->isGuest()
                        ? ( !is_null($request->input('sponsor_member_id'))
                                ? Auth::user()
                                      ->where('old_member_id', $request->input('sponsor_member_id'))
                                      ->firstOrFail()->id
                                : Auth::user()
                                      ->identifier()->referrer )
                        : $request->input('sponsor_member_id'),
                    "downline_member_id" => !is_null($request->input('order_for_user_id'))
                        ? $request->input('order_for_user_id')
                        : Auth::id(),
                    "selected" => $request->input('selected'),
                ]
            )
        ));
    }

    /**
     * Create Sale
     *
     * Creates sales from provided data
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createSale(Request $request) {
        $this->authorize('create', [Sale::class]);

        return response($this->shopSaleRepository->createSale($request->all()));
    }
}

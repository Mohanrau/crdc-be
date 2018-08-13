<?php
namespace App\Http\Controllers\V1\Promotions;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Promotions\PromotionRequest,
    Interfaces\Promotions\PromotionFreeItemsInterface,
    Http\Controllers\Controller,
    Models\Promotions\PromotionFreeItem
};
use Illuminate\Http\Request;

class PromotionFreeItemController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * PromotionFreeItemController constructor.
     *
     * @param PromotionFreeItemsInterface $promotionFreeItems
     * @param PromotionFreeItem $model
     */
    public function __construct(
        PromotionFreeItemsInterface $promotionFreeItems,
        PromotionFreeItem $model
    )
    {
        $this->middleware('auth');

        $this->obj = $promotionFreeItems;

        $this->authorizedModel = $model;
    }

    /**
     * get promotionFreeItems by filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterPromotionFreeItem(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getPromotionFreeItemsByFilters(
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get promotion free item details for a given countryId and optional promo_id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function PromotionFreeItemDetails(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id'
        ]);

        $this->authorize('view', [$this->authorizedModel]);

        return response($this->obj->promotionFreeItemsDetails(
            $request->input('country_id'),
            ($request->has('promo_id') ?
                ((($request->input('promo_id') == null) ? 0 : $request->input('promo_id'))):
                null)
            )
        );
    }

    /**
     * create or update promotionFreeItem
     *
     * @param PromotionRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdatePromotionFreeItem(PromotionRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('promo_id') == null){
            $this->authorize('create', [$this->authorizedModel]);
        }else{
            $this->authorize('update', [$this->authorizedModel]);
        }

        return response($this->obj->createOrUpdate($request->all()));
    }
}

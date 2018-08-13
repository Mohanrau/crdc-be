<?php
namespace App\Http\Controllers\V1\Kitting;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Kitting\KittingRequest,
    Http\Controllers\Controller,
    Interfaces\Kitting\KittingInterface,
    Models\Kitting\Kitting
};
use Illuminate\Http\Request;

class KittingController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * KittingController constructor.
     *
     * @param KittingInterface $kittingRepository
     * @param Kitting $model
     */
    public function __construct(KittingInterface $kittingRepository, Kitting $model)
    {
        $this->middleware('auth');

        $this->obj = $kittingRepository;

        $this->authorizedModel = $model;
    }

    /**
     * get kitting listing by filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterKitting(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'is_esac' => 'nullable|boolean'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getKittingByFilters(
                $request->input('country_id'),
                ($request->has('is_esac') ? $request->input('is_esac') : null),
                ($request->has('kitting_code') ? $request->input('kitting_code') : ''),
                ($request->has('product_code') ? $request->input('product_code') : ''),
                ($request->has('sales_type') ? $request->input('sales_type') : []),
                0,
                ($request->has('include_categories') ? $request->input('include_categories') : null),
                ($request->has('include_kittings') ? $request->input('include_kittings') : null),
                ($request->has('exclude_kittings') ? $request->input('exclude_kittings') : null),
                ($request->has('text') ? $request->input('text') : ''),
                ($request->has('active') ? $request->input('active') : 1),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get kitting Details for a given country and kittingId (optional)
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function kittingDetails(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id'
        ]);

        //check if user authorized for view or search permission
        $this->applySearchOrViewPermission($this->authorizedModel);

        //TODO optimize this part to check if this kitting belong to the given country

        return response($this->obj->kittingDetails(
            $request->input('country_id'),
            ($request->has('kitting_id') ?
                ($request->input('kitting_id') > 0)? $request->input('kitting_id') : 0  :  0)
        ));
    }

    /**
     * create or update kitting
     *
     * @param KittingRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdateKitting(KittingRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('kitting_id') == null){
            $this->authorize('create', [$this->authorizedModel]);
        }else{
            $this->authorize('update', [$this->authorizedModel]);
        }

        return response($this->obj->createOrUpdate($request->all()));
    }
}

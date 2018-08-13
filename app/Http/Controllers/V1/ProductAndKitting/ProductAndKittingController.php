<?php
namespace App\Http\Controllers\V1\ProductAndKitting;

use App\Interfaces\ProductAndKitting\ProductAndKittingInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductAndKittingController extends Controller
{
    private
        $productAndKittingObj;

    /**
     * ProductAndKittingController constructor.
     *
     * @param ProductAndKittingInterface $productAndKitting
     */
    public function __construct(ProductAndKittingInterface $productAndKitting)
    {
        $this->middleware('auth');

        $this->productAndKittingObj = $productAndKitting;
    }

    /**
     * search for the available products or kitting for sales
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function searchProductOrKitting(Request $request)
    {
        //TODO implement rnp - implement sales create rnp

        request()->validate([
            'user_id' => 'required|integer|exists:members,user_id',
            'country_id' => 'required|integer|exists:countries,id',
            'transaction_location_id' => 'required|integer|exists:locations,id',
            'text' => 'sometimes|min:1|nullable',
            'esac_vouchers.*' => 'sometimes|nullable|integer|exists:esac_vouchers,id',
            'sale_types' => 'nullable|array',
        ]);

        return response($this->productAndKittingObj->searchProductsAndKitting(
            $request->input('user_id'),
            $request->input('country_id'),
            $request->input('transaction_location_id'),
            (($request->input('text') !=  null) ? $request->input('text') : ''),
            ($request->has('esac_vouchers') ? $request->input('esac_vouchers') : null),
            ($request->has('sale_types') ? $request->input('sale_types') : []),
            ($request->has('is_consignment_return') ? $request->input('is_consignment_return') : false),
            ($request->has('limit') ? $request->input('limit') : 20),
            ($request->has('sort') ? $request->input('sort') :  'id'),
            ($request->has('order') ? $request->input('order') : 'desc'),
            ($request->has('offset') ? $request->input('offset') :  0),
            ($request->has('mixed') ? $request->input('mixed') : false)
        ));
    }

    /**
     * search for the available products and kitting for enrollment
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function searchProductOrKittingEnrollment(Request $request)
    {
        //TODO implement rnp - create enrollment 

        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'transaction_location_id' => 'required|integer|exists:locations,id',
            'enrollment_type_id' => 'required|integer|exists:enrollments_types,id',
            'text' => 'sometimes|min:1|nullable',
        ]);

        return response($this->productAndKittingObj->searchProductsAndKittingEnrollment(
            $request->input('country_id'),
            $request->input('transaction_location_id'),
            $request->input('enrollment_type_id'),
            (($request->input('text') !=  null) ? $request->input('text') : ''),
            ($request->has('limit') ? $request->input('limit') : 20),
            ($request->has('sort') ? $request->input('sort') :  'id'),
            ($request->has('order') ? $request->input('order') : 'desc'),
            ($request->has('offset') ? $request->input('offset') :  0)
        ));
    }
}

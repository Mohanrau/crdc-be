<?php
namespace App\Http\Controllers\V1\Virel;

use App\{
    Interfaces\Virel\VirelInterface,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class VirelController extends Controller
{
    private $obj;

    /**
     * VirelController constructor.
     *
     * @param VirelInterface $virelRepository
     */
    public function __construct(VirelInterface $virelRepository)
    {
        $this->obj = $virelRepository;
    }

    /**
     * get user by email
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        request()->validate([
            'email' => 'required_without:old_member_id|email',
            'old_member_id' => 'required_without:email|exists:users,old_member_id'
        ]);

        return response(
            $this->obj->getUser(
                ($request->has('email') ? $request->input('email') : null),
                ($request->has('old_member_id') ? $request->input('old_member_id') : null)
            )
        );
    }

    /**
     * get member by old_member_id
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getMember(Request $request)
    {
        request()->validate([
            'old_member_id' => 'required|exists:users,old_member_id'
        ]);

        return response(
            $this->obj->getMember(
                $request->input('old_member_id')
            )
        );
    }

    /**
     * get product category list
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductCategories()
    {
        return response(
            $this->obj->getProductCategories()
        );
    }

    /**
     * get standard product list
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {
        return response(
            $this->obj->getProducts()
        );
    }

    /**
     * get promo product list
     *
     * @return \Illuminate\Http\Response
     */
    public function getPromoProducts()
    {
        return response(
            $this->obj->getPromoProducts()
        );
    }
}
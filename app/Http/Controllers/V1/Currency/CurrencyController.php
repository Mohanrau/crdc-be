<?php
namespace App\Http\Controllers\V1\Currency;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Currency\CurrencyRequest,
    Http\Requests\Currency\CurrencyConversionRequest,
    Interfaces\Currency\CurrencyInterface,
    Http\Controllers\Controller,
    Models\Currency\Currency
};
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * CurrencyController constructor.
     *
     * @param CurrencyInterface $currencyRepository
     * @param Currency $model
     */
    public function __construct(CurrencyInterface $currencyRepository, Currency $model)
    {
        $this->middleware('auth');

        $this->obj = $currencyRepository;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Currency\CurrencyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CurrencyRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Currency\CurrencyRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CurrencyRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }

    /**
     * Store a newly created currencies conversion resource in storage.
     *
     * @param  CurrencyConversionRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function currenciesConversionsStore(CurrencyConversionRequest $request)
    {
        return response(
            $this->obj->currenciesConversionsStore(
                $request->only([
                    'from_currency_id', 'to_currency_id', 'rate', 'cw_id'
                ])
            )
        );
    }

    /**
     * Display a currencies conversion listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCurrenciesConversionsList(Request $request)
    {
        request()->validate([
            'from_currency_id' => 'exists:currencies,id|different:to_currency_id',
            'to_currency_id' => 'exists:currencies,id|different:from_currency_id',
            'cw_id' => 'integer|exists:cw_schedules,id'
        ]);

        return response(
            $this->obj->getCurrenciesConversionsByFilters(
                ($request->has('from_currency_id') ? $request->input('from_currency_id') : NULL),
                ($request->has('to_currency_id') ? $request->input('to_currency_id') : NULL),
                ($request->has('cw_id') ? $request->input('cw_id') : NULL),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Get currencies conversion rate.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCurrenciesConversionsRate(Request $request)
    {
        request()->validate([
            'from_currency_id' => 'exists:currencies,id|different:to_currency_id',
            'to_currency_id' => 'exists:currencies,id|different:from_currency_id'
        ]);

        return response(
            $this->obj->getCurrenciesConversionsRate(
                $request->input('from_currency_id'),
                $request->input('to_currency_id')
            )
        );
    }
}

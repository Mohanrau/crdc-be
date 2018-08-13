<?php
namespace App\Http\Controllers\V1\Settings;

use App\{
    Models\Settings\CountryDynamicContent,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class CountryDynamicContentController extends Controller
{
    private $countryDynamicContentObj;

    /**
     * CountryDynamicContentController constructor.
     *
     * @param CountryDynamicContent $countryDynamicContent
     */
    public function __construct(CountryDynamicContent $countryDynamicContent)
    {
        $this->middleware('auth');

        $this->countryDynamicContentObj = $countryDynamicContent;
    }

    /**
     * get Country Dynamic Content by type
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCountryDynamicContentByType(Request $request)
    {
        request()->validate([
            'country_id' => 'sometimes|integer|exists:countries,id',
            'types' => 'required|exists:countries_dynamic_content,type'
        ]);

        $data =
            $this->countryDynamicContentObj
                ->whereIn('type', $request->input('types'));

        if($request->has('country_id'))
        {
            $data->where('country_id', $request->input('country_id'));
        }

        if($data->count() > 0) {

            $data = $data->get();

            $data = collect($data)->mapWithKeys(function ($item) {
                $data[$item->type] =  $item->content;

                return $data;
            });

        }else{
            $data = ['error' => trans('message.empty')];
        }

        return response(
            $data
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'type' => 'required',
            'content' => 'required'
        ]);

        return response($this->countryDynamicContentObj->create($request->all()));
    }
}

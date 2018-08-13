<?php
namespace App\Http\Controllers\V1\Languages;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Languages\LanguageRequest,
    Interfaces\Languages\LanguageInterface,
    Http\Controllers\Controller,
    Models\Languages\Language
};
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    use ResourceController{
        index as oldIndex;
    }

    private
        $obj,
        $authorizedModel
    ;

    /**
     * LanguageController constructor.
     *
     * @param LanguageInterface $languageRepository
     * @param Language $model
     */
    public function __construct(LanguageInterface $languageRepository, Language $model)
    {
        $this->middleware('auth')->except('index');

        $this->obj = $languageRepository;

        $this->authorizedModel = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        if ($request->has('country_id')){
            request()->validate([
                'country_id' => 'required|integer|exists:countries,id'
            ]);
        }

        return response(
            $this->obj->getLanguages(
                ($request->has('country_id') ? $request->input('country_id') : null),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Languages\LanguageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LanguageRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Languages\LanguageRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LanguageRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }
}

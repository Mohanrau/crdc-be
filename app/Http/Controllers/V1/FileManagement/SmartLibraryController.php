<?php
namespace App\Http\Controllers\V1\FileManagement;

use App\{
    Http\Requests\FileManagement\SmartLibraryRequest,
    Interfaces\FileManagement\SmartLibraryInterface,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SmartLibraryController extends Controller
{
    private $obj;

    /**
     * SmartLibraryController constructor.
     *
     * @param SmartLibraryInterface $smartLibraryInterface
     */
    public function __construct(SmartLibraryInterface $smartLibraryInterface)
    {
        $this->middleware('auth');

        $this->obj = $smartLibraryInterface;
    }

    /**
     * Create or Update resource
     *
     * @param  \App\Http\Requests\FileManagement\SmartLibraryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function createOrUpdate(SmartLibraryRequest $request)
    {
        return response($this->obj->createOrUpdate($request->all()));
    }

    /**
     * Get the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response($this->obj->show($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }
    
    /**
     * get smart library product listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSmartLibraryProductList(Request $request)
    {
        request()->validate([
            'countries.*' => 'nullable|integer|exists:countries,id',
            'text' => 'required|min:1|max:255'
        ]);

        return response(
            $this->obj->getSmartLibraryProduct(
                $request->input('countries'),
                $request->input('text')
            )
        );
    }

    /**
     * get smart library listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSmartLibraryList(Request $request)
    {
        $fileTypeOption = [];

        foreach ($this->obj->getSmartLibraryFileTypeList()['data'] as $fileType) {
            array_push($fileTypeOption, $fileType['code']);
        }

        request()->validate([
            'countries.*' => 'nullable|integer|exists:countries,id',
            'languages.*' => 'nullable|integer|exists:languages,id',
            'file_types.*' => 'nullable|string|in:"' . implode('","', $fileTypeOption) . '"',
            'product_categories.*' => 'nullable|integer|exists:product_categories,id'
        ]);

        return response(
            $this->obj->getSmartLibrariesByFilters(
                ($request->has('country_id') ? $request->input('country_id') : 0),
                ($request->has('title') ? $request->input('title') : ''),
                ($request->has('file_type') ? $request->input('file_type') : ''),
                ($request->has('sale_type_id') ? $request->input('sale_type_id') : 0),
                ($request->has('product_category_id') ? $request->input('product_category_id') : 0),
                ($request->has('product_id') ? $request->input('product_id') : 0),
                ($request->has('status') ? $request->input('status') : 2),
                ($request->has('new_joiner_essential_tools') ? $request->input('new_joiner_essential_tools') : 2),
                ($request->has('use_mobile_filter') ? $request->input('use_mobile_filter') : 0),
                ($request->has('countries') ? $request->input('countries') : array()),
                ($request->has('languages') ? $request->input('languages') : array()),
                ($request->has('file_types') ? $request->input('file_types') : array()),
                ($request->has('product_categories') ? $request->input('product_categories') : array()),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get smart library file type listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSmartLibraryFileTypeList(Request $request)
    {
        return response(
            $this->obj->getSmartLibraryFileTypeList()
        );
    }

}
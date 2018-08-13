<?php
namespace App\Http\Requests\FileManagement;

use App\Interfaces\FileManagement\SmartLibraryInterface;
use App\Interfaces\Masters\MasterInterface;
use App\Rules\General\MasterDataIdExists;
use Illuminate\Foundation\Http\FormRequest;

class SmartLibraryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param MasterInterface $masterRepository
     * @param SmartLibraryInterface $smartLibraryInterface
     * @return array
     */
    public function rules(
        MasterInterface $masterRepository,
        SmartLibraryInterface $smartLibraryInterface
    )
    {
        $fileTypeOption = [];

        foreach ($smartLibraryInterface->getSmartLibraryFileTypeList()['data'] as $fileType) {
            array_push($fileTypeOption, $fileType['code']);
        }

        return [
            'title' => 'required|min:3|max:50',
            'description' => 'required|min:3|max:500',
            'language_id' => 'required|integer|exists:languages,id',
            'sale_type_id' => [
                'nullable', 'integer',
                new MasterDataIdExists($masterRepository, 'sale_types')
            ],
            'product_category_id' => 'nullable|integer|exists:product_categories,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'status' => 'required|boolean',
            'sequence_priority' => 'required|integer|min:0',
            'thumbnail_data' => 'required',
            'upload_file_type' => 'required|string|in:"' . implode('","', $fileTypeOption) . '"',
            'upload_file_data' => 'required',
            'new_joiner_essential_tools' => 'required|boolean'
        ];
    }
}
<?php
namespace App\Repositories\FileManagement;

use App\{
    Interfaces\FileManagement\SmartLibraryInterface,
    Models\FileManagement\SmartLibrary,
    Models\Locations\Country,
    Models\Languages\Language,
    Models\Masters\MasterData,
    Models\Products\ProductCategory,
    Models\Products\Product,
    Repositories\BaseRepository
};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Support\Facades\Auth;

class SmartLibraryRepository extends BaseRepository implements SmartLibraryInterface
{   
    private $countryObj,
        $languageObj,
        $masterDataObj,
        $productCategoryObj,
        $productObj;
    
    /**
     * SmartLibraryRepository constructor.
     *
     * @param SmartLibrary $model
     * @param Country $country
     * @param Language $language
     * @param MasterData $masterData
     * @param ProductCategory $productCategory
     * @param Product $product
     */
    public function __construct(
        SmartLibrary $model,
        Country $country,
        Language $language,
        MasterData $masterData,
        ProductCategory $productCategory,
        Product $product
    )
    {
        parent::__construct($model);
        
        $this->countryObj = $country;
        
        $this->languageObj = $language;
        
        $this->masterDataObj = $masterData;
        
        $this->productCategoryObj = $productCategory;
        
        $this->productObj = $product;
    }

    /**
     * get distinct products belong to union of countries
     *
     * @param array $countries
     * @param string $text
     * @return array
     */
    public function getSmartLibraryProduct(array $countries, string $text)
    {
        $data = array();
        
        if (count($countries) > 0) {
            foreach ($countries as $country) {
                $entity = $this->countryObj
                    ->find($country)
                    ->entity()
                    ->first();

                $products = $entity
                    ->products()
                    ->where('name', 'like', '%' . $text . '%')
                    ->orWhere('sku', 'like', '%' . $text . '%')
                    ->get();
                
                foreach ($products as $product) {
                    $item = [
                        "id" => $product->id, 
                        "name" => $product->name, 
                        "sku" => $product->sku
                    ];

                    if (!in_array($item, $data)) {
                        array_push($data, $item);
                    }
                }                
            }
        }
        
        $totalRecords = collect(
            [
                'total' => count($data)
            ]
        );
        
        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get upload file type list
     *
     * @return array
     */
    public function getSmartLibraryFileTypeList()
    {
        $data = array();

        foreach ($this->modelObj->fileTypeOption as $fileTypeCode=>$fileTypeName) {
            array_push($data, ['code' => $fileTypeCode, 'name' => $fileTypeName]);
        }
        
        $totalRecords = collect(
            [
                'total' => count($data)
            ]
        );
        
        return $totalRecords -> merge(['data' => $data]);
    }
    
    /**
     * get smart library filtered by the following parameters
     *
     * @param int $countryId
     * @param string $title
     * @param string $fileType
     * @param int $saleTypeId
     * @param int $productCategoryId
     * @param int $productId
     * @param int $status
     * @param int $newJoinerEssentialTools
     * @param int $useMobileFilter
     * @param array $countries
     * @param array $languages
     * @param array $fileTypes
     * @param array $productCategories  
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getSmartLibrariesByFilters(
        int $countryId = 0,
        string $title = '',
        string $fileType = '',
        int $saleTypeId = 0,
        int $productCategoryId = 0,
        int $productId = 0,
        int $status = 2,
        int $newJoinerEssentialTools = 2,
        int $useMobileFilter = 0,
        array $countries = array(),
        array $languages = array(),
        array $fileTypes = array(),
        array $productCategories = array(),
        int $paginate = 20,
        string $orderBy = 'sequence_priority',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $mobileFilter = null;

        $data = $this->modelObj
            ->with(['language', 'saleType', 'productCategory', 'product']);
        
        if ($countryId > 0) {
            $data = $data
                ->join('smart_library_countries', 'smart_library_countries.smart_library_id', '=', 'smart_libraries.id')
                ->where('smart_library_countries.country_id', $countryId);
        }
        
        if ($title != '') {
            $data = $data
                ->where('smart_libraries.title', 'like', '%' . $title . '%');
        }
        
        if ($fileType != '') {
            $data = $data
                ->where('smart_libraries.upload_file_type', $fileType);
        }
        
        if ($saleTypeId > 0) {
            $data = $data
                ->where('smart_libraries.sale_type_id', $saleTypeId);
        }
        
        if ($productCategoryId > 0) {
            $data = $data
                ->where('smart_libraries.product_category_id', $productCategoryId);
        }
        
        if ($productId > 0) {
            $data = $data
                ->where('smart_libraries.product_id', $productId);
        }
        
        if ($status < 2) {
            $data = $data
                ->where('smart_libraries.status', $status);
        }

        if ($newJoinerEssentialTools < 2) {
            $data = $data
                ->where('smart_libraries.new_joiner_essential_tools', $newJoinerEssentialTools);
        }

        if ($useMobileFilter > 0) {
            if (count($countries) > 0 && count($languages) > 0 && count($fileTypes) > 0) {
                if (count($countries) > 0) {
                    if ($countryId <= 0) {
                        $data = $data
                            ->join('smart_library_countries', 'smart_library_countries.smart_library_id', '=', 'smart_libraries.id');
                    }

                    $data = $data
                        ->whereIn('smart_library_countries.country_id', $countries);
                }
                
                if (count($languages) > 0) {
                    $data = $data
                        ->whereIn('smart_libraries.language_id', $languages);
                }
                
                if (count($fileTypes) > 0) {
                    $data = $data
                        ->whereIn('smart_libraries.upload_file_type', $fileTypes);
                }
                
                if (count($productCategories) > 0) {
                    $data = $data
                        ->whereIn('smart_libraries.product_category_id', $productCategories);
                }
            }
            else {
                $data = $data
                    ->where('id', -1);
            }
            
            $countryFilter = $this->countryObj->active()->get()->map(function($country) use($countries) {
                return [
                    "id" => $country->id,
                    "name" => $country->name,
                    "code" => $country->code_iso_2,
                    "selected" => in_array($country->id, $countries)
                ];
            });
            
            $languageFilter = $this->languageObj->active()->get()->map(function($language) use($languages) {
                return [
                    "id" => $language->id,
                    "name" => $language->name,
                    "locale_code" => $language->locale_code,
                    "selected" => in_array($language->id, $languages)
                ];
            });
            
            $fileTypeArray = $this->modelObj->fileTypeOption;
            
            $fileTypeFilter = array_map(function($file_type_key, $file_type_value) use($fileTypes) {
                return [
                    "id" => $file_type_key,
                    "name" => $file_type_value,
                    "selected" => in_array($file_type_key, $fileTypes)
                ];
            }, array_keys($fileTypeArray), $fileTypeArray);
            
            $productCategoryFilter = $this->productCategoryObj->active()->where('for_marketing', 1)->get()->map(function($productCategory) use($productCategories) {
                return [
                    "id" => $productCategory->id,
                    "name" => $productCategory->name,
                    'mobile_image_link' => $productCategory->mobileImageLink,
                    "selected" => in_array($productCategory->id, $productCategories)
                ];
            });

            $mobileFilter = array(
                'countries' => $countryFilter,
                'languages' => $languageFilter,
                'file_types' => $fileTypeFilter,
                'product_categories' => $productCategoryFilter
            );
        }
        
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );
        
        if ($useMobileFilter > 0) {
            $totalRecords = $totalRecords->merge(['filter' => $mobileFilter]);
        }

        switch (strtolower($orderBy)) {
            case 'language':
                $data = $data
                    ->join('languages', 'languages.id', '=', 'smart_libraries.language_id', 'left outer')
                    ->orderBy('languages.name', $orderMethod);
                break;
            case 'sale_type':
                $data = $data
                    ->join('master_data', 'master_data.id', '=', 'smart_libraries.sale_type_id', 'left outer')
                    ->orderBy('master_data.title', $orderMethod);
                break;
            case 'product_category':
                $data = $data
                    ->join('product_categories', 'smart_libraries.product_category_id', '=', 'product_categories.id', 'left outer')
                    ->orderBy('product_categories.name', $orderMethod);
                break;
            case 'product':
                $data = $data
                    ->join('products', 'products.id', '=', 'smart_libraries.product_id', 'left outer')
                    ->orderBy('products.name', $orderMethod);
                break;
            default:
                $data = $data->orderBy($orderBy, $orderMethod);
                break;
        }

        $data = $data->select('smart_libraries.*');
        
        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();
        
        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * get one smart library by id
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $data = $this->modelObj
            ->with(['language', 'saleType', 'productCategory', 'product', 'smartLibraryCountries'])
            ->findOrFail($id);
        
        $data->countries = $data->smartLibraryCountries()->get()->pluck('id')->toArray();

        $productCategoryNameArray = $data->productCategory()->get()->pluck('name')->toArray();

        $data->product_category_name = (count($productCategoryNameArray) > 0) ? $productCategoryNameArray[0] : null;

        $productSkuArray = $data->product()->get()->pluck('sku')->toArray();

        $data->product_code = (count($productSkuArray) > 0) ? $productSkuArray[0] : null;
        
        return $data;
    }
    
    /**
     * create or update smart library
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $smartLibrary = null;
        $smartLibraryCountry = null; 
        $errorBag = [];

        $setting = Uploader::getUploaderSetting(true);

        //get current data stored in db
        $currentThumbnailData = '';
        $currentUploadFileData = '';
        
        if (isset($data['id'])) {
            $smartLibrary = $this->modelObj->findOrFail($data['id']);
            
            $currentThumbnailData = $smartLibrary['thumbnail_data'];
            
            $currentUploadFileData = $smartLibrary['upload_file_data'];
        }
        
        //sync file changes to server
        if ($currentThumbnailData !== $data['thumbnail_data']) {
            $oldFileArray = [];

            $newFileArray = [];
            
            if (strpos($currentThumbnailData, '://') === false && $currentThumbnailData !== '') {
                $oldFileArray = [$currentThumbnailData];
            }

            if (strpos($data['thumbnail_data'], '://') === false && $data['thumbnail_data'] !== '') {
                $newFileArray = [$data['thumbnail_data']];
            }
            
            if (count($oldFileArray) > 0 || count($newFileArray) > 0) {                
                Uploader::synchronizeServerFile($setting['smart_library_thumbnail'], $oldFileArray, $newFileArray, false);
            }
        }

        if ($currentUploadFileData !== $data['upload_file_data']) {
            $oldFileArray = [];

            $newFileArray = [];
            
            if (strpos($currentUploadFileData, '://') === false && $currentUploadFileData !== '') {
                $oldFileArray = [$currentUploadFileData];
            }

            if (strpos($data['upload_file_data'], '://') === false && $data['upload_file_data'] !== '') {
                $newFileArray = [$data['upload_file_data']];
            }

            if (count($oldFileArray) > 0 || count($newFileArray) > 0) {
                Uploader::synchronizeServerFile($setting['smart_library_file'], $oldFileArray, $newFileArray, false);
            }
        }
        
        $smartLibraryData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'language_id' => $data['language_id'],
            'sale_type_id' => $data['sale_type_id'],
            'product_category_id' => $data['product_category_id'],
            'product_id' => $data['product_id'],
            'status' => $data['status'],
            'sequence_priority' => $data['sequence_priority'],
            'thumbnail_data' => $data['thumbnail_data'],
            'upload_file_type' => $data['upload_file_type'],
            'upload_file_data' => $data['upload_file_data'],
            'new_joiner_essential_tools' => $data['new_joiner_essential_tools']
        ];
        
        if (isset($smartLibrary)) {
            $smartLibrary->update(array_merge(['updated_by' => Auth::id()], $smartLibraryData));
        }
        else
        {
            $smartLibrary = Auth::user()
                ->createdBy($this->modelObj)
                ->create($smartLibraryData);
        }

        $smartLibrary->smartLibraryCountries()
            ->sync($data['countries']);
        
        return array_merge(['errors' => $errorBag],
            $this->show($smartLibrary->id)->toArray()
        );
    }
    
    /**
     * delete smart library
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $smartLibrary = $this->modelObj
            ->findOrFail($id);

        $countryIds = $smartLibrary
            ->smartLibraryCountries()
            ->get()
            ->pluck('id')
            ->toArray();

        $smartLibrary
            ->smartLibraryCountries()
            ->detach($countryIds);

        $setting = Uploader::getUploaderSetting(true);
        
        if (strpos($smartLibrary->thumbnail_data, '://') === false) {
            Uploader::deleteServerFile($setting['smart_library_thumbnail'], [$smartLibrary['thumbnail_data']]);            
        }

        if (strpos($smartLibrary->upload_file_data, '://') === false) {
            Uploader::deleteServerFile($setting['smart_library_file'], [$smartLibrary['upload_file_data']]);            
        }

        $smartLibrary->delete();
    }
}
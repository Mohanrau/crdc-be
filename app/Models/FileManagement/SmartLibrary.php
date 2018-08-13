<?php
namespace App\Models\FileManagement;

use App\Models\{
    Locations\Country, 
    Languages\Language, 
    Masters\MasterData, 
    Products\ProductCategory, 
    Products\Product
};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;

class SmartLibrary extends Model
{
    protected $table = 'smart_libraries';

    protected $appends = ['thumbnail_link', 'upload_file_link'];

    protected $fillable = [
        'title',
        'description',
        'language_id',
        'sale_type_id',
        'product_category_id',
        'product_id',
        'status',
        'sequence_priority',
        'thumbnail_data',
        'upload_file_type',
        'upload_file_data',
        'new_joiner_essential_tools',
        'updated_by'
    ];

    public $fileTypeOption = [
        "I" => "Image",
        "P" => "PDF",
        "V" => "Video",
        "L" => "Link"
    ];

    /**
     * get smart library country for a given SmartLibraryObj
    
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function smartLibraryCountries()
    {   
        return $this
            ->belongsToMany(Country::class, 'smart_library_countries', 'smart_library_id', 'country_id')
            ->withTimestamps();
    }

    /**
     * get smart library language for a given SmartLibraryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    /**
     * get smart library sale type for a given SmartLibraryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleType()
    {
        return $this->belongsTo(MasterData::class, 'sale_type_id', 'id');
    }

    /**
     * get smart library product category for a given SmartLibraryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    /**
     * get smart library product for a given SmartLibraryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the thumbnail url.
     *
     * @return string
     */
    public function getThumbnailLinkAttribute()
    {
        if (strpos($this->thumbnail_data, '://') === false) {
            return Uploader::getFileLink('file', 'smart_library_thumbnail', $this->thumbnail_data);
        }
        else {
            return $this->thumbnail_data;
        }
    }

    /**
     * Get the upload file url.
     *
     * @return string
     */
    public function getUploadFileLinkAttribute()
    {
        if (strpos($this->upload_file_data, '://') === false) {
            return Uploader::getFileLink('file', 'smart_library_file', $this->upload_file_data);
        }
        else {
            return $this->upload_file_data;
        }
    }
}
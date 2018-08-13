<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasAudit;

    protected $table = 'product_images';

    protected $appends = ['image_link'];

    protected $fillable = [
        'country_id',
        'entity_id',
        'product_id',
        'image_path',
        'default',
        'active'
    ];

    /**
     * check if tax active
     *
     * @param $query
     * @return mixed
     */
    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }

    /**
     * get country details for a given productImageObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    
    /**
     * Get the image url.
     *
     * @return string
     */
    public function getImageLinkAttribute()
    {
        return Uploader::getFileLink('file', 'product_standard_image', $this->image_path);
    }
}

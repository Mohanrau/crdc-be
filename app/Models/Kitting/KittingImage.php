<?php
namespace App\Models\Kitting;

use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;

class KittingImage extends Model
{
    protected $table = 'kitting_images';

    protected $appends = ['image_link'];

    protected $fillable = [
        'kitting_id',
        'image_path',
        'default',
        'active'
    ];

    /**
     * check if image is default
     *
     * @param $query
     * @return mixed
     */
    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }

    /**
     * check if image is active
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Get the image url.
     *
     * @return string
     */
    public function getImageLinkAttribute()
    {
        return Uploader::getFileLink('file', 'product_kitting_image', $this->image_path);
    }
}

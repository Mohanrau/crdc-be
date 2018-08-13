<?php
namespace App\Models\Dummy;

use App\{
    Helpers\Traits\HasAudit,
    Models\Products\Product
};
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    use HasAudit;

    protected $table = 'dummies';

    protected $fillable = [
        'country_id',
        'dmy_code',
        'dmy_name',
        'is_lingerie',
        'active'
    ];

    /**
     * get products details for a given dummyObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dummyProducts()
    {
        return $this->belongsToMany(Product::class, 'dummies_products');
    }
}

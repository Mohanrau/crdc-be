<?php
namespace App\Models\Shop;

use App\Helpers\Traits\HasAudit;
use App\Models\{
    Products\Product,
    Kitting\Kitting,
    Users\User
};
use Illuminate\Database\Eloquent\Model;

class Favorites extends Model
{
    use HasAudit;

    protected
        $table = 'shop_favorites',
        $fillable = [
            'product_id',
            'kitting_id',
            'user_id',
            'country_id',
            'location_id'
        ];

    /**
     * Product Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Kitting Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitting()
    {
        return $this->belongsTo(Kitting::class, 'kitting_id');
    }

    /**
     * User Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Finds user favorites for a given country and location
     *
     * @param $userId
     * @return mixed
     */
    public function getFavoritesForUser($userId)
    {
        return $this
            ->where('user_id', $userId)
            ->with('user', 'product', 'product.productImages', 'product.productDescriptions',
                'kitting', 'kitting.kittingImages', 'kitting.kittingDescriptions')
            ->get();
    }

    /**
     * Get favorite product and kitting
     *
     * @param $userId
     * @param null $productId
     * @param null $kittingId
     * @return mixed
     * @throws \Exception
     */
    public function getFavoriteProductAndKitting($userId, $productId = null, $kittingId = null)
    {
        if (empty($productId) && empty($kittingId)) {
            throw new \Exception(trans("message.product.either-should-exist"));
        }
        $product = $this->where('user_id', $userId)
             ->with('user', 'product', 'kitting');

        if (!empty($productId)) {
            $product->where('product_id', $productId);
        }

        if (!empty($kittingId)) {
            $product->where('kitting_id', $kittingId);
        }
        return $product->first();
    }
}

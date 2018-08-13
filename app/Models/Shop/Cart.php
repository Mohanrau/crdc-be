<?php
namespace App\Models\Shop;

use App\Helpers\Traits\HasAudit;
use App\Helpers\Classes\UserIdentifier;

use App\Models\{
    Locations\Country,
    Locations\Location,
    Products\Product,
    Kitting\Kitting,
    Users\User,
    Users\Guest
};
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasAudit;

    protected
        $table = 'shop_cart',

        $fillable = [
            'product_id',
            'kitting_id',
            'sales_type_id',
            'user_identifier',
            'user_identifier_model',
            'country_id',
            'location_id',
            'quantity',
            'order_for_user_id'
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
     * get mapped user identifier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getUserIdentifier()
    {
        $guestModel = new Guest();
        $userModel = new User();
        switch($this->user_identifier_model){
            case $guestModel->getTable() :
                return $this->belongsTo(Guest::class, 'user_identifier');
                break;
            case $userModel->getTable() :
                return $this->belongsTo(User::class, 'user_identifier');
                break;
            default :
                return null;
        }
    }

    /**
     * Country Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Location Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Finds user cart items
     *
     * @param UserIdentifier $userIdentifier
     * @param int|null $countryId
     * @param int|null $locationId
     * @param int|null $orderForUserId
     * @return mixed
     */
    public function getUserCartItems(
        UserIdentifier $userIdentifier,
        ?int $countryId = null,
        ?int $locationId = null,
        ?int $orderForUserId = null
    )
    {
        $items = $this->where('user_identifier', $userIdentifier->identifier)
            ->where('user_identifier_model', $userIdentifier->modelTable);

        if ($countryId) {
            $items->where('country_id', $countryId);
        }

        if ($locationId) {
            $items->where('location_id', $locationId);
        }

        if ($orderForUserId) {
            $items->where('order_for_user_id', $orderForUserId);
        }

        return $items->with('location', 'country', 'product', 'kitting')
        ->get();
    }

    /**
     * Returns a single cart item
     *
     * @param UserIdentifier $userIdentifier
     * @param int $countryId
     * @param int $locationId
     * @param int|null $productId
     * @param int|null $kittingId
     * @param int|null $orderForUserId
     * @return mixed
     * @throws \Exception
     */
    public function getSingleCartItem(
        UserIdentifier $userIdentifier,
        int $countryId,
        int $locationId,
        ?int $productId = null,
        ?int $kittingId = null,
        ?int $orderForUserId = null
    )
    {
        if (empty($productId) && empty($kittingId)) {
            throw new \Exception(trans("message.product.either-should-exist"));
        }

        $item = $this->where('user_identifier', $userIdentifier->identifier)
                     ->where('user_identifier_model', $userIdentifier->modelTable)
                     ->where('country_id', $countryId)
                     ->where('location_id', $locationId)
                     ->with('location', 'country', 'product', 'kitting');

        if (!empty($productId)) {
            $item->where('product_id', $productId);
        }

        if (!empty($kittingId)) {
            $item->where('kitting_id', $kittingId);
        }

        if ($orderForUserId) {
            $item->where('order_for_user_id', $orderForUserId);
        }

        return $item->first();
    }

    /**
     * Add an item with quantity
     *
     * if the item quantity is set to zero, there is no point of having the item in the cart
     *
     * @param Cart $item
     * @param int $quantity
     * @return Cart
     * @throws \Exception
     */
    public function addItemQuantity(Cart $item, int $quantity)
    {
        if ($quantity == 0) {
            $item->delete();
            return [];
        }

        $item->quantity = $quantity;

        $item->update();

        return $item;
    }
}

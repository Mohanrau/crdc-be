<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Guest;

class GuestSale extends Model
{
    public
        $timestamps = false
    ;

    protected
        $table = 'guest_sales',
        $fillable = []
    ;

    /**
     * get sale for a given saleProductsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Guests for the guest sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guests()
    {
        return $this->HasMany(Guest::class, 'guest_unique_id', 'unique_id');
    }
}

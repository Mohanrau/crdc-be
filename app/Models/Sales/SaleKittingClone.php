<?php
namespace App\Models\Sales;

use App\Models\Kitting\Kitting;
use Illuminate\Database\Eloquent\Model;

class SaleKittingClone extends Model
{
    protected $table = 'sales_kitting_clone';

    protected $fillable = [
        'sale_id',
        'kitting_id',
        'country_id',
        'name',
        'code',

        'quantity',
        'available_quantity',

        'transaction_type_id',
        'currency_id',
        'gmp_price_gst',
        'rp_price',
        'rp_price_gst',
        'nmp_price',
        'effective_date',
        'expiry_date',
        'base_cv',
        'wp_cv',
        'cv1',
        'cv2',
        'cv3',
        'cv4',
        'cv5',
        'cv6',
        'eligible_cv',
        'virtual_invoice_cv',
        'welcome_bonus_l1',
        'welcome_bonus_l2',
        'welcome_bonus_l3',
        'welcome_bonus_l4',
        'welcome_bonus_l5',
    ];

    /**
     * get kitting details for a given saleKittingCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitting()
    {
        return $this->belongsTo(Kitting::class);
    }

    /**
     * get sale kitting products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(SaleProduct::class, 'mapping_id', 'id')
            ->where('mapping_model', 'sales_kitting_clone');
    }

    //Todo implement that part - jalala
    public function productsParent()
    {
        return $this->morphMany(SaleProduct::class, 'mapping');
    }
}

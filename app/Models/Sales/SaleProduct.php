<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\MasterData;

class SaleProduct extends Model
{
    use HasAudit;

    protected $table = 'sales_products';

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_price_id',
        'type_id',

        'mapping_id',
        'mapping_model',

        'transaction_type_id',
        'quantity',
        'available_quantity',
        'foc_qty',

        'gmp_price_gst',
        'rp_price',
        'rp_price_gst',
        'nmp_price',

        'average_price_unit', // for kitting
        'total',

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

        //this cols for pwp foc items
        'option_id',
        'set_id',
        'set_key',
        'operator'
    ];

    protected $append = [
        'country_id'
    ];

    /**
     * get sale for a given saleProductsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * get product details for a given salesProductsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(SaleProductClone::class, 'product_id');
    }

    /**
     * get the mapped model of this product sale - to find out if this is a loose product, pwp or kitting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getMappedModel()
    {
        switch($this->mapping_model){
            case 'sales_kitting_clone' :
                return $this->belongsTo(SaleKittingClone::class, 'mapping_id');
                break;
            case 'sales_promotion_free_items_clone' :
                return $this->belongsTo(SalePromotionFreeItemClone::class, 'mapping_id');
                break;
            default :
                return $this->product();
        }
    }

    /**
     * get the sizes chosen for the given SaleProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productSizes()
    {
        return $this->hasMany(SaleProductSize::class);
    }

    /**
     * get transaction type for the given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType()
    {
        return $this->belongsTo(MasterData::class,'transaction_type_id');

    }

    /**
     * get the current delivery state for the given product
     *
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    public function deliveryStage()
    {
        return $this->hasMany(DeliveryOrder::class, 'sales_product_id');
    }

    /**
     * Get the country id for the given product.
     *
     * @return int
     */
    public function getAttributeCountryId()
    {
        if($this->sale)
        {
            return $this->sale->country_id;
        }
        
        return null;
    }
}

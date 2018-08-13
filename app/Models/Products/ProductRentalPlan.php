<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use App\Models\Locations\Entity;
use Illuminate\Database\Eloquent\Model;

class ProductRentalPlan extends Model
{
    use HasAudit;

    protected $table = 'products_rentals_plans';

    protected $fillable = [
        'country_id',
        'entity_id',
        'product_id',
        'initial_payment',
        'monthly_repayment',
        'total_payment',
        'tenure',
        'number_of_cw'
    ];

    /**
     * get country details for a given productRentalPlanObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get entity details for a given productRentalPlanObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    /**
     * get product details for a given productRentalPlanObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * get product rental cv allocation for a given productRentalPlanObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productRentalCvAllocation()
    {
        return $this->hasMany(ProductRentalCvAllocation::class, 'product_rental_plan_id');
    }
}

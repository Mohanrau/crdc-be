<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;;
use Illuminate\Database\Eloquent\Model;

class ProductRentalCvAllocation extends Model
{
    use HasAudit;

    protected $table = 'products_rentals_cv_allocations';

    protected $fillable = [
        'product_rental_plan_id',
        'cw_number',
        'allocate_cv'
    ];

    /**
     * get product rental cv a llocation for a given productRentalCvAllocationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productRentalPlan()
    {
        return $this->belongsTo(ProductRentalPlan::class, 'product_rental_plan_id');
    }

}

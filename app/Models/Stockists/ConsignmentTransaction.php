<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class ConsignmentTransaction extends Model
{
    use HasAudit;

    protected $table = 'consignments_transactions';

    protected $fillable = [
        'stockist_id',
        'mapping_id',
        'mapping_model',
        'stockist_deposit',
        'stockist_credit_limit',
        'consignment_ratio_provided',
        'consignment_stock_return',
        'cumulative_deposit',
        'cumulative_credit_limit',
        'cumulative_consigned_stock',
        'average_consignment_ratio',
        'unutilised_credit_limit'
    ];

    /**
     * get stockist detail for a given consignmentTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }
}

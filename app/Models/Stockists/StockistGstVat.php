<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistGstVat extends Model
{
    use HasAudit;

    protected $table = 'stockists_gst_vats';

    protected $fillable = [
        'stockist_id',
        'gst_vat_detail'
    ];

    /**
     * get stockist detail for a given stockistGstVatObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }
}

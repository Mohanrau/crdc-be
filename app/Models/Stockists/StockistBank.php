<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistBank extends Model
{
    use HasAudit;

    protected $table = 'stockists_banks';

    protected $fillable = [
        'stockist_id',
        'bank_detail',
        'courier_no'
    ];

    /**
     * get stockist detail for a given stockistBankObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }
}

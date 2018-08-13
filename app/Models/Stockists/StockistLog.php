<?php
namespace App\Models\Stockists;

use App\Models\Masters\MasterData;
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistLog extends Model
{
    use HasAudit;

    protected $table = 'stockists_logs';

    protected $fillable = [
        'stockist_id',
        'stockist_type_id',
        'status_id',
        'stockist_ratio',
        'ibs_online',
        'effective_date',
        'remark',
        'updated_by'
    ];

    /**
     * get stockist detail for a given stockistLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get stockist type details for a given stockistLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistType()
    {
        return $this->belongsTo(MasterData::class,'stockist_type_id');
    }

    /**
     * get status details for a given stockistLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class,'status_id');
    }

}

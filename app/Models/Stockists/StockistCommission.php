<?php
namespace App\Models\Stockists;

use App\Models\{
    Stockists\Stockist,
    General\CWSchedule,
    Currency\Currency
};
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistCommission extends Model
{
    use HasAudit;

    protected $table = 'stockist_commission';

    protected $fillable = [
        'cw_id',
        'stockist_id',
        'otc_wp_cv',
        'otc_wp_commission_percentage',
        'otc_wp_amount',
        'otc_others_cv',
        'otc_others_commission_percentage',
        'otc_others_amount',
        'total_otc_cv',
        'total_otc_amount',
        'online_wp_cv',
        'online_wp_commission_percentage',
        'online_wp_amount',
        'online_others_cv',
        'online_others_commission_percentage',
        'online_others_amount',
        'total_online_cv',
        'total_online_amount',
        'tax_company_name',
        'tax_no',
        'tax_type',
        'tax_rate',
        'gross_commission',
        'currency_rate',
        'local_currency_id',
        'total_gross_amount',
        'total_tax_amount',
        'total_nett_amount'
    ];

    /**
     * get stockist details for a given stockistCommissionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class);
    }

    /**
     * get cwSchedule details for a given stockistCommissionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class);
    }

    /**
     * get member details for a given stockistCommissionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class,'local_currency_id');
    }

}

<?php
namespace App\Models\Campaigns;

use App\Models\{
    Campaigns\EsacPromotion,
    Campaigns\EsacVoucherSubType,
    Masters\MasterData
};
use Illuminate\Database\Eloquent\Model;

class EsacPromotionVoucherSubType extends Model
{
    protected $table = 'esac_promotion_voucher_sub_types';

    protected $fillable = [
        'promotion_id',
        'voucher_sub_type_id',
        'voucher_period_id',
        'voucher_amount',
        'min_purchase_amount'
    ];
    
    /**
     * get promotion for a given EsacPromotionEsacVoucherSubTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promotion()
    {
        return $this->belongsTo(EsacPromotion::class, 'promotion_id', 'id');
    }

    /**
     * get voucher sub type for a given EsacPromotionEsacVoucherSubTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucherSubType()
    {
        return $this->belongsTo(EsacVoucherSubType::class, 'voucher_sub_type_id', 'id');
    }

     /**
     * get voucher period for a given EsacPromotionEsacVoucherSubTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucherPeriod()
    {
        return $this->belongsTo(MasterData::class, 'voucher_period_id', 'id');
    }
}
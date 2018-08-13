<?php
namespace App\Models\Campaigns;

use App\Models\{
    Locations\Country,
    Campaigns\Campaign,
    Campaigns\EsacPromotion,
    Campaigns\EsacVoucherType,
    Campaigns\EsacVoucherSubType,
    Masters\MasterData,
    Users\User
};
use App\Helpers\Traits\LastModified;
use Illuminate\Database\Eloquent\Model;

class EsacVoucher extends Model
{
    use LastModified;

    protected $table = 'esac_vouchers';

    protected $appends = ['last_modified_by', 'last_modified_at'];

    protected $fillable = [
        'country_id',
        'campaign_id',
        'promotion_id',
        'voucher_type_id',
        'voucher_sub_type_id',
        'voucher_number',
        'voucher_value',
        'voucher_status',
        'voucher_remarks',
        'voucher_period_id',
        'member_user_id',
        'issued_date',
        'expiry_date',
        'redeem_date',
        'max_purchase_qty',
        'min_purchase_amount',
        'active',
        'updated_by'
    ];

    /**
     * get country for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * get campaign for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign()
    {
        return $this
            ->belongsTo(Campaign::class, 'campaign_id', 'id')
            ->with(['fromCwSchedule', 'toCwSchedule']);
    }

    /**
     * get esac promotion for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function esacPromotion()
    {
        return $this->belongsTo(EsacPromotion::class, 'promotion_id', 'id');
    }

    /**
     * get esac voucher type for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function esacVoucherType()
    {
        return $this->belongsTo(EsacVoucherType::class, 'voucher_type_id', 'id');
    }

    /**
     * get esac voucher sub type for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function esacVoucherSubType()
    {
        return $this->belongsTo(EsacVoucherSubType::class, 'voucher_sub_type_id', 'id');
    }

    /**
     * get voucher period for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucherPeriod()
    {
        return $this->belongsTo(MasterData::class, 'voucher_period_id', 'id');
    }

    /**
     * get user  for a given EsacVoucherObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'member_user_id', 'id');
    }
}
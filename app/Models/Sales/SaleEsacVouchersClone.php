<?php
namespace App\Models\Sales;

use App\Models\Sales\Sale;
use App\Models\Campaigns\EsacVoucher;
use Illuminate\Database\Eloquent\Model;

class SaleEsacVouchersClone extends Model
{
    protected $table = 'sales_esac_vouchers_clone';

    protected $fillable = [
        'country_id',
        'sale_id',
        'voucher_id',
        'campaign_id',
        'campaign_name',
        'from_campaign_cw_schedule_id',
        'to_campaign_cw_schedule_id',
        'promotion_id',
        'promotion_taxable',
        'promotion_entitled_by',
        'promotion_max_purchase_qty',
        'voucher_type_id',
        'voucher_type_name',
        'voucher_sub_type_id',
        'voucher_sub_type_name',
        'voucher_number',
        'voucher_value',
        'voucher_status',
        'voucher_remarks',
        'voucher_period_id',
        'member_user_id',
        'issued_date',
        'expiry_date',
        'max_purchase_qty',
        'min_purchase_amount'
    ];

    /**
     * get sale details for a given saleEsacVouchersCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * get esac voucher details for a given saleProductCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function esacVoucher()
    {
        return $this->belongsTo(EsacVoucher::class, 'voucher_id');
    }
}

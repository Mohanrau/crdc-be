<?php
namespace App\Models\Sales;

use App\Models\Sales\Sale;
use App\Models\Campaigns\EsacVoucher;
use Illuminate\Database\Eloquent\Model;

class SaleCancellationEsacVoucher extends Model
{
    protected $table = 'sales_cancellations_esac_vouchers';

    protected $fillable = [
        'sale_esac_voucher_clone_id',
        'sale_cancellation_id',
        'available_quantity_snapshot',
        'quantity',
        'voucher_value',
    ];

    /**
     * get sale esac vouchers clone details for a given saleEsacVouchersCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleEsacVouchersClone()
    {
        return $this->belongsTo(SaleEsacVouchersClone::class, 'sale_esac_voucher_clone_id');
    }

    /**
     * get sale cancellation details for a given saleEsacVouchersCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleCancellation()
    {
        return $this->belongsTo(SaleCancellation::class, 'sale_cancellation_id');
    }
}

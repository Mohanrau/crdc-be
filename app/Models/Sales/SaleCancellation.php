<?php
namespace App\Models\Sales;

use App\Models\{
    Invoices\Invoice,
    Invoices\LegacyInvoice,
    Workflows\WorkflowTracking,
    General\CWSchedule,
    Masters\MasterData,
    Members\Member,
    Users\User,
    Locations\Location,
    Locations\StockLocation
};
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\HasAudit;

class SaleCancellation extends Model
{
    use HasAudit;

    protected $table = 'sales_cancellations';

    protected $fillable = [
        'sale_id',
        'invoice_id',
        'legacy_invoice_id',
        'workflow_tracking_id',
        'transaction_location_id',
        'stock_location_id',
        'user_id',
        'cw_id',
        'cancellation_type_id',
        'cancellation_mode_id',
        'cancellation_reason_id',
        'cancellation_status_id',
        'transaction_date',
        'total_amount',
        'total_buy_back_amount',
        'rounding_adjustment',
        'total_product_cv',
        'remarks',
        'is_legacy',
        'good_receive_completed'
    ];

    /**
     * get sale details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * get invoice details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * get legacy invoice details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legacyInvoice()
    {
        return $this->belongsTo(LegacyInvoice::class, 'legacy_invoice_id');
    }

    /**
     * get credit note for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function creditNote()
    {
        return $this->hasOne(CreditNote::class, 'mapping_id')
            ->where('mapping_model', $this->table);
    }

    /**
     * get sale cancel products for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleCancelProducts()
    {
        return $this->hasMany(SaleCancellationProduct::class, 'sale_cancellation_id');
    }

    /**
     * get sale cancel esac voucher for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleCancelEsacVoucher()
    {
        return $this->hasMany(SaleCancellationEsacVoucher::class, 'sale_cancellation_id')
            ->with('saleEsacVouchersClone');
    }

    /**
     * get workflow track details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTrack()
    {
        return $this->belongsTo(WorkflowTracking::class);
    }

    /**
     * get transaction location details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionLocation()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * get stock location details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockLocation()
    {
        //TODO :: Alson need change to StockLocation when StockLocation API is ready
        return $this->belongsTo(Location::class);
    }

    /**
     * get user details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * get member details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get issuer data for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * get the cwSchedules for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }

    /**
     * get cancellation mode Details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cancellationMode()
    {
        return $this->belongsTo(MasterData::class,'cancellation_mode_id');
    }

    /**
     * get cancellation type details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cancellationType()
    {
        return $this->belongsTo(MasterData::class,'cancellation_type_id');
    }

    /**
     * get cancellation reason details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cancellationReason()
    {
        return $this->belongsTo(MasterData::class,'cancellation_reason_id');
    }

    /**
     * get cancellation status details for a given salesCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cancellationStatus()
    {
        return $this->belongsTo(MasterData::class, 'cancellation_status_id');
    }

    /**
     * get legacy sale cancellation products for a given saleCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function legacySaleCancellationProduct()
    {
        return $this->hasMany(LegacySaleCancellationProduct::class, 'sale_cancellation_id')
            ->whereNull('legacy_sales_cancellations_kitting_clone_id');
    }

    /**
     * get legacy sale cancellation kitting for a given saleCancellationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function legacySaleCancellationKitting()
    {
        return $this->hasMany(LegacySaleCancellationKittingClone::class, 'sale_cancellation_id');
    }

    /**
     * get loose legacy product in sales cancellation
     *
     * @return array
     */
    public function getLegacySaleCancelProducts()
    {
        return $this
            ->legacySaleCancellationProduct()
            ->with('legacySaleCancellationProductClone')
            ->get()
            ->map(function ($item){
                return  [
                    'product_id' => $item['legacySaleCancellationProductClone']->product_id,
                    'name' => $item['legacySaleCancellationProductClone']->name,
                    'sku' => $item['legacySaleCancellationProductClone']->sku,
                    'quantity' => $item->quantity,
                    'eligible_cv' => $item->product_cv,
                    'base_price' => [
                        'gmp_price_tax' => $item->gmp_price_gst,
                        'nmp_price' => $item->nmp_price,
                        'total' => $item->total,
                        'base_cv' => $item->product_cv
                    ],
                    'available_quantity' => $item->available_quantity_snapshot,
                    'cancellation_quantity' => $item->quantity
                ];
            });
    }

    /**
     * get legacy kitting in sales cancellation
     *
     * @return array
     */
    public function getLegacySaleCancellationKitting()
    {
        return $this
            ->legacySaleCancellationKitting()
            ->with('products.legacySaleCancellationProductClone')
            ->get()
            ->map(function ($item){
                return [
                    'kitting_id' => $item->kitting_id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'quantity' => $item->quantity,
                    'available_quantity' => $item->available_quantity_snapshot,
                    'eligible_cv' => $item->product_cv,
                    'kitting_price' => [
                        'gmp_price_tax' => $item->gmp_price_gst,
                        'nmp_price' => $item->nmp_price,
                        'total' => $item->total,
                        'base_cv' => $item->product_cv
                    ],
                    'cancellation_quantity' => $item->quantity,
                    'kitting_products' => collect($item->products)->map(function ($productItem){
                        return [
                            'product_id' => $productItem['legacySaleCancellationProductClone']->product_id,
                            'name' => $productItem['legacySaleCancellationProductClone']->name,
                            'sku' => $productItem['legacySaleCancellationProductClone']->sku,
                            'quantity' => $productItem->quantity,
                            'eligible_cv' => $productItem->product_cv,
                            'base_price' => [
                                'gmp_price_tax' => $productItem->gmp_price_gst,
                                'nmp_price' => $productItem->nmp_price,
                                'average_price_unit' => $productItem->average_price_unit,
                                'total' => $productItem->total,
                                'base_cv' => $productItem->product_cv
                            ],
                            'available_quantity' => $productItem->available_quantity_snapshot,
                            'cancellation_quantity' => $productItem->quantity
                        ];
                    })
                ];
            });
    }
}

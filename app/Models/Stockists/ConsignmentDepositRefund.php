<?php
namespace App\Models\Stockists;

use App\Models\{
    Payments\Payment,
    Masters\MasterData,
    Users\User
};
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class ConsignmentDepositRefund extends Model
{
    use HasAudit;

    protected $table = 'consignments_deposits_refunds';

    protected $fillable = [
        'stockist_id',
        'workflow_tracking_id',
        'type_id',
        'transaction_date',
        'document_number',
        'amount',
        'credit_limit',
        'ratio',
        'minimum_amount',
        'maximum_amount',
        'minimum_capping',
        'remark',
        'status_id',
        'action_by',
        'action_at',
        'verification_status_id',
        'verified_by',
        'verified_at',
        'updated_by'
    ];

    /**
     * get stockist detail for a given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get consignment deposit refund type details for a given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consignmentDepositRefundType()
    {
        return $this->belongsTo(MasterData::class,'type_id');
    }

    /**
     * get consignment deposit refund status details for a given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class,'status_id');
    }

    /**
     * get action by detail by given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actionBy()
    {
        return $this->belongsTo(User::class,'action_by');
    }

    /**
     * get consignment deposit refund verification status details for a given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verificationStatus()
    {
        return $this->belongsTo(MasterData::class,'verification_status_id');
    }

    /**
     * get verified by detail by given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class,'verified_by');
    }

    /**
     * get payments for a given consignmentDepositRefundObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'mapping_id')
            ->where('mapping_model', 'consignments_deposits_refunds')
            ->with('paymentModeProvider');
    }
}

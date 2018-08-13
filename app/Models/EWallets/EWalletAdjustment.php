<?php
namespace App\Models\EWallets;

use App\{
    Helpers\Traits\HasAudit,
    Models\Locations\Country,
    Models\Masters\MasterData,
    Models\Users\User
};
use Illuminate\Database\Eloquent\Model;

class EWalletAdjustment extends Model
{
    use HasAudit;

    protected $table = 'user_ewallet_adjustments';

    protected $fillable = [
        "user_id",
        "country_id",
        "transaction_id",
        "amount_type_id",
        "amount",
        "reason_id",
        "remarks",
        "level_one_status",
        "level_one_reason",
        "level_one_by",
        "level_one_approval_at",
        "level_two_status",
        "level_two_reason",
        "level_two_by",
        "level_two_approval_at"
    ];

    protected $with = [
        'country',
        'transaction',
        'reason',
        'user',
        'amountType',
        'levelOneUser',
        'levelTwoUser',
        'createdBy'
    ];

    protected $appends = ['amount_detail'];

    /**
     * get country for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get country for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(EWalletTransaction::class, 'transaction_id');
    }

    /**
     * get user for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get reason for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(MasterData::class,'reason_id');
    }

    /**
     * get amount type for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function amountType()
    {
        return $this->belongsTo(MasterData::class,'amount_type_id');
    }

    /**
     * get level one user for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelOneUser()
    {
        return $this->belongsTo(User::class, 'level_one_by');
    }

    /**
     * get level two user for a given ewalletAdjustmentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelTwoUser()
    {
        return $this->belongsTo(User::class, 'level_two_by');
    }

    /**
     * get debit and credit amount for a given ewalletAdjustmentObj
     *
     * @return array
     */
    public function getAmountDetailAttribute()
    {
        if( strtolower($this->amountType()->pluck('title')[0]) == 'debit' )
        {
            return [
                'debit_amount' => $this->amount,
                'credit_amount' => null
            ];
        }
        elseif( strtolower($this->amountType()->pluck('title')[0]) == 'credit' )
        {
            return [
                'debit_amount' => null,
                'credit_amount' => $this->amount
            ];
        }
    }
}

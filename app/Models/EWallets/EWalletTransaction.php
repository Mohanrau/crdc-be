<?php
namespace App\Models\EWallets;

use App\{
    Models\Currency\Currency, Models\Masters\MasterData, Models\Users\User, Helpers\Traits\HasAudit
};
use Illuminate\Database\Eloquent\Model;

class EWalletTransaction extends Model
{
    use HasAudit;

    protected $table = 'user_ewallet_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "ewallet_id",
        "currency_id",
        "transaction_type_id",
        "transfer_to_user_id",
        "transaction_date",
        "transaction_number",
        "amount_type_id",
        "amount",
        "before_balance",
        "after_balance",
        "transaction_details",
        "member_payment_info",
        "recipient_email",
        "recipient_reference",
        "transaction_status_id"
    ];

    protected $with = [
        'amountType',
        'transferToUser',
        'currency',
        'transactionType',
        'transactionStatus'
    ];

    protected $appends = [
        'debitAmount' => 'debit_amount',
        'creditAmount' => 'credit_amount',
    ];

    /**
     * Generate Transaction Number
     *
     * @return string
     */
    public function generateTransactionNumber()
    {
        $transaction_number = config('ewallet.transaction_number_start');
        if($this->count())
        {
            $transaction_number = $this->orderBy('id', 'desc')->first()->transaction_number + 1;
        }

        return str_pad($transaction_number, 11, "0", STR_PAD_LEFT);
    }

    /**
     * get e-wallet for a given ewalletTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ewallet()
    {
        return $this->belongsTo(EWallet::class, 'ewallet_id', 'id');
    }

    /**
     * get user for a given ewalletTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transferToUser()
    {
        return $this->belongsTo(User::class, 'transfer_to_user_id', 'id')->with('member.country');
    }

    /**
     * get transaction amount type for a given ewalletTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function amountType()
    {
        return $this->belongsTo(MasterData::class,'amount_type_id');
    }

    /**
     * get currency for a given ewalletTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    /**
     * get transaction type for a given ewalletTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType()
    {
        return $this->belongsTo(MasterData::class,'transaction_type_id');
    }

    /**
     * get transaction status for a given ewalletTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionStatus()
    {
        return $this->belongsTo(MasterData::class,'transaction_status_id');
    }

    /**
     * get debit amount for transaction
     *
     * @return int
     */
    public function getDebitAmountAttribute()
    {
        return ($this->amountType->title == config('mappings.ewallet_amount_type.debit')) ? $this->attributes['amount'] : null;
    }

    /**
     * get credit amount for transaction
     *
     * @return int
     */
    public function getCreditAmountAttribute()
    {
        return ($this->amountType->title == config('mappings.ewallet_amount_type.credit')) ? $this->attributes['amount'] : null;
    }
}

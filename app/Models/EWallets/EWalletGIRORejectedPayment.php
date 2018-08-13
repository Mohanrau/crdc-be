<?php
namespace App\Models\EWallets;

use App\Helpers\Traits\HasAudit;
use App\Models\{
    Currency\Currency,
    Locations\Country,
    Users\User
};
use Illuminate\Database\Eloquent\Model;

class EWalletGIRORejectedPayment extends Model
{
    use HasAudit;

    protected $table = 'user_ewallet_giro_rejected_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "country_id",
        "currency_id",
        "file_no",
        "rejected_amount",
        "total_amount",
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
        'user',
        'currency',
        'levelOneUser',
        'levelTwoUser',
        'createdBy'
    ];

    /**
     * Generate File No.
     *
     * @return string
     */
    public function generateFileNo()
    {
        $file_no = config('ewallet.giro_rejected_payment_file_no');
        if($this->count())
        {
            $file_no = $this->orderBy('id', 'desc')->first()->file_no + 1;
        }

        return str_pad($file_no, 9, "0", STR_PAD_LEFT);
    }

    /**
     * get country for a given ewalletGIRORejectedPaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get user for a given ewalletGIRORejectedPaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get currency for a given ewalletGIRORejectedPaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * get level one user for a given ewalletGIRORejectedPaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelOneUser()
    {
        return $this->belongsTo(User::class, 'level_one_by');
    }

    /**
     * get level two user for a given ewalletGIRORejectedPaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelTwoUser()
    {
        return $this->belongsTo(User::class, 'level_two_by');
    }
}

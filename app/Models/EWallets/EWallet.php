<?php
namespace App\Models\EWallets;

use App\{Helpers\Traits\HasAudit, Models\Currency\Currency, Models\Users\User};
use Illuminate\Database\Eloquent\Model;

class EWallet extends Model
{
    use HasAudit;

    protected $table = 'user_ewallets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "default_currency_id",
        "security_pin",
        "balance",
        "auto_withdrawal",
        "active",
        "blocked"
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = [
        'activated'
    ];

    /**
     * The relationship attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $with = [
        'currency'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "security_pin"
    ];

    /**
     * get user for a given ewalletObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * get transactions for a given ewalletObj
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(EWalletTransaction::class, 'ewallet_id', 'id');
    }

    /**
     * get currency obj for a given ewalletObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id', 'id');
    }

    /**
     * get eWallet activated status for given eWalletObj
     *
     * @return bool
     */
    public function getActivatedAttribute()
    {
        return empty($this->security_pin) ? 0 : 1;
    }
}

<?php
namespace App\Models\Stockists;

use App\Models\{
    Locations\Country,
    Locations\Location,
    Masters\MasterData,
    Members\Member,
    Users\User
};
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class Stockist extends Model
{
    use HasAudit;

    protected $table = 'stockists';

    protected $fillable = [
        'member_user_id',
        'stockist_user_id',
        'country_id',
        'stockist_type_id',
        'status_id',
        'stockist_number',
        'name',
        'email',
        'stockist_ratio',
        'ibs_online',
        'registered_date',
        'effective_date',
        'updated_by'
    ];

    /**
     * get country details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get user details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function memberUser()
    {
        return $this->belongsTo(User::class,'member_user_id');
    }

    /**
     * get stockist user details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistUser()
    {
        return $this->belongsTo(User::class,'stockist_user_id');
    }

    /**
     * get member details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'member_user_id','user_id')
            ->with('user');
    }

    /**
     * get stockist type details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistType()
    {
        return $this->belongsTo(MasterData::class,'stockist_type_id');
    }

    /**
     * get status details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class,'status_id');
    }

    /**
     * get business registration address for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function businessAddress()
    {
        return $this->hasOne(StockistBusinessAddress::class, 'stockist_id');
    }

    /**
     * get deposit credit limit for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function depositSetting()
    {
        return $this->hasOne(StockistDepositSetting::class, 'stockist_id');
    }

    /**
     * get stockist bank for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function bank()
    {
        return $this->hasOne(StockistBank::class, 'stockist_id');
    }

    /**
     * get gst vat for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function gstVat()
    {
        return $this->hasOne(StockistGstVat::class, 'stockist_id');
    }

    /**
     * get stockist logs for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockistLog()
    {
        return $this->hasMany(StockistLog::class, 'stockist_id')
            ->with('createdBy');
    }

    /**
     * get stockist location details for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistLocation()
    {
        return $this->belongsTo(Location::class,'stockist_number', 'code');
    }

    /**
     * get consignment deposit refund for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function consignmentDepositRefund()
    {
        return $this->hasMany(ConsignmentDepositRefund::class, 'stockist_id');
    }

    /**
     * get consignment order return for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function consignmentOrderReturn()
    {
        return $this->hasMany(ConsignmentOrderReturn::class, 'stockist_id');
    }

    /**
     * get consignment transaction for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function consignmentTransaction()
    {
        return $this->hasMany(ConsignmentTransaction::class, 'stockist_id');
    }

    /**
     * get stockist consignment product for a given stockistObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function stockistConsignmentProduct()
    {
        return $this->hasMany(StockistConsignmentProduct::class, 'stockist_id');
    }
}

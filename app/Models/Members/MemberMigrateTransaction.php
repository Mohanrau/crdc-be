<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;
use App\{
    Helpers\Traits\HasAudit,
    Models\General\CWSchedule,
    Models\Members\Member,
    Models\Masters\MasterData,
    Models\Locations\Country
};

class MemberMigrateTransaction extends Model
{
    use HasAudit;

    protected $table = 'members_migrates_transactions';

    protected $fillable = [
        "user_id",
        "country_id",
        "previous_country_id",
        "cw_id",
        "reason_id",
        "case_reference_number"
    ];

    
    /**
     * get member details for a given memberMigrateTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user','country');
    }

    /**
     * get cw details for a given memberMigrateTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }

    /**
     * get country details for a given memberMigrateTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get previous country details for a given memberMigrateTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function previousCountry()
    {
        return $this->belongsTo(Country::class, 'previous_country_id');
    }

    /**
     * get reason for a given memberMigrateTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(MasterData::class,'reason_id');
    }

}

<?php
namespace App\Models\Members;

use App\Models\Masters\MasterData;
use Illuminate\Database\Eloquent\Model;

class MemberContactInfo extends Model
{
    protected $table = 'member_contact_info';

    protected $fillable = [
        'user_id',
        'preferred_contact_id',
        'mobile_1_country_code_id',
        'mobile_1_num',
        'mobile_1_activated',
        'mobile_2_country_code_id',
        'mobile_2_num',
        'tel_office_1_country_code_id',
        'tel_office_1_num',
        'tel_office_2_country_code_id',
        'tel_office_2_num',
        'tel_home_1_country_code_id',
        'tel_home_1_num',
        'fax_country_code_id',
        'fax_num',
        'email',
        'email_verified',
        'replicator_website',
        'country_of_residence_id',
    ];

    protected $with = [
        'preferredContact'
    ];

    /**
     * get status for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preferredContact()
    {
        return $this->belongsTo(MasterData::class,'preferred_contact_id');
    }
}

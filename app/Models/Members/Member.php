<?php
namespace App\Models\Members;

use App\{
    Models\Bonus\EnrollmentRank,
    Models\Enrollments\EnrollmentTypes,
    Models\General\CWSchedule,
    Models\Locations\Country,
    Models\Masters\MasterData,
    Models\Users\User,
    Models\Bonus\TeamBonusRank,
    Helpers\Traits\HasAudit
};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasAudit;

    protected $table = 'members';

    protected $fillable = [
        'user_id',
        'country_id',
        'avatar_image_path',
        'name',
        'translated_name',
        'nationality_id',
        'ic_pass_type_id',
        'active_until_cw_id',
        'ic_passport_number',
        'cw',
        'date_of_birth',
        'join_date',
        'expiry_date',
        'personal_sales_cv',
        'personal_sales_cv_percentage',
        'effective_rank_id',

        'enrollment_rank_id',
        'enrollment_type_id',

        'highest_rank_id',
        'tin_no_taiwan',
        'tin_no_taiwan_verified',
        'tin_no_philippines',
        'tin_no_philippines_verified',
        'enroll_from_received',
        'ic_pass_verified',
        'defer_bonus_commission',
        'defer_reason_id',
        'bank_type',
        'status_id',
    ];

    protected $appends = [
        'avatar_image_link'
    ];

    /**
     * get country details for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get nationality country details for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nationality()
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    /**
     * get member personal data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function personalData()
    {
        return $this->hasOne(MemberPersonalData::class, 'user_id', 'user_id');
    }

    /**
     * get member ic or passport photos
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function iCPassport()
    {
        return $this->hasMany(MemberICPassport::class, 'user_id', 'user_id');
    }

    /**
     * get member beneficiary for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function beneficiary()
    {
        return $this->hasOne(MemberBeneficiary::class, 'user_id', 'user_id');
    }

    /**
     * get member payments for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment()
    {
        return $this->hasOne(MemberPayment::class, 'user_id', 'user_id');
    }

    /**
     * get member address for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address()
    {
        return $this->hasOne(MemberAddress::class, 'user_id', 'user_id');
    }

    /**
     * get the contact info for the given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contactInfo()
    {
        return $this->hasOne(MemberContactInfo::class, 'user_id', 'user_id');
    }

    /**
     * get member tree for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sponsor()
    {
        return  $this->hasOne(MemberTree::class, 'user_id', 'user_id');
    }

    /**
     * get member tree for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tree()
    {
        return  $this->hasOne(MemberTree::class, 'user_id', 'user_id');
    }

    /**
     * get the taxes for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tax()
    {
        return $this->hasOne(MemberTax::class,'user_id', 'user_id');
    }

    /**
     * get member's enrollment rank for given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function enrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'enrollment_rank_id');
    }

    /**
     * get the enrollment type for the give memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function enrollmentType()
    {
        return $this->belongsTo(EnrollmentTypes::class, 'enrollment_type_id');
    }

    /**
     * get member's effective rank for given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function effectiveRank()
    {
        return $this->belongsTo(TeamBonusRank::class, 'effective_rank_id');
    }

    /**
     * get member's highest rank for given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     */
    public function highestRank()
    {
        return $this->belongsTo(TeamBonusRank::class, 'highest_rank_id');
    }

    /**
     * get user for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * get status for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class,'status_id');
    }

    /**
     * get avatar url for given memberObj
     *
     * @return mixed
     */
    public function getAvatarImageLinkAttribute()
    {
        if(!is_null( $this->attributes['avatar_image_path'] ) && $this->attributes['avatar_image_path'] != "" )
        {
            return Uploader::getFileLink('file', 'member_avatar', $this->attributes['avatar_image_path']);
        }
        else {
            return null;
        }
    }

    public function activeUntilCw()
    {
        return $this->belongsTo(CWSchedule::class, 'active_until_cw_id');
    }

    /**
     * Get Member Active Status in currenct and last cw
     *
     * @param $cwScheduleRepositoryObj
     * @param $masterRepositoryObj
     * @param $memberActiveRecordObj
     * @param $memberSaleActivitiesStatusConfigCodes
     * @return array
     */
    public function getMemberSaleActivityStatus(
        $cwScheduleRepositoryObj,
        $masterRepositoryObj,
        $memberActiveRecordObj,
        $memberSaleActivitiesStatusConfigCodes
    )
    {
        $member = $this;

        //get current and previous cw active status
        $currentCwSchedule = $cwScheduleRepositoryObj
            ->getCwSchedulesList("current", [
                'limit' => 0,
                'sort' => 'id',
                'order' => 'asc'
            ])
            ->get('data')
            ->first();

        $lastCwSchedule = $cwScheduleRepositoryObj
            ->getCwSchedulesList("past", [
                'limit' => 0,
                'sort' => 'cw_name',
                'order' => 'desc'
            ])
            ->get('data')
            ->first();

        //Get Member Status Master Data Value
        $memberSaleActivityStatusData = $masterRepositoryObj->getMasterDataByKey(
            array('member_sale_activities_status'));

        $memberSaleActivityStatusList = array_change_key_case($memberSaleActivityStatusData['member_sale_activities_status']
            ->pluck('id','title')->toArray());

        $activeStatusObj = collect($memberSaleActivityStatusData['member_sale_activities_status'])
            ->where('id', $memberSaleActivityStatusList[$memberSaleActivitiesStatusConfigCodes['active']])
            ->first();

        $inactiveStatusObj = collect($memberSaleActivityStatusData['member_sale_activities_status'])
            ->where('id', $memberSaleActivityStatusList[$memberSaleActivitiesStatusConfigCodes['inactive']])
            ->first();

        $lastCwActiveRecord = $memberActiveRecordObj->where('user_id', $member->user_id)
            ->where('cw_id', $lastCwSchedule->id)
            ->where('is_active', 1)
            ->first();

        $currentCwActiveRecord = $memberActiveRecordObj->where('user_id', $member->user_id)
            ->where('cw_id', $currentCwSchedule->id)
            ->where('is_active', 1)
            ->first();

        return [
            'current_cw' => [
                'cw_name' => $currentCwSchedule->cw_name,
                'status' => ($currentCwActiveRecord) ?
                        $activeStatusObj : $inactiveStatusObj
            ],
            'previous_cw' => [
                'cw_name' => $lastCwSchedule->cw_name,
                'status' => ($lastCwActiveRecord) ?
                        $activeStatusObj : $inactiveStatusObj
            ]
        ];
    }
}

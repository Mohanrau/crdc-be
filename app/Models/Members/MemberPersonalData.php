<?php
namespace App\Models\Members;

use App\Models\Languages\Language;
use App\Models\Masters\MasterData;
use Illuminate\Database\Eloquent\Model;

class MemberPersonalData extends Model
{
    protected $table = 'members_personal_data';

    protected $fillable = [
        'user_id',
        'language_id',
        'gender_id',
        'salutation_id',
        'ethnic_group_id',
        'religion_id',
        'martial_status_id',
        'spouse_elken_member',
        'spouse_name',
        'spouse_ibo_id',
        'ic_pass_type_id',
        'ic_pass_type_number',
        'education_id',
        'occupation_id',
        'industry_id',
        'salary_range_id',
        'annual_revenue_id',
    ];

    /**
     * change the presentation for memberPersonalData
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'language_id' => $this->language_id,
            'language_data' =>  $this->language()->first(),
            'gender_id' => $this->gender_id,
            'gender_data' => $this->gender()->first(),
            'salutation_id' => $this->salutation_id,
            'ethnic_group_id' => $this->ethnic_group_id,
            'religion_id' => $this->religion_id,
            'martial_status_id' => $this->martial_status_id,
            'spouse' => [
                'spouse_elken_member' => $this->spouse_elken_member,
                'spouse_name' => $this->spouse_name,
                'spouse_ibo_id' => $this->spouse_ibo_id,
                'ic_pass_type_id' => $this->ic_pass_type_id,
                'ic_pass_type_number' => $this->ic_pass_type_number,
            ],
            'education_id' => $this->education_id,
            'occupation_id' => $this->occupation_id,
            'industry_id' => $this->industry_id,
            'salary_range_id' => $this->salary_range_id,
            'annual_revenue_id' => $this->annual_revenue_id,
        ];
    }

    /**
     * get language details for a given MemberPersonalDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * get gender data for a given MemberPersonalDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender()
    {
        return $this->belongsTo(MasterData::class,'gender_id');
    }

    /**
     * get the salutation data for a given MemberPersonalDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salutation()
    {
        return $this->belongsTo(MasterData::class,'salutation_id');

    }

    /**
     * get the ethnicGroup data for a given MemberPersonalDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ethnicGroup()
    {
        return $this->belongsTo(MasterData::class,'ethnic_group_id');
    }

    /**
     * get the  martial status data for a given MemberPersonalDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function martialStatus()
    {
        return $this->belongsTo(MasterData::class,'martial_status_id');
    }
}

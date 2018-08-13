<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;

class MemberBeneficiary extends Model
{
    protected $table = 'members_beneficiary';

    protected $fillable = [
        'user_id',
        'beneficiary_name',
        'beneficiary_type_id',
        'beneficiary_ic_num',
        'beneficiary_mobile_code_id',
        'beneficiary_phone_num',
        'beneficiary_office_phone_code_id',
        'beneficiary_office_phone_num',
        'beneficiary_home_phone_code_id',
        'beneficiary_home_phone_num',
        'beneficiary_relationship_id',
    ];
}

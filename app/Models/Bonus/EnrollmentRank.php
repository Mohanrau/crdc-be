<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;

class EnrollmentRank extends Model
{
    protected $table = 'enrollment_ranks';

    protected $fillable = [
        'enrollment_type_id',
        'rank_code',
        'rank_name',
        'CV',
        'entitlement_lvl',
        'sales_types'
    ];
}

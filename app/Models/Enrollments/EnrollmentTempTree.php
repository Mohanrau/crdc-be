<?php
namespace App\Models\Enrollments;

use Illuminate\Database\Eloquent\Model;

class EnrollmentTempTree extends Model
{
    protected $table = 'enrollments_temp_tree';

    protected $fillable = [
        'unique_id',
        'user_id',
        'sponsor_user_id',
        'placement_user_id',
        'placement_position',
        'is_auto'
    ];
}

<?php
namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserOTP extends Model
{
    protected $table = 'user_otps';

    protected $fillable = [
        'user_id',
        'contact',
        'code',
        'code_type_id',
        'tries',
        'send_count',
        'verified',
        'expired'
    ];

    protected $hidden = [
        'code'
    ];
}

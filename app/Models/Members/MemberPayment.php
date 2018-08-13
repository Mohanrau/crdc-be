<?php
namespace App\Models\Members;

use App\Models\Masters\MasterData;
use Illuminate\Database\Eloquent\Model;

class MemberPayment extends Model
{
    protected $table = 'members_payments_data';

    protected $fillable = [
        'user_id',
        'payment_data',
    ];
}

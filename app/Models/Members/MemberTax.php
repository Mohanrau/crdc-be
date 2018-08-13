<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;

class MemberTax extends Model
{
    protected $table = 'member_taxes';

    protected $fillable = [
        'user_id',
        'tax_data'
    ];
}

<?php
namespace App\Models\Payments;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class MposTransaction extends Model
{
    use HasAudit;

    protected $table = 'mpos_transactions';

    protected $fillable = [
        'amount',
        'terminal_id',
        'merchant_id',
        'payment_id',
        'redeemed',
        'approval_code',
        'params'
    ];
}

<?php
namespace App\Models\Payments;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class AeonPaymentFtpLog extends Model
{
    use HasAudit;

    protected $table = 'aeon_payments_ftp_logs';

    protected $fillable = [
        'country_id',
        'request_file_name',
        'response_file_name',
        'ftp_status'
    ];

}

<?php
namespace App\Models\General;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class CWSchedule extends Model
{
    use HasAudit;

    protected $table = 'cw_schedules';

    protected $fillable = [
        'cw_name',
        'date_from',
        'date_to'
    ];
}

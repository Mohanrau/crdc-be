<?php
namespace App\Models\Enrollments;

use App\Models\Masters\MasterData;
use App\Models\Sales\Sale;
use Illuminate\Database\Eloquent\Model;

class EnrollmentTemp extends Model
{
    protected $table = 'enrollments_temp_data';

    protected $fillable = [
        'unique_id',
        'user_id',
        'member_id',
        'sale_id',
        'sms_code',
        'temp_data',
        'status_id'
    ];

    /**
     * get master data info - status for the given enrollmentTempObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterData()
    {
        return $this->belongsTo(MasterData::class, 'status_id');
    }

    /**
     * get sale info
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}

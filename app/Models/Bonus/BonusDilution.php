<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Models\General\CWSchedule;

class BonusDilution extends Model
{
    protected $table = 'bonus_dilution';

    protected $fillable = [
        'cw_id',
        'diluted_percentage'
    ];

    /**
     * return commission week for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }
}

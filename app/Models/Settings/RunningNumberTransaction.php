<?php
namespace App\Models\Settings;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class RunningNumberTransaction extends Model
{
    use HasAudit;

    protected $table = 'running_number_transactions';

    protected $fillable = [
        'running_number_setting_id',
        'prefix',
        'suffix',
        'running_no'
    ];

    /**
     * get running number setting info for a given RunningNumberTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function runningNumberSetting()
    {
        return $this->belongsTo(RunningNumberSetting::class);
    }
}

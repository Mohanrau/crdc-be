<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistDepositSettingLogs extends Model
{
    use HasAudit;

    protected $table = 'stockists_deposits_settings_logs';

    protected $fillable = [
        'stockist_deposit_setting_id',
        'minimum_initial_deposit',
        'maximum_initial_deposit',
        'minimum_top_up_deposit',
        'maximum_top_up_deposit',
        'minimum_capping',
        'credit_limit_1',
        'credit_limit_2',
        'credit_limit_3',
        'updated_by'
    ];

    /**
     * get stockist detail for a given stockistDepositSettingLogsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistDepositSetting()
    {
        return $this->belongsTo(StockistDepositSetting::class,'stockist_deposit_setting_id');
    }
}

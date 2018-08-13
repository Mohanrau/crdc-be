<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistDepositSetting extends Model
{
    use HasAudit;

    protected $table = 'stockists_deposits_settings';

    protected $fillable = [
        'stockist_id',
        'minimum_initial_deposit',
        'maximum_initial_deposit',
        'minimum_top_up_deposit',
        'maximum_top_up_deposit',
        'minimum_capping',
        'credit_limit_1',
        'credit_limit_2',
        'credit_limit_3',
        'deposit_balance',
        'deposit_limit',
        'ar_balance',
        'updated_by'
    ];

    /**
     * get stockist detail for a given stockistDepositSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get stockist deposit setting logs for a given stockistDepositSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function depositSettingLogs()
    {
        return $this->hasMany(StockistDepositSettingLogs::class, 'stockist_deposit_setting_id');
    }

    /**
     * get last modified column field in stockist deposit setting
     *
     * @param string $columnField
     * @return array
     */
    public function getLastModifiedDepositSettings(string $columnField)
    {
        return $this
            ->depositSettingLogs()
            ->whereNotNull($columnField)
            ->orderBy('created_at', 'desc')
            ->select(
                $columnField,
                'created_at as last_modified'
            )
            ->first()
            ->toArray();
    }
}

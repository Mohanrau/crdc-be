<?php
namespace App\Models\Settings;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class RunningNumberSetting extends Model
{
    use HasAudit;

    protected $table = 'running_number_settings';

    protected $fillable = [
        'code',
        'name',
        'is_general_mode',
        'prefix',
        'suffix',
        'begin_number',
        'running_width',
        'active'
    ];

    /**
     * get running number transaction for a given RunningNumberSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function runningNumberTransaction()
    {
        return $this->hasMany(RunningNumberTransaction::class);
    }

    /**
     * get running number special format setup for a given RunningNumberSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function runningNumberSpecialFormatSettings()
    {
        return $this->hasMany(RunningNumberSpecialFormatSettings::class);
    }
}

<?php
namespace App\Models\Settings;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class RunningNumberSpecialFormatSettings extends Model
{
    use HasAudit;

    protected $table = 'running_number_special_format_settings';

    protected $fillable = [
        'running_number_setting_id',
        'country_id',
        'prefix',
        'suffix',
        'date_from',
        'date_to',
        'begin_number',
        'end_number',
        'running_width',
        'active'
    ];

    /**
     * get running number master setting info for a given runningNumberSpecialFormatSettingsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function runningNumberMasterSetting()
    {
        return $this->belongsTo(RunningNumberSetting::class);
    }

    /**
     * get country info for a given runningNumberSpecialFormatSettingsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}

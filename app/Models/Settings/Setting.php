<?php
namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'setting_key_id',
        'value',
        'mapping_id',
        'mapping_model',
        'active'
    ];

    /**
     * get Setting Key info for a given SettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function settingKey()
    {
        return $this->belongsTo(settingKey::class);
    }
}

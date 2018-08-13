<?php
namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class SettingKey extends Model
{
    protected $table = 'settings_keys';

    protected $fillable = [
        'name',
        'key'
    ];

    /**
     * get setting for a given settingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settingData()
    {
        return $this->hasMany(Setting::class);
    }
}

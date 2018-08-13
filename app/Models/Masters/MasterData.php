<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payments\PaymentModeProvider;
use App\Models\Payments\PaymentModeSetting;

class MasterData extends Model
{
    protected $table = 'master_data';

    protected $fillable = [
        'master_id',
        'title',
        'sort'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Cast Name to Capitalize First Letter of every word
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return ucwords( strtolower($this->attributes['title']) );
    }

    /**
     * get Master info for a given MasterDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function master()
    {
        return $this->belongsTo(Master::class);
    }

    /**
     * get payment mode provider for a given masterDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentModeProvider()
    {
        return $this->hasMany(PaymentModeProvider::class);
    }

    /**
     * get payment mode setting for a given masterDataObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function paymentModeSetting()
    {
        return $this->hasManyThrough(PaymentModeSetting::class,  PaymentModeProvider::class);
    }

    /**
     * Get master data id when given a title and master key
     *
     * @param string $title
     * @param string $masterKey
     * @return mixed
     */
    public function getIdByTitle(string $title, string $masterKey)
    {
        return $this
            ->whereHas('master',  function($query) use ($masterKey){
                $query->where('key', $masterKey);
            })
            ->where('title', $title)
            ->pluck('id')
            ->first();
    }
}

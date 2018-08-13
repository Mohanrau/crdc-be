<?php
namespace App\Models\Campaigns;

use App\Models\{
    Locations\Country,
    Campaigns\EsacVoucherSubType
};
use App\Helpers\Traits\LastModified;
use Illuminate\Database\Eloquent\Model;

class EsacVoucherType extends Model
{
    use LastModified;

    protected $table = 'esac_voucher_types';

    protected $appends = ['last_modified_by', 'last_modified_at'];
    
    protected $fillable = [
        'country_id',
        'name',
        'description',
        'active',
        'updated_by'
    ];

    /**
     * get country for a given EsacVoucherTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * get voucher sub type for a given EsacVoucherTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function esacVoucherSubTypes()
    {
        return $this->hasMany(EsacVoucherSubType::class, 'voucher_type_id', 'id');
    }
}
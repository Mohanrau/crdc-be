<?php
namespace App\Models\Stockists;

use App\Models\Locations\Country;
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistBusinessAddress extends Model
{
    use HasAudit;

    protected $table = 'stockists_business_addresses';

    protected $fillable = [
        'stockist_id',
        'contact_person',
        'mobile_1_country_code_id',
        'mobile_1_num',
        'mobile_2_country_code_id',
        'mobile_2_num',
        'telephone_office_country_code_id',
        'telephone_office_num',
        'fax_country_code_id',
        'fax_num',
        'email_1',
        'email_2',
        'addresses'
    ];

    /**
     * get stockist detail for a given stockistBusinessAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get mobile one country code details for a given stockistBusinessAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mobileOneCountryCode()
    {
        return $this->belongsTo(Country::class,'mobile_1_country_code_id');
    }

    /**
     * get mobile two country code details for a given stockistBusinessAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mobileTwoCountryCode()
    {
        return $this->belongsTo(Country::class,'mobile_2_country_code_id');
    }

    /**
     * get telephone office country code details for a given stockistBusinessAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function telephoneOfficeCountryCode()
    {
        return $this->belongsTo(Country::class,'telephone_office_country_code_id');
    }

    /**
     * get fax country code details for a given stockistBusinessAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function faxCountryCode()
    {
        return $this->belongsTo(Country::class,'fax_country_code_id');
    }
}

<?php
namespace App\Models\Locations;

use App\Models\{
    Locations\Country,
    Locations\State, 
    Locations\City,
    Locations\Location
};
use Illuminate\Database\Eloquent\Model;

class LocationAddresses extends Model
{
    protected $table = 'locations_addresses_data';

    protected $fillable = [
        'location_id',
        'telephone_code_id',
        'telephone_num',
        'mobile_phone_code_id',
        'mobile_phone_num',
        'country_id',
        'state_id',
        'area',
        'display_name',
        'address_data'
    ];

    protected $append = [
    	'permanent_address',
    	'correspondence_address'
    ];

    /**
     * get location info for a given locationAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * get country info for a given locationAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get state info for a given locationAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Getter for correspondance address
     * 
     * @return string
     */
    public function getCorrespondenceAddressAttribute()
    {
        return $this->getAddress("Correspondence");
    }

    /**
     * Getter for permanent address
     * 
     * @return string
     */
    public function getPermanentAddressAttribute()
    {
        return $this->getAddress("Permanent");
    }

    /**
     * Common function to return address in 1 line of string.
     * Type of address can be "Permanent", "Correspondence", "Shipping1", "Shipping2" 
     * @param string $type 
     * @return string
     */
    private function getAddress($type)
    {
    	if(empty($this->address_data))
        {
            return '';
        }
        
        $address = json_decode($this->address_data, true);
        
        if (empty($address))
        {
        	return '';
        }
        
        $info = [];

        foreach ($address as $row)
        {
            if ($row['title'] == $type)
            {
                $info = $row['fields'];
                break;
            }
        }

        $fullAddress = "";

        $size = count($info);

        for($i = 0; $i < $size; $i++)
        {
            if ($info[$i]['label'] == 'postcode')
            {
                break;
            }

            if ($info[$i]['value'])
            {
            	$fullAddress .= str_replace("\n", "<br/>", $info[$i]['value'])."<br/>";
            }
        }
        return $fullAddress;
    }
}

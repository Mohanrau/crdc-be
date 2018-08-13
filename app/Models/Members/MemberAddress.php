<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;

class MemberAddress extends Model
{
    protected $table = 'members_addresses_data';

    protected $fillable = [
        'user_id',
        'address_data'
    ];

    protected $append = [
    	'permanent_address',
    	'correspondence_address'
    ];

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
     * Type of address can be "Premanent", "Correspondence", "Shipping1", "Shipping2"
     *
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

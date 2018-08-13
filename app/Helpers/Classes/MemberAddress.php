<?php
namespace App\Helpers\Classes;

use App\Models\Locations\
{
    Country,
    State,
    City
};

/**
 * Helper class to read Member Address data from json format.
 */
class MemberAddress
{
    private $cityObj, $countryObj, $stateObj;

    /**
     * MemberAddress constructor.
     *
     * @param City $cityObj
     * @param Country $countryObj
     * @param State $stateObj
     */
    public function __construct(
        City $cityObj,
        Country $countryObj,
        State $stateObj
    )
    {
        $this->cityObj = $cityObj;

        $this->countryObj = $countryObj;

        $this->stateObj = $stateObj;
    }

    /**
     * Get Correspondence address from json address data
     *
     * @param mixed $address 
     * @return string
     */
    public function getCorrespondenceAddress($address)
    {
        return $this->getAddress($address, "Correspondence");
    }

    /**
     * Get Permanent address from json address data
     *
     * @param mixed $address 
     * @return string
     */
    public function getPermanentAddress($address)
    {
        return $this->getAddress($address, "Permanent");
    }

    /**
     * Get Shipping 1 address from json address data
     *
     * @param mixed $address 
     * @return string
     */
    public function getShipping1Address($address)
    {
        return $this->getAddress($address, "Shipping 1");
    }

    /**
     * Get Shipping 2 address from json address data
     *
     * @param mixed $address 
     * @return string
     */
    public function getShipping2Address($address)
    {
        return $this->getAddress($address, "Shipping 2");
    }

    /**
     * Trim string
     *
     * @param string $haystack 
     * @param string $needle 
     * @return string
     */
    function trim($haystack, $needle)
    {
        $length = strlen($needle);
        
        if (substr($haystack, 0, $length) === $needle)
        {
            $haystack = substr($haystack, 1);
        }

        $len = strlen($haystack);

        if(substr($haystack, -$length) === $needle)
        {
            $haystack = substr($haystack, 0, $len-1);
        }

        $haystack = str_replace('\"', '"', $haystack);

        return $haystack;
    }

    /**
     * Get address field from json address data based on type. 
     * Helper for subsequent functions to parse the value in different format. 
     * Type can Permanent, Correspondence, Shipping 1, Shipping 2
     *
     * @param mixed $address 
     * @param string $type 
     * @return string
     */
    private function getAddressFields($address, $type)
    {
        $info = null;

        if(empty($address))
        {
            return $info;
        }
        
        $address = $this->trim($address, "\"");

        $address = json_decode($address, true);

        if (!$address)
        {
            return $info;
        }
        
        foreach ($address as $row)
        {
            if ($type == "" || $row['title'] == $type)
            {
                $info = $row['fields'];
                break;
            }
        }

        if ($info == null) {
            return $info;
        }

        $size = count($info);

        for($i = 0; $i < $size; $i++)
        {
            if (isset($info[$i]['key'])) {
                if ($info[$i]['key'] == 'cities')
                {
                    $cityObj = $this->cityObj->find($info[$i]['value']);

                    if ($cityObj)
                    {
                        $info[$i]['value'] = $cityObj->name;
                    }
                }
                else if ($info[$i]['key'] == 'states')
                {
                    $stateObj = $this->stateObj->find($info[$i]['value']);
                    
                    if ($stateObj)
                    {
                        $info[$i]['value'] = $stateObj->name;
                    }
                }
                else if ($info[$i]['key'] == 'countries')
                {
                    $countryObj = $this->countryObj->find($info[$i]['value']);
                    
                    if ($countryObj)
                    {
                        $info[$i]['value'] = $countryObj->name;
                    }
                }
            }
        }

        return $info;
    }

    /**
     * Get address in one line space delimited string from json address data based on type.
     * Type can Permanent, Correspondence, Shipping 1, Shipping 2
     *
     * @param mixed $address 
     * @param string $type 
     * @return string
     */
    public function getAddress($address, $type)
    {
        $lineBreak = " ";
        
        $info = $this->getAddressFields($address, $type);

        if ($info == null) {
            return '';
        }

        $fullAddress = $postcode = $city = $country = $state = "";

        $size = count($info);

        for($i = 0; $i < $size; $i++)
        {
            if ($info[$i]['label'] == 'postcode')
            {
                $postcode = $info[$i]['value'];
            }
            else if (isset($info[$i]['key']) && $info[$i]['key'] == 'cities')
            {
                $city = $info[$i]['value'];
            }
            else if (isset($info[$i]['key']) && $info[$i]['key'] == 'states')
            {
                $state = $info[$i]['value'];
            }
            else if (isset($info[$i]['key']) && $info[$i]['key'] == 'countries')
            {
                $country = $info[$i]['value'];
            }
            else if ($info[$i]['value'])
            {
                $fullAddress .= str_replace("\n", "<br/>", $info[$i]['value']).$lineBreak;
            }
        }

        return $fullAddress.$postcode.$lineBreak.$city.$lineBreak.$state.$lineBreak.$country;
    }

    /**
     * Get address in yy data structure format from json address data based on type.
     * Type can Permanent, Correspondence, Shipping 1, Shipping 2
     *
     * @param mixed $address 
     * @param string $type 
     * @return array
     */
    public function getAddressAsYYStructure($address, $type)
    {
        $yyAddress = [
            'addr1' => '',
            'addr2' => '',
            'addr3' => '',
            'addr4' => '',
            'postcode' => '',
            'city' => '',
            'state' => '',
            'country' => ''
        ];
        
        $info = $this->getAddressFields($address, $type);

        if ($info == null) {
            return $yyAddress;
        }

        $size = count($info);

        for ($i = 0; $i < $size; $i++)
        {
            if ($info[$i]['value'] != null) {
                if (isset($info[$i]['map'])) {
                    if ($info[$i]['map'] == config('mappings.yy_address.addr1'))
                    {
                        if ($yyAddress['addr1'] !== '') {
                            $yyAddress['addr1'] = $yyAddress['addr1'] . ' ';
                        }
        
                        $yyAddress['addr1'] = $yyAddress['addr1'] . $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.addr2'))
                    {
                        if ($yyAddress['addr2'] !== '') {
                            $yyAddress['addr2'] = $yyAddress['addr2'] . ' ';
                        }
        
                        $yyAddress['addr2'] = $yyAddress['addr2'] . $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.addr3'))
                    {
                        if ($yyAddress['addr3'] !== '') {
                            $yyAddress['addr3'] = $yyAddress['addr3'] . ' ';
                        }
        
                        $yyAddress['addr3'] = $yyAddress['addr3'] . $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.addr4'))
                    {
                        if ($yyAddress['addr4'] !== '') {
                            $yyAddress['addr4'] = $yyAddress['addr4'] . ' ';
                        }
        
                        $yyAddress['addr4'] = $yyAddress['addr4'] . $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.postcode'))
                    {
                        $yyAddress['postcode'] = $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.city'))
                    {
                        $yyAddress['city'] = $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.state'))
                    {
                        $yyAddress['state'] = $info[$i]['value'];
                    }
                    else if ($info[$i]['map'] == config('mappings.yy_address.country'))
                    {
                        $yyAddress['country'] = $info[$i]['value'];
                    }  
                } 
                else { // fallback to use key/label
                    if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.address1'))) {
                        $yyAddress['addr1'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.address2'))) {
                        $yyAddress['addr2'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.address3'))) {
                        $yyAddress['addr3'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.address4'))) {
                        $yyAddress['addr4'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.postcode'))) {
                        $yyAddress['postcode'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.city'))) {
                        $yyAddress['city'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.state'))) {
                        $yyAddress['state'] = $info[$i]['value'];
                    } else if (strtolower($info[$i]['label']) == strtolower(config('mappings.member_shipping_address.country'))) {
                        $yyAddress['country'] = $info[$i]['value'];
                    }
                }
            }
        }

        return $yyAddress;
    }

    /**
     * Get address in array format from json address data based on type.
     * Type can Permanent, Correspondence, Shipping 1, Shipping 2
     *
     * @param mixed $address 
     * @param string $type 
     * @return array
     */
    public function toArray($address, $type)
    {
        $result = [];

        $info = $this->getAddressFields($address, $type);

        if ($info == null) {
            return $result;
        }

        $size = count($info);

        for($i = 0; $i < $size; $i++)
        {
            array_push($result, $info[$i]['value']);
        }

        return $result;
    }
}
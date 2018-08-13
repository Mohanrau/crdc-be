<?php
namespace App\Helpers\Classes;

/**
 * Utilities class for various common function.
 */
class Utilities
{
    /**
     * Get Language ISO code based on Country ISO code.
     *
     * @param $countryISOCode
     * @return string
     */
    public static function getLangCode($countryISOCode)
    {
        switch ($countryISOCode) {
            case 'TW':
            case 'CN':
            case 'HK':
                return 'zh';
            case 'TH':
                return 'th';
            case 'KH':
                return 'km';
            default:
                return 'en';
                break;
        }
    }
}
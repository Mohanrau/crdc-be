<?php
namespace App\Helpers\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class CvBreakdown implements Arrayable
{
    protected
        $breakdown,
        $saleTypes
    ;

    /**
     * CvBreakdown constructor.
     *
     * @param array $breakdown keys should match /config/mappings.php[cv_acronym]
     * @param array $saleTypes keys should match /config/setting.php[sale-type-cvs] and include all keys of /config/setting.php[cv-mechanism]
     */
    public function __construct (array $breakdown, array $saleTypes) {
        $this->breakdown = $breakdown;

        $this->saleTypes = $saleTypes;
    }

    /**
     * @return array
     */
    public function getBreakdown () {
        return $this->breakdown;
    }

    /**
     * @return array
     */
    public function getSaleTypes () {
        return $this->saleTypes;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray ()
    {
        return [
            'break_down' => $this->breakdown,
            'sales_types' => $this->saleTypes
        ];
    }
}
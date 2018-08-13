<?php
namespace App\Http\Resources\Products;

use Illuminate\Http\Resources\Json\Resource;

class ProductResource extends Resource
{
    private $countryId;

    public function __construct($resource, int $countryId)
    {
        parent::__construct($resource);

        $this->countryId = $countryId;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        dd($this->countryId);


        return [
            'product_id' => $this->id,
            'entity' => $this->entity,
            'locations' => $this->locations
        ];
    }
}

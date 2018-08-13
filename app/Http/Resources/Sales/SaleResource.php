<?php
namespace App\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\Resource;

class SaleResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $products = $this->saleProducts()->get();

        return [
            'sales_data' => $this->resource->toArray(),
            'products' => $products
        ];
    }
}

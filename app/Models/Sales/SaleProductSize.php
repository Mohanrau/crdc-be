<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SaleProductSize extends Model
{
    protected $table = 'sales_products_sizes';

    protected $fillable = [
        'sale_id',
        'sale_product_id',
        'product_id',
        'master_id',
        'master_data_id'
    ];
}

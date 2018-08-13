<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class SaleProductOption extends Model
{
    use HasAudit;

    protected $table = 'sales_products_options';

    protected $fillable = [
        'sale_products_id',
        'sale_id',
        'product_id',
        'e-voucher'
    ];
}

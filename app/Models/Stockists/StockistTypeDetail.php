<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistTypeDetail extends Model
{
    use HasAudit;

    protected $table = 'stockist_type_details';

    protected $fillable = [
        'stockist_type_id',
        'online_wp_percentage',
        'online_other_percentage',
        'otc_wp_percentage',
        'otc_other_percentage'
    ];
}

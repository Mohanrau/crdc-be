<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class ProductPriceSetting extends Model
{
    use HasAudit;

    protected $table = 'product_prices_settings';

    protected $fillable = [
        'country_id',
        'entity_id',
        'product_id',
        'currency_id',
        'base_cv',
        'wp_cv',
        'cv1',
        'cv2',
        'welcome_bonus_l1',
        'welcome_bonus_l2',
        'welcome_bonus_l3',
        'welcome_bonus_l4',
        'welcome_bonus_l5',
        'promo'
    ];
}

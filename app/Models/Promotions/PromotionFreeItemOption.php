<?php
namespace App\Models\Promotions;

use Illuminate\Database\Eloquent\Model;

class PromotionFreeItemOption extends Model
{
    protected $table = 'promotion_free_items_options';

    protected $fillable = [
        'promo_id',
        'option_id',
        'option_products'
    ];

    public $timestamps = false;
}

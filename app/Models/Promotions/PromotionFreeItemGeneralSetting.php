<?php
namespace App\Models\Promotions;

use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\DB
};

class PromotionFreeItemGeneralSetting extends Model
{
    protected $table = 'promotion_free_items_general_settings';

    protected $fillable = [
        'promo_id',
        'master_id',
        'master_data_id'
    ];

    public $timestamps = false;

    /**
     * get master title for a given countryId and productId
     *
     * @param int $promoId
     * @return mixed
     */
    public function getMasters(int $promoId)
    {
        return DB::table($this->table)
            ->leftJoin('master_data', 'master_data.id', '=', $this->table.'.master_data_id')
            ->leftJoin('masters', 'masters.id', '=', 'master_data.master_id')
            ->select(
                'masters.id As masterId',
                'masters.title',
                'masters.key'
            )
            ->where($this->table.'.promo_id', $promoId)
            ->groupBy('masters.title')
            ->get();
    }
}

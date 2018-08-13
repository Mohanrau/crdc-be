<?php
namespace App\Models\Products;

use App\Models\Masters\MasterData;
use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\DB
};

class ProductGeneralSetting extends Model
{
    protected $table = 'product_general_settings';

    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'entity_id',
        'product_id',
        'master_id',
        'master_data_id'
    ];

    /**
     * get masterData for a given productObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterData()
    {
        return $this->belongsTo(MasterData::class);
    }

    /**
     * get master title for a given countryId and productId
     *
     * @param int $countryId
     * @param int $entityId
     * @param int $productId
     * @return mixed
     */
    public function getMasters(int $countryId, int $entityId,  int $productId)
    {
        return DB::table($this->table)
            ->leftJoin('master_data', 'master_data.id', '=', $this->table.'.master_data_id')
            ->leftJoin('masters', 'masters.id', '=',  $this->table.'.master_id')
            ->select(
                'masters.id As masterId',
                'masters.title',
                'masters.key'
            )
            ->where($this->table.'.product_id', $productId)
            ->where($this->table.'.country_id', $countryId)
            ->where($this->table.'.entity_id', $entityId)
            ->groupBy('masters.title')
            ->get();
    }
}

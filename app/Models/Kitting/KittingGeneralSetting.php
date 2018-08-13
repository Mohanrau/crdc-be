<?php
namespace App\Models\Kitting;

use App\Models\Masters\MasterData;
use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\DB
};

class KittingGeneralSetting extends Model
{
    protected $table = 'kitting_general_settings';

    protected $fillable = [
        'kitting_id',
        'master_id',
        'master_data_id'
    ];

    public $timestamps = false;

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
     * @param int $kittingId
     * @return mixed
     */
    public function getMasters(int $kittingId)
    {
        return DB::table($this->table)
            ->leftJoin('master_data', 'master_data.id', '=', $this->table.'.master_data_id')
            ->leftJoin('masters', 'masters.id', '=', 'master_data.master_id')
            ->select(
                'masters.id As masterId',
                'masters.title',
                'masters.key'
            )
            ->where($this->table.'.kitting_id', $kittingId)
            ->groupBy('masters.title')
            ->get();
    }
}

<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Helpers\Classes\Master as MasterHelper;

/**
 * @method static getMasterData (string $masterKey)
 * @method static getMasterDataArray (string $masterKey)
 * @method static idBelongsToMaster (string $masterKey, int $masterId)
 * @method static getMasterDataTitleById (string $masterKey, int $masterDataId)
 * @method static getMasterId(string $masterKey)
 * @method static getMasterDataById(string $masterKey, int $masterId)
 * @method static getMasterDataByTitle(string $masterKey, string $title)
 *
 * @see \App\Helpers\Classes\Master
 */
class Master extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MasterHelper::class;
    }
}
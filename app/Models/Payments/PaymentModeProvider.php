<?php
namespace App\Models\Payments;

use App\Helpers\Traits\HasAudit;
use App\Models\Masters\MasterData;
use Illuminate\Database\Eloquent\Model;

class PaymentModeProvider extends Model
{
    use HasAudit;

    protected $table = 'payments_modes_providers';

    protected $fillable = [
        'master_data_id',
        'code',
        'name'
    ];

    /**
     * get payment mode setting for a given paymentModeProviderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentModeSetting()
    {
        return $this->hasMany(PaymentModeSetting::class);
    }

    /**
     * get payments mode details for a given paymentModeProviderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentsMode()
    {
        return $this->belongsTo(MasterData::class,'master_data_id');
    }
}

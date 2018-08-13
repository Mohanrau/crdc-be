<?php
namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Model;
use App\{
    Helpers\Traits\HasAudit,
    Models\Locations\Country
};

class PaymentModeSetting extends Model
{
    use HasAudit;

    protected $table = 'payments_modes_settings';

    protected $fillable = [
        'payment_mode_provider_id',
        'location_type_id',
        'country_id',
        'configuration_file_name',
        'allow_partial',
        'setting_detail',
        'active'
    ];

    /**
     * get country details for a given paymentModeSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get payment mode provider details for a given paymentModeSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentModeProvider()
    {
        return $this->belongsTo(PaymentModeProvider::class, 'payment_mode_provider_id');
    }
}

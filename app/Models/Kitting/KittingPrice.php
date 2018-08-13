<?php
namespace App\Models\Kitting;

use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Model;

class KittingPrice extends Model
{
    protected $table = 'kitting_prices';

    protected $fillable = [
        'kitting_id',
        'currency_id',
        'gmp_price_gst',
        'rp_price',
        'rp_price_gst',
        'nmp_price',
        'effective_date',
        'expiry_date',
        'base_cv',
        'wp_cv',
        'cv1',
        'cv2',
        'cv3',
        'cv4',
        'cv5',
        'cv6',
        'welcome_bonus_l1',
        'welcome_bonus_l2',
        'welcome_bonus_l3',
        'welcome_bonus_l4',
        'welcome_bonus_l5',
        'active'
    ];

    /**
     * get currency details for a given kittingPriceObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * change the representation of array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'kitting_id' => $this->kitting_id,
            'gmp_price_tax' => $this->gmp_price_gst,
            'nmp_price' => $this->nmp_price,
            'rp_price' => $this->rp_price,
            'rp_price_tax' => $this->rp_price_gst,
            'effective_date' => $this->effective_date,
            'expiry_date' => $this->expiry_date,
            'base_cv' => $this->base_cv,
            'wp_cv' => $this->wp_cv,
            'cv_1' => $this->cv1,
            'cv_2' => $this->cv2,
            'cv_3' => $this->cv3,
            'cv_4' => $this->cv4,
            'cv_5' => $this->cv5,
            'cv_6' => $this->cv6,
            'cv1' => $this->cv1,
            'cv2' => $this->cv2,
            'cv3' => $this->cv3,
            'cv4' => $this->cv4,
            'cv5' => $this->cv5,
            'cv6' => $this->cv6,
            'bonuses' => [
                'welcome_bonus_1' => $this->welcome_bonus_l1,
                'welcome_bonus_2' => $this->welcome_bonus_l2,
                'welcome_bonus_3'=> $this->welcome_bonus_l3,
                'welcome_bonus_4' => $this->welcome_bonus_l4,
                'welcome_bonus_5' => $this->welcome_bonus_l5,
            ]
        ];
    }
}

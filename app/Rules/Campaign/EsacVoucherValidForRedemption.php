<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\EsacVoucher;
use Illuminate\Contracts\Validation\Rule;

class EsacVoucherValidForRedemption implements Rule
{
    private $esacVoucherObj, $voucherId;

    /**
     * EsacVoucherValidForRedemption constructor
     * 
     * @param EsacVoucher $esacVoucher
     */
    public function __construct(EsacVoucher $esacVoucher) 
    {
        $this->esacVoucherObj = $esacVoucher;
    }

    /**
     * Determine if the validation rule passes
     * 
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $esacVouchers = $this->esacVoucherObj
            ->where('id', $value)
            ->where('esac_vouchers.voucher_status', 'N')
            ->where('expiry_date', '>=', date('Y-m-d'));
        
        $this->voucherId = $value;
        
        return ($esacVouchers->count() === 1 ? true : false);
    }

    /**
     * Get the validation error message.
     * 
     * @return string
     */
    public function message()
    {
        return __('message.esacVoucher-invalid-for-redemption', [
            'voucher_id' => $this->voucherId
        ]);
    }
} 

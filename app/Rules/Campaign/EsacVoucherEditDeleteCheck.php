<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\EsacVoucher;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleEsacVouchersClone;
use App\Interfaces\Masters\MasterInterface;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Config;

class EsacVoucherEditDeleteCheck implements Rule
{
    private $isEdit,
        $esacVoucherObj, 
        $masterRepositoryObj,
        $saleObj,
        $saleEsacVouchersCloneObj, 
        $esacVoucherNumber;

    /**
     * EsacVoucherEditDeleteCheck constructor
     * 
     * @param bool $isEdit
     * @param EsacVoucher $esacVoucher
     * @param MasterInterface $masterInterface
     * @param Sale $sale
     * @param SaleEsacVouchersClone $saleEsacVouchersClone
     */
    public function __construct(
        bool $isEdit,
        EsacVoucher $esacVoucher,
        MasterInterface $masterInterface,
        Sale $sale,
        SaleEsacVouchersClone $saleEsacVouchersClone) 
    {
        $this->isEdit = $isEdit;

        $this->esacVoucherObj = $esacVoucher;

        $this->masterRepositoryObj = $masterInterface;

        $this->saleObj = $sale;

        $this->saleEsacVouchersCloneObj = $saleEsacVouchersClone;
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
        if ($this->isEdit && !isset($value)) {
            return true;
        }
        else {
            $esacVoucher = $this->esacVoucherObj->find($value);

            if ($esacVoucher !== null) {
                $this->esacVoucherNumber = $esacVoucher->voucher_number;
            }

            $saleOrderStatus = $this->masterRepositoryObj
                ->getMasterDataByKey(['sale_order_status'])['sale_order_status'];

            $cancelledStatus = $saleOrderStatus
                ->where('title', strtoupper(Config::get('mappings.sale_order_status')['cancelled']))
                ->pluck('id')
                ->toArray();

            // get voucher clone count exclude cancelled sale
            $saleEsacVouchersCloneCount = $this->saleEsacVouchersCloneObj
                ->where('voucher_id', '=', $value)
                ->whereNotIn('sale_id', $this->saleObj->whereIn('order_status_id', $cancelledStatus)->pluck('id')->toArray())
                ->count();

            return $saleEsacVouchersCloneCount == 0;
        }
    }

    /**
     * Get the validation error message.
     * 
     * @return string
     */
    public function message()
    {
        $translateKey = ($this->isEdit) ? 
            'message.campaign.cannot-edit-used-master': 
            'message.campaign.cannot-delete-used-master';
        
        $translateParam = [
            'master' => 'eSac Voucher', 
            'name' => $this->esacVoucherNumber
        ];

        return __($translateKey, $translateParam);
    }
} 

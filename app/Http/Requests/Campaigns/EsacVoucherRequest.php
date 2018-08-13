<?php
namespace App\Http\Requests\Campaigns;

use App\Models\Campaigns\EsacVoucher;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleEsacVouchersClone;
use App\Interfaces\Masters\MasterInterface;
use App\Rules\General\MasterDataIdExists;
use App\Rules\Campaign\EsacVoucherEditDeleteCheck;
use Illuminate\Foundation\Http\FormRequest;

class EsacVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param EsacVoucher $esacVoucher
     * @param MasterInterface $masterRepository
     * @param Sale $sale
     * @param SaleEsacVouchersClone $saleEsacVouchersClone
     * @return array
     */
    public function rules(
        EsacVoucher $esacVoucher,
        MasterInterface $masterRepository,
        Sale $sale,
        SaleEsacVouchersClone $saleEsacVouchersClone
    )
    {
        if ($this->has('id')) {
            $ignoredId = $this->input('id');
        }
        else {
            $ignoredId = 'NULL';
        }

        if ($this->has('country_id')) {
            $countryId = $this->input('country_id');
        }
        else {
            $countryId = 'NULL';
        }

        return [
            'id' => [
                'bail', 'sometimes', 'nullable', 'integer', 'exists:esac_vouchers,id',
                new EsacVoucherEditDeleteCheck(
                    true, 
                    $esacVoucher, 
                    $masterRepository, 
                    $sale, 
                    $saleEsacVouchersClone
                )
            ],
            'country_id' => 'required|integer|exists:countries,id',
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'promotion_id' => 'required|integer|exists:esac_promotions,id',
            'voucher_type_id' => 'required|integer|exists:esac_voucher_types,id',
            'voucher_sub_type_id' => 'required|integer|exists:esac_voucher_sub_types,id',
            'voucher_number' => 'required|string|min:3|unique:esac_vouchers,voucher_number,' . $ignoredId . ',id,country_id,' . $countryId,
            'voucher_value' => 'required|numeric|min:0',
            'voucher_status' => 'required|string|in:N,P,V',
            'voucher_remarks' => 'sometimes|nullable|string',
            'voucher_period_id' => [
                'required', 'integer',
                new MasterDataIdExists($masterRepository, 'voucher_period')
            ],
            'member_user_id' => 'required|integer|exists:users,id',
            'issued_date' => 'required|date',
            'expiry_date' => 'required|date'
        ];
    }
}
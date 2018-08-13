<?php
namespace App\Http\Requests\Sales;

use App\{
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface,
    Models\Bonus\EnrollmentRank,
    Models\Enrollments\EnrollmentTemp,
    Models\Members\Member,
    Models\Products\Product,
    Models\Campaigns\EsacVoucher,
    Models\Campaigns\EsacPromotion,
    Models\Locations\Country,
    Models\Kitting\KittingProduct,
    Rules\CW\CwCheck,
    Rules\General\MasterDataIdExists,
    Rules\Product\ProductAvailableInCountry,
    Rules\Sales\SalesCreationMinimumCvValidation,
    Rules\Campaign\EsacVoucherValidForRedemption,
    Rules\Campaign\EsacVoucherRedemption};
use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param MasterInterface $masterInterface
     * @param Product $product
     * @param EsacVoucher $esacVoucher
     * @param EsacPromotion $esacPromotion
     * @param Country $country
     * @param KittingProduct $kittingProduct
     * @return array
     */
    public function rules(
        CwSchedulesInterface $cwSchedulesInterface,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        EnrollmentRank $enrollmentRank,
        EnrollmentTemp $enrollmentTemp,
        Member $member,
        Product $product, 
        EsacVoucher $esacVoucher,
        EsacPromotion $esacPromotion,
        Country $country,
        KittingProduct $kittingProduct)
    {
        
        $corporateSale = (empty($this->input('sales_data.is_corporate_sales')) ? false : $this->input('sales_data.is_corporate_sales'));

        return [
            'sales_data' => 'required|array',
            'sales_data.status' => 'required|string|in:pending,save',
            'sales_data.country_id' => 'required|integer|exists:countries,id',
            'sales_data.downline_member_id' => 'required|integer|exists:members,user_id',
            'sales_data.sponsor_member_id' => 'sometimes|nullable|integer|exists:members,user_id',
            'sales_data.location_id' => 'required|integer|exists:locations,id',
            'sales_data.stock_location_id' => 'required|integer|exists:stock_locations,id',
            'sales_data.selected.shipping.sale_delivery_method' => 'required|integer|exists:master_data,id',
            'sales_data.cw_id' => ['required', new CwCheck($cwSchedulesInterface)],

            'sales_data.cvs.total_cv' => 'required|integer',
            'sales_data.cvs.total_qualified_cv' => 'required|integer',
            'sales_data.cvs.total_amp_cv' => [
                'required',
                'integer',
                new SalesCreationMinimumCvValidation(
                    $masterInterface,
                    $settingsInterface,
                    $enrollmentRank,
                    $enrollmentTemp,
                    $member,
                    $this->input('sales_data.downline_member_id'),
                    'amp',
                    $this->input('sales_data.products'),
                    $this->input('sales_data.kittings')
                )
            ],
            'sales_data.cvs.total_upgrade_cv' => [
                'required',
                'integer',
                new SalesCreationMinimumCvValidation(
                    $masterInterface,
                    $settingsInterface,
                    $enrollmentRank,
                    $enrollmentTemp,
                    $member,
                    $this->input('sales_data.downline_member_id'),
                    'enrollmentUpgrade',
                    $this->input('sales_data.products'),
                    $this->input('sales_data.kittings')
                )
            ],

            'sales_data.order_fees.total_nmp' => 'required|numeric',
            'sales_data.order_fees.tax_amount' => 'required|numeric',
            'sales_data.order_fees.total_gmp' => 'required|numeric',
            'sales_data.order_fees.rounding_adjustment' => 'required|numeric',

            'sales_data.skip_downline' => 'sometimes|nullable|boolean',

            //validating products --------------------------------------------------------------------------------------
            'sales_data.products.*.product_id'=>
                [
                    'sometimes', 'required', 'integer', 'exists:products,id',
                    new ProductAvailableInCountry($product, $this->input('sales_data.country_id'))
                ],
            'sales_data.products.*.quantity' => 'required_with:sales_data.products.*.product_id|integer',
            'sales_data.products.*.transaction_type' => [
                'required_with:sales_data.products.*.product_id',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_types')
            ],

            //validating kitting----------------------------------------------------------------------------------------
            'sales_data.kittings.*.kitting_id' => 'sometimes|required|integer|exists:kitting,id',
            'sales_data.kittings.*.quantity' => 'required_with:sales_data.products.*.kittings|integer',
            'sales_data.kittings.*.transaction_type' =>[
                'required_with:sales_data.kittings.*.kitting_id',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_types')
            ],

            //validating promotion free items --------------------------------------------------------------------------
            'sales_data.promotion.*.promo_id' => 'sometimes|required|integer|exists:promotion_free_items,id',

            //validating esac voucher-----------------------------------------------------------------------------------
            'sales_data.is_esac_redemption' => 'sometimes|nullable|boolean',
            'sales_data.esac_vouchers.*' =>
                [
                    'sometimes', 'integer', 'exists:esac_vouchers,id',
                    new EsacVoucherValidForRedemption($esacVoucher)
                ],
            'sales_data' =>
                [
                    new EsacVoucherRedemption($esacVoucher, $esacPromotion, $country, $kittingProduct)
                ],

            //validating corporate sales--------------------------------------------------------------------------------
            'sales_data.is_corporate_sales' => 'sometimes|boolean',
            'sales_data.corporate_sales' => 'required_if:sales_data.is_corporate_sales,1',
            'sales_data.corporate_sales.company_name' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|string|min:3|max:191' : 'nullable',
            'sales_data.corporate_sales.company_reg_number' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|string|min:3|max:191' : 'nullable',
            'sales_data.corporate_sales.company_address' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|array' : 'nullable',
            'sales_data.corporate_sales.company_email' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|string|max:191|email' : 'nullable',
            'sales_data.corporate_sales.person_in_charge' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|min:3|max:191' : 'nullable',
            'sales_data.corporate_sales.contact_country_code_id' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|integer|exists:countries,id' : 'nullable',
            'sales_data.corporate_sales.contact_number' => ($corporateSale) ? 'required_with:sales_data.corporate_sales|min:3|max:30' : 'nullable'
        ];
    }
}

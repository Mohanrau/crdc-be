<?php
namespace App\Rules\Payments;

use App\Interfaces\Payments\PaymentInterface;
use App\Models\Payments\PaymentModeSetting;
use Illuminate\Contracts\Validation\Rule;

class PaymentInputFieldValidation implements Rule
{
    private
        $paymentRepositoryObj,
        $paymentModeSettingObj,
        $fields,
        $emptyFieldName;

    /**
     * PaymentInputFieldValidation constructor.
     *
     * @param PaymentInterface $paymentInterface
     * @param PaymentModeSetting $paymentModeSetting
     * @param $fields
     */
    public function __construct(
        PaymentInterface $paymentInterface,
        PaymentModeSetting $paymentModeSetting,
        $fields
    )
    {
        $this->paymentRepositoryObj = $paymentInterface;

        $this->paymentModeSettingObj = $paymentModeSetting;

        $this->fields = $fields;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = true;

        $paymentSettingDetail = $this->paymentModeSettingObj->find($value);

        if($paymentSettingDetail){

            $countryIso2 = $paymentSettingDetail->country->code_iso_2;

            $paymentObject = $this->paymentRepositoryObj
                ->getPaymentObject($countryIso2, $paymentSettingDetail->configuration_file_name);

            $requiredInputs = $paymentObject->requiredInputs();

            $fields = collect($this->fields);

            collect($requiredInputs)->each(
                function($requiredInputItem, $requiredInputKey) 
                    use($fields) {

                        $requiredField = $fields->where('name', $requiredInputKey)->first();

                        if(!$requiredField){
                            $this->emptyFieldName = $requiredInputItem;

                            return false;
                        }

                        if(!isset($requiredField['value']) || empty($requiredField['value'])){
                            
                            $this->emptyFieldName = $requiredInputItem;

                            return false;

                        }
                    });

            $result = (!empty($this->emptyFieldName)) ? false : true;
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.make-payment.required-input-field', [
            'name' => $this->emptyFieldName
        ]);
    }
}

<?php
namespace App\Rules\Payments;

use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Auth
};

class CreditCardNumberValidation implements Rule
{
    private $fields;

    /**
     * CreditCardNumberValidation constructor.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentModeSetting
     * @param fields
     */
    public function __construct(
        $fields
    )
    {
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

        $fields = collect($this->fields);

        $cardNumberField = $fields->where('name','card_number')->first();

        if($cardNumberField){

            $cardNumber = $cardNumberField['value'];

            $result = (strlen($cardNumber) !== 16) ? false : true;
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
        return trans('message.make-payment.invalid-card-number');
    }
}

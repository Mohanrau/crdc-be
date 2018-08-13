<?php
namespace App\Rules\Invoices;

use Illuminate\Contracts\Validation\Rule;
use App\Models\{
    Invoices\Invoice,
    Invoices\LegacyInvoice
};

class LegacyInvoiceCheck implements Rule
{
    private $id, $isLegacy;

    /**
     * Create a new rule instance.
     *
     * @param  int  $cwId
     * @return void
     */
    public function __construct($isLegacy)
    {
        $this->isLegacy = $isLegacy;
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
        $this->id = $value;

        if ($this->isLegacy)
        {
            $legacyInvoice = LegacyInvoice::whereId($value)->first();

            if (empty($legacyInvoice))
            {
                $this->error = 1;
                return false;
            }
        }
        else
        {
            $invoice = Invoice::whereId($value)->first();

            if (empty($invoice))
            {
                $this->error = 2;
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->error == 1)
        {
            return trans('validation.exists', ['attribute' => "legacy tax invoice"]);
        }
        else
        {
            return trans('validation.exists', ['attribute' => "tax invoice"]);
        }
    }
}

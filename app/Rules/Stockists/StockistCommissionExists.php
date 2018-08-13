<?php
namespace App\Rules\Stockists;

use Illuminate\Contracts\Validation\Rule;
use App\Models\{
    Stockists\StockistCommission,
    Stockists\Stockist,
    General\CWSchedule
};

class StockistCommissionExists implements Rule
{
    protected $cwId,
              $error,
              $name,
              $cwName,
              $cWSchedule,
              $stockistCommission;

    /**
     * Create a new rule instance.
     *
     * @param  int  $cwId
     * @return void
     */
    public function __construct(
        $cwId, 
        CWSchedule $cWSchedule,
        Stockist $stockist,
        StockistCommission $stockistCommission
    )
    {
        $this->cwId = $cwId;
        $this->cWSchedule = $cWSchedule;
        $this->stockist = $stockist;
        $this->stockistCommission = $stockistCommission;
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
        $cw = $this->cWSchedule->whereId($this->cwId)->first();

        if (empty($cw))
        {
            return false;
        }

        $stockist = $this->stockist->whereId($value)->first();

        if (empty($stockist))
        {
            return false;
        }

        $sc = $this->stockistCommission->where('cw_id', $this->cwId)->where('stockist_id', $value)->first();

        if ($sc)
        {
            return true;
        }

        $this->name = $stockist->name;

        $this->cwName = $cw->cw_name;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.stockist_commission_not_found', ['user' => $this->name, 'cw' => $this->cwName]);
    }
}

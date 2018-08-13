<?php
namespace App\Rules\Bonus;

use Illuminate\Contracts\Validation\Rule;
use App\Models\{
    Bonus\BonusSummary,
    Users\User
};

class YearlyIncomeExists implements Rule
{
    protected $error, $name, $year;

    /**
     * YearlyIncomeExists constructor.
     *
     * @param $year
     */
    public function __construct($year)
    {
        $this->year = $year;
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
        $user = User::whereId($value)->first();

        $list = BonusSummary::join('cw_schedules', 'bonuses_summary.cw_id', 'cw_schedules.id')
                            ->where('user_id', '=', $user->id)
                            ->whereRaw("year(cw_schedules.date_from) = ? ", $this->year);
                            
        if ($list->count() > 0)
        {
           return true;
        }

        $this->name = $user->name;

        $this->error = 2;

        return false;
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
            return trans('validation.not_in', ['attribute' => "user"]);
        }
        else
        {
            return trans('validation.yearly_income_not_found', ['user' => $this->name, 'year' => $this->year]);
        }
    }
}
